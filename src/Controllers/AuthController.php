<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Security\Passwords;
use App\Security\Roles;
use App\Support\Text;
use App\Support\Url;

final class AuthController extends Controller
{
    public function showLogin(): Response
    {
        if ($this->app->auth->check()) {
            return $this->redirect('/');
        }

        $next = $this->request->query('next');
        if ($next !== '') {
            $_SESSION['_intended'] = Url::safeRedirect($next);
        }

        return $this->loginForm('', []);
    }

    public function login(): Response
    {
        if ($this->app->auth->check()) {
            return $this->redirect('/');
        }

        $email = Text::singleLine($this->request->input('email'));
        $password = $this->request->input('password');

        if ($email === '' || $password === '') {
            return $this->loginForm($email, ['form' => 'Please enter your email address and password.']);
        }
        if (!$this->app->captcha->verify($this->request->input('g-recaptcha-response'))) {
            return $this->loginForm($email, ['form' => 'Please complete the “I’m not a robot” check.']);
        }

        $author = $this->app->auth->attempt($email, $password);
        if ($author === null) {
            return $this->loginForm($email, ['form' => 'The email address or password is incorrect.']);
        }

        $this->app->auth->login($author);
        $this->app->csrf->regenerate();
        $this->app->flash->success('Welcome back, ' . $author['Name'] . '!');

        return $this->redirect($this->app->pullIntendedUrl('/'));
    }

    public function logout(): Response
    {
        $this->app->auth->logout();
        $this->app->flash->success('You have been logged out.');

        return $this->redirect('/');
    }

    public function showRegister(): Response
    {
        if ($this->app->auth->check()) {
            return $this->redirect('/');
        }

        return $this->registerForm(['name' => '', 'email' => ''], []);
    }

    public function register(): Response
    {
        if ($this->app->auth->check()) {
            return $this->redirect('/');
        }

        $input = [
            'name' => Text::singleLine($this->request->input('name')),
            'email' => Text::singleLine($this->request->input('email')),
        ];
        $password = $this->request->input('password');

        $errors = [];
        if ($input['name'] === '' || Text::length($input['name']) > 100) {
            $errors['name'] = 'Please enter your name (at most 100 characters).';
        }
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false || strlen($input['email']) > 254) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        $passwordError = Passwords::validate($password, $this->request->input('password_confirmation'));
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }
        if ($errors === [] && !$this->app->captcha->verify($this->request->input('g-recaptcha-response'))) {
            $errors['form'] = 'Please complete the “I’m not a robot” check.';
        }
        if ($errors === [] && $this->app->authors->emailExists($input['email'])) {
            $errors['email'] = 'An account with this email address already exists.';
        }
        if ($errors !== []) {
            return $this->registerForm($input, $errors);
        }

        $this->app->authors->create($input['name'], $input['email'], Passwords::hash($password), [Roles::USER]);

        $this->app->mailer->send(
            $input['email'],
            'Welcome to Ideal Ideas',
            '<p>Hi ' . e($input['name']) . ',</p><p>Your account has been created and you can now log in.</p>',
        );

        $this->app->flash->success('Your account has been created. You can now log in.');

        return $this->redirect('/login');
    }

    /** @param array<string, string> $errors */
    private function loginForm(string $email, array $errors): Response
    {
        return $this->render('auth/login', [
            'title' => 'Log in',
            'email' => $email,
            'errors' => $errors,
            'useCaptcha' => true,
        ], $errors === [] ? 200 : 422);
    }

    /**
     * @param array{name: string, email: string} $input
     * @param array<string, string> $errors
     */
    private function registerForm(array $input, array $errors): Response
    {
        return $this->render('auth/register', [
            'title' => 'Create an account',
            'input' => $input,
            'errors' => $errors,
            'useCaptcha' => true,
        ], $errors === [] ? 200 : 422);
    }
}
