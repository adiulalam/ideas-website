<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Http\Response;
use App\Support\Text;

/** Category administration (Site Administrators and Category Moderators). */
final class CategoryController extends Controller
{
    public function index(): Response
    {
        $this->guard();

        return $this->render('admin/categories/index', [
            'title' => 'Manage categories',
            'categories' => $this->app->categories->all(),
            'fab' => ['href' => '/admin/categories/new', 'label' => 'Add a category'],
        ]);
    }

    public function create(): Response
    {
        $this->guard();

        return $this->form(null, '', []);
    }

    public function store(): Response
    {
        $this->guard();
        [$name, $errors] = $this->validate(null);
        if ($errors !== []) {
            return $this->form(null, $name, $errors);
        }

        $this->app->categories->create($name);
        $this->app->flash->success('Category “' . $name . '” has been created.');

        return $this->redirect('/admin/categories');
    }

    public function edit(): Response
    {
        $this->guard();
        $category = $this->find($this->request->queryInt('id'));

        return $this->form($category, $category['Name'], []);
    }

    public function update(): Response
    {
        $this->guard();
        $category = $this->find($this->request->inputInt('id'));
        [$name, $errors] = $this->validate($category['ID']);
        if ($errors !== []) {
            return $this->form($category, $name, $errors);
        }

        $this->app->categories->update($category['ID'], $name);
        $this->app->flash->success('Category “' . $name . '” has been updated.');

        return $this->redirect('/admin/categories');
    }

    public function destroy(): Response
    {
        $this->guard();
        $category = $this->find($this->request->inputInt('id'));
        $this->app->categories->delete($category['ID']);
        $this->app->flash->success('Category “' . $category['Name'] . '” has been deleted.');

        return $this->redirect('/admin/categories');
    }

    private function guard(): void
    {
        $this->requireUser();
        $this->authorize($this->app->auth->canManageCategories());
    }

    /** @return array{ID: int, Name: string, Image: string} */
    private function find(?int $id): array
    {
        $category = $id !== null ? $this->app->categories->find($id) : null;
        if ($category === null) {
            throw $this->notFound('That category does not exist.');
        }

        return $category;
    }

    /** @return array{0: string, 1: array<string, string>} */
    private function validate(?int $exceptId): array
    {
        $name = Text::singleLine($this->request->input('name'));
        $errors = [];
        if ($name === '' || Text::length($name) > 100) {
            $errors['name'] = 'Please enter a name (at most 100 characters).';
        } elseif ($this->app->categories->nameExists($name, $exceptId)) {
            $errors['name'] = 'A category with this name already exists.';
        }

        return [$name, $errors];
    }

    /**
     * @param array{ID: int, Name: string, Image: string}|null $category
     * @param array<string, string> $errors
     */
    private function form(?array $category, string $name, array $errors): Response
    {
        return $this->render('admin/categories/form', [
            'title' => $category === null ? 'New category' : 'Edit category',
            'category' => $category,
            'name' => $name,
            'errors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
