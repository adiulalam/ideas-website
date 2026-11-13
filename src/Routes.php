<?php

declare(strict_types=1);

namespace App;

use App\Controllers\Admin\AuthorController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\AuthController;
use App\Controllers\CommentController;
use App\Controllers\ContactController;
use App\Controllers\IdeaController;
use App\Http\Router;

/** The full route table. Every POST route is CSRF-checked in App::handle(). */
final class Routes
{
    public static function build(App $app): Router
    {
        $router = new Router();

        $ideas = new IdeaController($app);
        $router->get('/', $ideas->index(...));
        $router->get('/idea', $ideas->show(...));
        $router->get('/ideas/new', $ideas->create(...));
        $router->post('/ideas/new', $ideas->store(...));
        $router->get('/ideas/edit', $ideas->edit(...));
        $router->post('/ideas/edit', $ideas->update(...));
        $router->post('/ideas/delete', $ideas->destroy(...));
        $router->post('/ideas/vote', $ideas->vote(...));

        $comments = new CommentController($app);
        $router->post('/comments', $comments->store(...));
        $router->post('/comments/delete', $comments->destroy(...));

        $auth = new AuthController($app);
        $router->get('/login', $auth->showLogin(...));
        $router->post('/login', $auth->login(...));
        $router->post('/logout', $auth->logout(...));
        $router->get('/register', $auth->showRegister(...));
        $router->post('/register', $auth->register(...));

        $contact = new ContactController($app);
        $router->get('/contact', $contact->show(...));
        $router->post('/contact', $contact->send(...));

        $authors = new AuthorController($app);
        $router->get('/admin/authors', $authors->index(...));
        $router->get('/admin/authors/new', $authors->create(...));
        $router->post('/admin/authors/new', $authors->store(...));
        $router->get('/admin/authors/edit', $authors->edit(...));
        $router->post('/admin/authors/edit', $authors->update(...));
        $router->post('/admin/authors/delete', $authors->destroy(...));

        $categories = new CategoryController($app);
        $router->get('/admin/categories', $categories->index(...));
        $router->get('/admin/categories/new', $categories->create(...));
        $router->post('/admin/categories/new', $categories->store(...));
        $router->get('/admin/categories/edit', $categories->edit(...));
        $router->post('/admin/categories/edit', $categories->update(...));
        $router->post('/admin/categories/delete', $categories->destroy(...));

        return $router;
    }
}
