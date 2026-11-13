<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\HttpException;
use App\Http\Response;
use App\Ideas\IdeaFilters;
use App\Services\UploadException;
use App\Support\Images;
use App\Support\Text;
use Throwable;

final class IdeaController extends Controller
{
    public const MAX_TEXT_LENGTH = 2000;

    public function index(): Response
    {
        $filters = IdeaFilters::fromQuery($this->request->queryAll());
        $total = $this->app->ideas->count($filters);
        $pages = max(1, (int) ceil($total / $filters->perPage));
        $filters = $filters->withPage(min($filters->page, $pages));
        $ideas = $this->app->ideas->search($filters, $filters->perPage, ($filters->page - 1) * $filters->perPage);

        return $this->render('ideas/index', [
            'title' => 'Ideas',
            'ideas' => $ideas,
            'myVotes' => $this->myVotes($ideas),
            'filters' => $filters,
            'total' => $total,
            'pages' => $pages,
            'authors' => $this->app->authors->listForFilter(),
            'categories' => $this->app->categories->all(),
            'fab' => ['href' => '/ideas/new', 'label' => 'Post a new idea'],
        ]);
    }

    public function show(): Response
    {
        $idea = $this->findIdea($this->request->queryInt('id'));
        $plain = $this->app->sanitizer->toPlainText($idea['IdeaText']);

        return $this->render('ideas/show', [
            'title' => $plain !== '' ? mb_strimwidth($plain, 0, 60, '…', 'UTF-8') : 'Idea',
            'idea' => $idea,
            'myVotes' => $this->myVotes([$idea]),
            'comments' => $this->app->comments->forIdea($idea['ID']),
        ]);
    }

    public function create(): Response
    {
        $this->requireUser();

        return $this->form(null, ['text' => '', 'categories' => [], 'departments' => [], 'image_url' => '']);
    }

    public function store(): Response
    {
        // The author is always the logged-in user. The old form trusted a
        // hidden "Author" field, so anyone could post as anyone — and the
        // handler did not even require a login.
        $user = $this->requireUser();
        [$input, $errors] = $this->validate();
        if ($errors !== []) {
            return $this->form(null, $input, $errors);
        }

        $image = $input['image_url'];
        $file = $this->request->file('image');
        if ($file !== null) {
            try {
                $image = $this->app->uploads->store($file);
            } catch (UploadException $e) {
                return $this->form(null, $input, ['image' => $e->getMessage()]);
            }
        }

        try {
            $id = $this->app->ideas->create((int) $user['Author_ID'], $input['clean'], $image, $input['categories'], $input['departments']);
        } catch (Throwable $e) {
            if ($file !== null) {
                $this->app->uploads->delete($image);
            }
            throw $e;
        }

        $this->app->flash->success('Your idea has been posted.');

        return $this->redirect('/idea?id=' . $id);
    }

    public function edit(): Response
    {
        $this->requireUser();
        $idea = $this->findIdea($this->request->queryInt('id'));
        $this->authorize($this->app->auth->canEditIdea($idea));

        return $this->form($idea, [
            'text' => $idea['IdeaText'],
            'categories' => $this->app->ideas->categoryIds($idea['ID']),
            'departments' => $this->app->ideas->departmentIds($idea['ID']),
            'image_url' => '',
        ]);
    }

    public function update(): Response
    {
        $this->requireUser();
        $idea = $this->findIdea($this->request->inputInt('id'));
        $this->authorize($this->app->auth->canEditIdea($idea));

        [$input, $errors] = $this->validate();
        if ($errors !== []) {
            return $this->form($idea, $input, $errors);
        }

        $newImage = $input['image_url'] !== '' ? $input['image_url'] : null;
        $file = $this->request->file('image');
        if ($file !== null) {
            try {
                $newImage = $this->app->uploads->store($file);
            } catch (UploadException $e) {
                return $this->form($idea, $input, ['image' => $e->getMessage()]);
            }
        }

        try {
            $this->app->ideas->update($idea['ID'], $input['clean'], $newImage, $input['categories'], $input['departments']);
        } catch (Throwable $e) {
            if ($file !== null) {
                $this->app->uploads->delete($newImage);
            }
            throw $e;
        }

        // Only ever delete the file recorded in the database for this idea,
        // and only if we created it (see ImageUploads::isManaged()).
        if ($newImage !== null && $newImage !== $idea['Image']) {
            $this->app->uploads->delete($idea['Image']);
        }

        $this->app->flash->success('Your idea has been updated.');

        return $this->redirect('/idea?id=' . $idea['ID']);
    }

