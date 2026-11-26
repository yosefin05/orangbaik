<?php

namespace App\\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'blockquote', 'h2', 'h3', 'h4', 'a', 'img', 'div', 'span',
    ];

    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'textarea', 'select', 'option',
        'video', 'audio', 'source', 'meta', 'link',
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="rich-text-root">' . $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $root = $document->getElementsByTagName('div')->item(0);
            if (!$root) {
                return '';
            }

            self::sanitizeChildren($root);

            $result = '';
            foreach ($root->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }

            return $result;
        } catch (\\Throwable) {
            // Fail closed: malformed rich text must never be rendered as raw HTML.
            return e(strip_tags($html));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (!$node instanceof DOMElement) {
                if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
                    $parent->removeChild($node);
                }
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($node);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                self::sanitizeChildren($node);
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                continue;
            }

            self::sanitizeAttributes($node, $tag);
            self::sanitizeChildren($node);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = match ($tag) {
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if (!in_array($name, $allowed, true)) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $href = trim($element->getAttribute('href'));
            if (!self::isSafeLink($href)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
            }
        }

        if ($tag === 'img') {
            $src = trim($element->getAttribute('src'));
            if (!self::isSafeImageSource($src)) {
                $element->parentNode?->removeChild($element);
                return;
            }

            foreach (['width', 'height'] as $dimension) {
                if ($element->hasAttribute($dimension)) {
                    $value = $element->getAttribute($dimension);
                    if (!preg_match('/^[0-9]{1,4}$/', $value)) {
                        $element->removeAttribute($dimension);
                    }
                }
            }
        }
    }

    private static function isSafeLink(string $url): bool
    {
        if ($url === '' || preg_match('/[\\x00-\\x20]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return !str_starts_with($url, '//');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private static function isSafeImageSource(string $url): bool
    {
        if ($url === '' || preg_match('/[\\x00-\\x20]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/storage/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }
}
