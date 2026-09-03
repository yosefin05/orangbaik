<?php

namespace App\Support;

class RichText
{
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><h4><a><img><div><span>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/\s+(?:href|src)\s*=\s*("|\')\s*javascript:[^"\']*\1/i', '', $html);
        $html = preg_replace('/\s+style\s*=\s*("|\')[^"\']*\1/i', '', $html);

        return $html;
    }
}
