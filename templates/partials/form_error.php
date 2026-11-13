<?php
/** @var array<string, string> $errors  @var string $field */
?>
<?php if (isset($errors[$field])): ?>
    <p class="field-error" id="<?= e($field) ?>-error"><?= e($errors[$field]) ?></p>
<?php endif; ?>
