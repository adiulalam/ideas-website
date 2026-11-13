<?php

declare(strict_types=1);

namespace App;

use App\Http\HttpException;
use App\Http\LegacyRoutes;
use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\IdeaRepository;
use App\Repository\RoleRepository;
use App\Repository\VoteRepository;
use App\Security\Auth;
use App\Security\Csrf;
use App\Security\SecurityHeaders;
use App\Services\Captcha;
use App\Services\HtmlSanitizer;
use App\Services\ImageUploads;
use App\Services\Mailer;
use App\Support\Flash;
use App\Support\Url;
use Throwable;

/**
 * Application kernel: wires services together and handles one request.
 * Public properties are the "container" that controllers use.
 */
final class App
{
    public readonly Database $db;
    public readonly View $view;
    public readonly Csrf $csrf;
    public readonly Flash $flash;
    public readonly Auth $auth;
    public readonly Captcha $captcha;
    public readonly Mailer $mailer;
    public readonly HtmlSanitizer $sanitizer;
    public readonly ImageUploads $uploads;
    public readonly IdeaRepository $ideas;
    public readonly AuthorRepository $authors;
    public readonly CommentRepository $comments;
    public readonly VoteRepository $votes;
    public readonly CategoryRepository $categories;
    public readonly DepartmentRepository $departments;
    public readonly RoleRepository $roles;

    public function __construct(public readonly Config $config, public readonly Request $request)
    {
        $this->db = new Database($config);
        $this->view = new View($config->rootDir . '/templates');
        $this->csrf = new Csrf();
        $this->flash = new Flash();
        $this->captcha = new Captcha($config);
        $this->mailer = new Mailer($config);
        $this->sanitizer = new HtmlSanitizer();
        $this->uploads = new ImageUploads($config->uploadDir);
        $this->ideas = new IdeaRepository($this->db);
        $this->authors = new AuthorRepository($this->db);
        $this->comments = new CommentRepository($this->db);
        $this->votes = new VoteRepository($this->db);
        $this->categories = new CategoryRepository($this->db);
        $this->departments = new DepartmentRepository($this->db);
        $this->roles = new RoleRepository($this->db);
        $this->auth = new Auth($this->authors);
    }

    public static function fromGlobals(string $rootDir): self
    {
        return new self(Config::load($rootDir), Request::fromGlobals());
    }

    public function run(): void
    {
        $https = $this->request->isHttps();
        SecurityHeaders::send($https);

        // Health checks must not create sessions.
        if ($this->request->path() === '/healthz') {
            $this->health()->send();

            return;
        }

        Session::start($https);
        $this->shareViewData();

        try {
            $response = $this->handle();
        } catch (HttpException $e) {
            $response = $this->httpError($e);
        } catch (DatabaseUnavailable $e) {
            error_log('[db] ' . $e->getMessage());
            $response = $this->errorPage(503, $e);
        } catch (Throwable $e) {
            error_log('[app] ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $response = $this->errorPage(500, $e);
        }

        $response->send();
    }

    public function handle(): Response
    {
        $method = $this->request->method();

        $legacy = LegacyRoutes::redirectFor($method, $this->request->path(), $this->request->queryAll());
        if ($legacy !== null) {
            return Response::redirect($legacy, 301);
        }

        if ($method === 'POST') {
            if ($this->request->postBodyTooLarge()) {
                throw new HttpException(413);
            }
            if (!$this->csrf->validate($this->request->input(Csrf::FIELD))) {
                throw new HttpException(403, 'Your session expired or the form was submitted from another site. Please go back, refresh the page and try again.');
            }
        }

        return Routes::build($this)->dispatch($method, $this->request->path());
    }

    /** Liveness + database check for Docker / uptime monitors. Never reveals details. */
    private function health(): Response
    {
        try {
            $this->db->pdo()->query('SELECT 1');

            return Response::json(['status' => 'ok']);
        } catch (Throwable $e) {
            error_log('[health] ' . $e->getMessage());

            return Response::json(['status' => 'error', 'database' => 'unreachable'], 503);
        }
    }

    private function shareViewData(): void
    {
        $this->view->share('auth', $this->auth);
        $this->view->share('csrf', $this->csrf);
        $this->view->share('flash', $this->flash);
        $this->view->share('captcha', $this->captcha);
        $this->view->share('sanitizer', $this->sanitizer);
        $this->view->share('currentPath', $this->request->path());
    }

    private function httpError(HttpException $e): Response
    {
        if ($e->status === 401) {
            // Remember where to send the user after they log in.
            $_SESSION['_intended'] = $this->request->method() === 'GET'
                ? $this->request->pathWithQuery()
                : Url::safeRedirect($this->request->input('return_to'), '/');
            $this->flash->error('Please log in to continue.');

            return Response::redirect('/login');
        }

        return $this->errorPage($e->status, $e);
    }

    private function errorPage(int $status, Throwable $e): Response
    {
        $message = $e instanceof HttpException ? $e->getMessage() : HttpException::defaultMessage($status);
        if ($this->config->debug && !$e instanceof HttpException) {
            $message .= "\n\n" . get_class($e) . ': ' . $e->getMessage();
        }

        $headers = $e instanceof HttpException ? $e->headers : [];
        if ($status === 503) {
            $headers['Retry-After'] = '60';
        }

        try {
            $body = $this->view->render('error', [
                'title' => HttpException::title($status),
                'status' => $status,
                'message' => $message,
                'currentUser' => $this->safeCurrentUser(),
            ]);
        } catch (Throwable $renderError) {
            error_log('[app] error page failed to render: ' . $renderError->getMessage());
            $body = '<!doctype html><meta charset="utf-8"><title>Error</title><h1>' . e(HttpException::title($status)) . '</h1><p>' . e($message) . '</p>';
        }

        return new Response($body, $status, $headers);
    }

    /**
     * The current user for page chrome. Swallows database errors so that the
     * 503 page itself never fails (the old error page crashed, leaving users
     * with a blank screen).
     *
     * @return array<string, mixed>|null
     */
    public function safeCurrentUser(): ?array
    {
        try {
            return $this->auth->user();
        } catch (Throwable) {
            return null;
        }
    }

    /** Pull the post-login destination saved when a guest hit a protected page. */
    public function pullIntendedUrl(string $fallback = '/'): string
    {
        $target = $_SESSION['_intended'] ?? null;
        unset($_SESSION['_intended']);

        return Url::safeRedirect(is_string($target) ? $target : null, $fallback);
    }
}
