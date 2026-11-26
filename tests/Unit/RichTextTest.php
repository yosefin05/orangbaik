<?php

namespace Tests\\Unit;

use App\\Support\\RichText;
use PHPUnit\\Framework\\TestCase;

class RichTextTest extends TestCase
{
    public function test_removes_executable_elements_and_event_handlers(): void
    {
        $clean = RichText::clean(
            '<p onclick="alert(1)">Hello<script>alert(2)</script><img src="/storage/safe.jpg" onerror="alert(3)"></p>'
        );

        $this->assertStringContainsString('Hello', $clean);
        $this->assertStringContainsString('/storage/safe.jpg', $clean);
        $this->assertStringNotContainsString('<script', strtolower($clean));
        $this->assertStringNotContainsString('onclick', strtolower($clean));
        $this->assertStringNotContainsString('onerror', strtolower($clean));
        $this->assertStringNotContainsString('alert(', strtolower($clean));
    }

    public function test_rejects_javascript_links_and_unsafe_image_sources(): void
    {
        $clean = RichText::clean(
            '<p><a href="javascript:alert(1)">Open</a><img src="data:text/html;base64,PHNjcmlwdD4="></p>'
        );

        $this->assertStringContainsString('Open', $clean);
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringNotContainsString('<img', strtolower($clean));
    }

    public function test_preserves_safe_links_and_adds_safe_blank_target_rel(): void
    {
        $clean = RichText::clean('<a href="https://example.com" target="_blank">Source</a>');

        $this->assertStringContainsString('https://example.com', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_empty_content_returns_empty_string(): void
    {
        $this->assertSame('', RichText::clean(null));
        $this->assertSame('', RichText::clean('   '));
    }
}
