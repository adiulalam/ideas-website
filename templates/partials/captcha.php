<?php
/** @var App\Services\Captcha $captcha */
?>
<?php if ($captcha->enabled()): ?>
    <div class="flex justify-center">
        <div class="g-recaptcha" data-sitekey="<?= e($captcha->siteKey()) ?>" data-theme="dark"></div>
    </div>
<?php endif; ?>
