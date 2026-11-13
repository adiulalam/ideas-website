<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitises idea text, which may contain a small amount of inline HTML.
 *
 * Only basic inline formatting and links are allowed. Text is cleaned when it
 * is saved and again when it is displayed, so rows written before this
 * policy existed are safe too.
 */
final class HtmlSanitizer
{
    private const ALLOWED = 'a[href|title],b,strong,i,em,u,br';

    /** @var array<string, HTMLPurifier> */
    private array $purifiers = [];

    public function __construct(private readonly ?string $cacheDir = null)
    {
    }

    /** Clean user input before storing it. */
    public function clean(string $html): string
    {
        return trim($this->purifier(false)->purify($html));
    }

    /** Clean stored text for display; also turns bare URLs into links. */
    public function render(string $html): string
    {
        return trim($this->purifier(true)->purify($html));
    }

    /** Plain-text version, e.g. for page titles, emails and emptiness checks. */
    public function toPlainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($this->clean($html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function purifier(bool $linkify): HTMLPurifier
    {
        $key = $linkify ? 'display' : 'storage';
        if (isset($this->purifiers[$key])) {
            return $this->purifiers[$key];
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', self::ALLOWED);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $config->set('AutoFormat.Linkify', $linkify);

        $cacheDir = $this->cacheDir ?? sys_get_temp_dir() . '/htmlpurifier';
        if (is_dir($cacheDir) || @mkdir($cacheDir, 0700, true)) {
            $config->set('Cache.SerializerPath', $cacheDir);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        return $this->purifiers[$key] = new HTMLPurifier($config);
    }
}
