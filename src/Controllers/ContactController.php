<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Support\Text;

/**
 * Contact form.
 *
 * The old version emailed the message *to the address typed into the form*,
 * from the site's Gmail account, with an unescaped name and subject: an open
 * relay for spam and phishing. Messages now always go to the site owner
 * (CONTACT_TO) with the visitor's address as Reply-To.
 */
final class ContactController extends Controller
{
    public function show(): Response
    {
        return $this->form(['name' => '', 'email' => '', 'subject' => '', 'message' => ''], []);
    }

    public function send(): Response
    {
        $input = [
            'name' => Text::singleLine($this->request->input('name')),
            'email' => Text::singleLine($this->request->input('email')),
            'subject' => Text::singleLine($this->request->input('subject')),
            'message' => Text::multiLine($this->request->input('message')),
        ];

        $errors = [];
        if ($input['name'] === '' || Text::length($input['name']) > 100) {
            $errors['name'] = 'Please enter your name (at most 100 characters).';
        }
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false || strlen($input['email']) > 254) {
            $errors['email'] = 'Please enter a valid email address so I can reply.';
        }
        if (Text::length($input['subject']) > 150) {
            $errors['subject'] = 'The subject can be at most 150 characters.';
        }
        if ($input['message'] === '' || Text::length($input['message']) > 5000) {
            $errors['message'] = 'Please write a message (at most 5,000 characters).';
        }
        if ($errors === [] && !$this->app->captcha->verify($this->request->input('g-recaptcha-response'))) {
            $errors['form'] = 'Please complete the “I’m not a robot” check.';
        }
        if ($errors !== []) {
            return $this->form($input, $errors);
        }

        $subject = $input['subject'] !== '' ? $input['subject'] : 'New message from the contact form';
        $body = '<p><strong>From:</strong> ' . e($input['name']) . ' &lt;' . e($input['email']) . '&gt;</p>'
            . '<p>' . nl2br(e($input['message'])) . '</p>';

        $sent = $this->app->config->contactTo !== ''
            && $this->app->mailer->send($this->app->config->contactTo, '[Ideal Ideas] ' . $subject, $body, $input['email'], $input['name']);

        if (!$sent) {
            return $this->form($input, ['form' => 'Sorry, your message could not be sent right now. Please try again later.']);
        }

        $this->app->flash->success('Thanks! Your message has been sent.');

        return $this->redirect('/contact');
    }

    /**
     * @param array{name: string, email: string, subject: string, message: string} $input
     * @param array<string, string> $errors
     */
    private function form(array $input, array $errors): Response
    {
        return $this->render('contact', [
            'title' => 'Contact me',
            'input' => $input,
            'errors' => $errors,
            'useCaptcha' => true,
        ], $errors === [] ? 200 : 422);
    }
}