    public function destroy(): Response
    {
        $this->requireUser();
        $idea = $this->findIdea($this->request->inputInt('id'));
        $this->authorize($this->app->auth->canEditIdea($idea));

        $image = $this->app->ideas->delete($idea['ID']);
        $this->app->uploads->delete($image);
        $this->app->flash->success('The idea has been deleted.');

        return $this->redirect('/');
    }

    public function vote(): Response
    {
        $user = $this->requireUser();
        $idea = $this->findIdea($this->request->inputInt('id'));

        $direction = $this->request->input('direction');
        if ($direction !== 'up' && $direction !== 'down') {
            throw new HttpException(400);
        }

        $this->app->votes->cast($idea['ID'], (int) $user['Author_ID'], $direction === 'up' ? 1 : -1);

        return $this->back('/idea?id=' . $idea['ID']);
    }

    /** @return array<string, mixed> */
    private function findIdea(?int $id): array
    {
        $idea = $id !== null ? $this->app->ideas->find($id) : null;
        if ($idea === null) {
            throw $this->notFound('That idea does not exist or has been deleted.');
        }

        return $idea;
    }

    /**
     * @param list<array<string, mixed>> $ideas
     * @return array<int, int>
     */
    private function myVotes(array $ideas): array
    {
        $userId = $this->app->auth->id();
        if ($userId === null || $ideas === []) {
            return [];
        }

        return $this->app->votes->forAuthor($userId, array_map(static fn (array $idea): int => (int) $idea['ID'], $ideas));
    }

    /**
     * @return array{0: array{text: string, clean: string, categories: list<int>, departments: list<int>, image_url: string}, 1: array<string, string>}
     */
    private function validate(): array
    {
        $raw = Text::multiLine($this->request->input('text'));
        $input = [
            'text' => $raw,
            'clean' => $this->app->sanitizer->clean($raw),
            'categories' => $this->app->categories->existingIds($this->request->inputIntList('categories')),
            'departments' => $this->app->departments->existingIds($this->request->inputIntList('departments')),
            'image_url' => trim($this->request->input('image_url')),
        ];

        $errors = [];
        if ($this->app->sanitizer->toPlainText($input['clean']) === '') {
            $errors['text'] = 'Please describe your idea.';
        } elseif (Text::length($raw) > self::MAX_TEXT_LENGTH) {
            $errors['text'] = sprintf('Ideas can be at most %s characters.', number_format(self::MAX_TEXT_LENGTH));
        }
        if ($input['categories'] === []) {
            $errors['categories'] = 'Choose at least one category.';
        }
        if ($input['departments'] === [] && $this->app->departments->all() !== []) {
            $errors['departments'] = 'Choose at least one department.';
        }
        if ($input['image_url'] !== '' && $this->request->file('image') !== null) {
            $errors['image'] = 'Either upload an image or paste a link, not both.';
        } elseif ($input['image_url'] !== '' && !Images::isValidRemoteUrl($input['image_url'])) {
            $errors['image'] = 'Image links must be a valid https:// address of at most 255 characters.';
        }

        return [$input, $errors];
    }

    /**
     * @param array<string, mixed>|null $idea
     * @param array<string, mixed> $input
     * @param array<string, string> $errors
     */
    private function form(?array $idea, array $input, array $errors = []): Response
    {
        return $this->render('ideas/form', [
            'title' => $idea === null ? 'New idea' : 'Edit idea',
            'idea' => $idea,
            'input' => $input,
            'errors' => $errors,
            'categories' => $this->app->categories->all(),
            'departments' => $this->app->departments->all(),
        ], $errors === [] ? 200 : 422);
    }
}
