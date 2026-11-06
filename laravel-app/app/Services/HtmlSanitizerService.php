<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizerService
{
    protected static ?HTMLPurifier $purifier = null;

    /**
     * Sanitize HTML content with a strict allowlist.
     * Restricts CSS properties, removes data: URIs, removes external images, and strips XSS vectors,
     * while preserving document editor attributes (data-draggable, data-owner-id, data-file-path).
     */
    public static function clean(?string $dirtyHtml): ?string
    {
        if ($dirtyHtml === null || trim($dirtyHtml) === '') {
            return $dirtyHtml;
        }

        if (self::$purifier === null) {
            $cacheDir = storage_path('framework/cache/purifier');
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('Cache.SerializerPath', $cacheDir);

            $config->set('HTML.DefinitionID', 'trms-html-purifier');
            $config->set('HTML.DefinitionRev', 1);

            // Allowed HTML elements and attributes
            $allowedHtml = implode(',', [
                'p[style|class|id|dir]',
                'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup',
                'span[style|class|id|data-handle]',
                'div[style|class|id|data-draggable|data-owner-id|data-signer-name]',
                'h1[style|class]', 'h2[style|class]', 'h3[style|class]',
                'h4[style|class]', 'h5[style|class]', 'h6[style|class]',
                'hr', 'br',
                'ul[style|class]', 'ol[style|class]', 'li[style|class]',
                'table[style|class|border|cellpadding|cellspacing|width]',
                'thead[style|class]', 'tbody[style|class]', 'tfoot[style|class]',
                'tr[style|class]',
                'th[style|class|colspan|rowspan|width]',
                'td[style|class|colspan|rowspan|width]',
                'blockquote[style|class]', 'pre[style|class]', 'code[style|class]',
                'img[src|alt|title|width|height|style|class|data-file-path]',
            ]);
            $config->set('HTML.Allowed', $allowedHtml);

            // Allowed CSS properties
            $config->set('CSS.AllowedProperties', [
                'text-align', 'font-weight', 'font-style', 'text-decoration',
                'font-size', 'font-family', 'line-height', 'letter-spacing',
                'color', 'background-color',
                'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'width', 'min-width', 'max-width', 'height', 'min-height', 'max-height',
                'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
                'border-collapse', 'border-spacing', 'border-color', 'border-style', 'border-width',
                'vertical-align', 'float', 'clear',
            ]);

            // Allowed schemes: only http and https (no data: URIs)
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
            ]);

            // Disable external resources (only allow relative paths or same host)
            $appHost = parse_url(config('app.url', 'http://localhost:8000'), PHP_URL_HOST) ?: 'localhost';
            $config->set('URI.Host', $appHost);
            $config->set('URI.DisableExternalResources', true);

            // Register custom HTML5 data-* attributes (MUST occur after all $config->set calls)
            if ($def = $config->maybeGetRawHTMLDefinition()) {
                $def->addAttribute('div', 'data-draggable', 'Text');
                $def->addAttribute('div', 'data-owner-id', 'Text');
                $def->addAttribute('div', 'data-signer-name', 'Text');
                $def->addAttribute('span', 'data-handle', 'Text');
                $def->addAttribute('img', 'data-file-path', 'Text');
            }

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier->purify($dirtyHtml);
    }
}
