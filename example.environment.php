<?php
/*
 * Production configuration (legacy format).
 *
 * Copy to ../environment.php on the server (one level above the checkout);
 * deploy-ideas-website.sh copies it into place and the container mounts it
 * read-only. Never commit the real file.
 *
 * Every value can instead be supplied as an environment variable — the names
 * in brackets take precedence over this file.
 */

// Database [DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD]
$_ENV["HOST"] = "DB IP address or hostname";
$_ENV["PORT"] = "3306";
$_ENV["DATABASE"] = "DB name";
$_ENV["USERNAME"] = "DB username";
$_ENV["PASSWORD"] = "DB password";

// Gmail SMTP [MAIL_USERNAME, MAIL_PASSWORD]
$_ENV["EMAIL"] = "Gmail address used to send mail";
$_ENV["EMAIL_PASSWORD"] = "Gmail app password";

// Google reCAPTCHA v2 secret [CAPTCHA_SECRET_KEY]
$_ENV["CAPTCHA_SECRET_KEY"] = "Google reCAPTCHA secret key";

// Optional
// $_ENV["APP_URL"] = "https://ideas.adiulalamadil.me";   // absolute links in emails
// $_ENV["CONTACT_TO"] = "where contact-form messages go"; // defaults to EMAIL
// $_ENV["CAPTCHA_SITE_KEY"] = "public reCAPTCHA site key"; // defaults to the existing key
