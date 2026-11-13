<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Http\HttpException;
use App\Http\Response;
use App\Security\Passwords;
use App\Security\Roles;
use App\Support\Text;

/**
 * Author administration (Site Administrators and Account Administrators).
 *
 * Only Site Administrators may grant the Site Administrator role or modify a
 * Site Administrator's account; previously an Account Administrator could
 * promote themselves to full admin.
 */
final class AuthorController extends Controller
{
    public function index(): Response
    {
        $this->guard();

        return $this->render('admin/authors/index', [
            'title' => 'Manage authors',
            'authors' => $this->app->authors->allWithRoles(),
            'fab' => ['href' => '/admin/authors/new', 'label' => 'Add an author'],
        ]);
    }

    public function create(): Response
    {
        $this->guard();

        return $this->form(null, ['name' => '', 'email' => '', 'roles' => [Roles::USER]], []);
    }

    public function store(): Response
    {
        $this->guard();
        [$input, $password, $errors] = $this->validate(null);
        if ($errors !== []) {
            return $this->form(null, $input, $errors);
        }

        $this->app->authors->create($input['name'], $input['email'], Passwords::hash((string) $password), $input['roles']);
        $this->app->flash->success('Author “' . $input['name'] . '” has been created.');

        return $this->redirect('/admin/authors');
    }

    public function edit(): Response
    {
        $this->guard();
        $author = $this->findEditable($this->request->queryInt('id'));

        return $this->form($author, [
            'name' => (string) $author['Name'],
            'email' => (string) $author['Email'],
            'roles' => $author['roles'],
        ], []);
    }

    public function update(): Response
    {
        $this->guard();
        $author = $this->findEditable($this->request->inputInt('id'));
        [$input, $password, $errors] = $this->validate($author);
        if ($errors !== []) {
            return $this->form($author, $input, $errors);
        }

        $this->app->authors->update(
            (int) $author['Author_ID'],
            $input['name'],
            $input['email'],
            $password !== null ? Passwords::hash($password) : null,
            $input['roles'],
        );
        $this->app->flash->success('Author “' . $input['name'] . '” has been updated.');

        return $this->redirect('/admin/authors');
    }

    public function destroy(): Response
    {
        $user = $this->guard();
        $author = $this->findEditable($this->request->inputInt('id'));
        if ((int) $author['Author_ID'] === (int) $user['Author_ID']) {
            throw new HttpException(403, 'You cannot delete your own account from the admin area.');
        }

        foreach ($this->app->authors->delete((int) $author['Author_ID']) as $image) {
            $this->app->uploads->delete($image);
        }
        $this->app->flash->success('Author “' . $author['Name'] . '” and all of their content have been deleted.');

        return $this->redirect('/admin/authors');
    }

    /** @return array<string, mixed> */
    private function guard(): array
    {
        $user = $this->requireUser();
        $this->authorize($this->app->auth->canManageAuthors());

        return $user;
    }

    /** @return array<string, mixed> */
    private function findEditable(?int $id): array
    {
        $author = $id !== null ? $this->app->authors->findWithRoles($id) : null;
        if ($author === null) {
            throw $this->notFound('That author does not exist.');
        }
        if (in_array(Roles::SITE_ADMIN, $author['roles'], true) && !$this->app->auth->hasAnyRole(Roles::SITE_ADMIN)) {
            throw new HttpException(403, 'Only site administrators can modify a site administrator’s account.');
        }

        return $author;
    }

    /**
     * @param array<string, mixed>|null $author
     * @return array{0: array{name: string, email: string, roles: list<string>}, 1: string|null, 2: array<string, string>}
     */
    private function validate(?array $author): array
    {
        $validRoles = array_column($this->app->roles->all(), 'ID');
        $input = [
            'name' => Text::singleLine($this->request->input('name')),
            'email' => Text::singleLine($this->request->input('email')),
            'roles' => array_values(array_intersect($this->request->inputStringList('roles'), $validRoles)),
        ];
        $password = $this->request->input('password');
        $authorId = $author !== null ? (int) $author['Author_ID'] : null;

        $errors = [];
        if ($input['name'] === '' || Text::length($input['name']) > 100) {
            $errors['name'] = 'Please enter a name (at most 100 characters).';
        }
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false || strlen($input['email']) > 254) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($this->app->authors->emailExists($input['email'], $authorId)) {
            $errors['email'] = 'Another author already uses this email address.';
        }
        if ($input['roles'] === []) {
            $errors['roles'] = 'Choose at least one role.';
        }

        $hadSiteAdmin = $author !== null && in_array(Roles::SITE_ADMIN, $author['roles'], true);
        if (in_array(Roles::SITE_ADMIN, $input['roles'], true) && !$hadSiteAdmin && !$this->app->auth->hasAnyRole(Roles::SITE_ADMIN)) {
            $errors['roles'] = 'Only site administrators can grant the Site Administrator role.';
        }

        if ($author === null || $password !== '') {
            $passwordError = Passwords::validate($password, $this->request->input('password_confirmation'));
            if ($passwordError !== null) {
                $errors['password'] = $passwordError;
            }
        }

        return [$input, $password !== '' ? $password : null, $errors];
    }

    /**
     * @param array<string, mixed>|null $author
     * @param array<string, mixed> $input
     * @param array<string, string> $errors
     */
    private function form(?array $author, array $input, array $errors): Response
    {
        return $this->render('admin/authors/form', [
            'title' => $author === null ? 'New author' : 'Edit author',
            'author' => $author,
            'input' => $input,
            'errors' => $errors,
            'roles' => $this->app->roles->all(),
            'canGrantSiteAdmin' => $this->app->auth->hasAnyRole(Roles::SITE_ADMIN),
        ], $errors === [] ? 200 : 422);
    }
}
