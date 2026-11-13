<?php

declare(strict_types=1);

namespace App\Controllers;

use App\App;
use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Support\Url;

abstract class Controller
{
    protected readonly Request $request;

    public function __construct(protected readonly App $app)
    {
        $this->request = $app->request;
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        $data['currentUser'] ??= $this->app->auth->user();

        return new Response($this->app->view->render($template, $data), $status);
    }

    protected function redirect(string $to): Response
    {
        return Response::redirect($to);
    }

    /** Redirect to the submitted `return_to` path if it is a safe local path. */
    protected function back(string $fallback = '/'): Response
    {
        return Response::redirect(Url::safeRedirect($this->request->input('return_to'), $fallback));
    }

    /**
     * Require a logged-in user (guests are sent to /login).
     *
     * @return array<string, mixed>
     */
    protected function requireUser(): array
    {
        $user = $this->app->auth->user();
        if ($user === null) {
            throw new HttpException(401);
        }

        return $user;
    }

    protected function authorize(bool $allowed): void
    {
        if (!$allowed) {
            throw new HttpException(403);
        }
    }

    protected function notFound(string $message = ''): HttpException
    {
        return new HttpException(404, $message);
    }
}
