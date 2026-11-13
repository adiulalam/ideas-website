<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Support\Text;

final class CommentController extends Controller
{
    public const MAX_LENGTH = 2000;

    public function store(): Response
    {
        $user = $this->requireUser();
        $ideaId = $this->request->inputInt('idea_id');
        $idea = $ideaId !== null ? $this->app->ideas->find($ideaId) : null;
        if ($idea === null) {
            throw $this->notFound('That idea does not exist or has been deleted.');
        }

        $back = '/idea?id=' . $idea['ID'] . '#comments';
        $comment = Text::multiLine($this->request->input('comment'));
        if ($comment === '') {
            $this->app->flash->error('Comments cannot be empty.');

            return $this->redirect($back);
        }
        if (Text::length($comment) > self::MAX_LENGTH) {
            $this->app->flash->error(sprintf('Comments can be at most %s characters.', number_format(self::MAX_LENGTH)));

            return $this->redirect($back);
        }

        $this->app->comments->create($idea['ID'], (int) $user['Author_ID'], $comment);

        if ((int) $idea['AuthorID'] !== (int) $user['Author_ID']) {
            $this->notifyIdeaAuthor($idea, $user);
        }

        $this->app->flash->success('Your comment has been posted.');

        return $this->redirect($back);
    }

    public function destroy(): Response
    {
        $user = $this->requireUser();
        $commentId = $this->request->inputInt('id');

        // Ownership is enforced in SQL: only the comment's author can delete it.
        $ideaId = $commentId !== null ? $this->app->comments->deleteOwned($commentId, (int) $user['Author_ID']) : null;
        if ($ideaId === null) {
            throw $this->notFound('That comment does not exist or is not yours.');
        }

        $this->app->flash->success('Your comment has been deleted.');

        return $this->redirect('/idea?id=' . $ideaId . '#comments');
    }

    /**
     * @param array<string, mixed> $idea
     * @param array<string, mixed> $commenter
     */
    private function notifyIdeaAuthor(array $idea, array $commenter): void
    {
        $to = $idea['AuthorEmail'] ?? null;
        if (!is_string($to) || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $summary = mb_strimwidth($this->app->sanitizer->toPlainText((string) $idea['IdeaText']), 0, 80, '…', 'UTF-8');
        $body = '<p>' . e((string) $commenter['Name']) . ' commented on your idea “' . e($summary) . '”.</p>';
        if ($this->app->config->appUrl !== '') {
            $link = $this->app->config->appUrl . '/idea?id=' . $idea['ID'] . '#comments';
            $body .= '<p><a href="' . e($link) . '">Read the comment</a></p>';
        }

        $this->app->mailer->send($to, 'New comment on your idea', $body);
    }
}
