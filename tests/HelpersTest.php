<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testEscapesHtmlAndQuotes(): void
    {
        $this->assertSame('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', e("<script>alert('x')</script>"));
        $this->assertSame('', e(null));
    }

    public function testFormatsDatesInFrench(): void
    {
        $this->assertSame('3 août 2025', format_date('2025-08-03'));
        $this->assertSame('1 janvier 2026', format_date('2026-01-01 10:00:00'));
    }

    public function testPluralizes(): void
    {
        $this->assertSame('1 ville', plural(1, 'ville'));
        $this->assertSame('3 villes', plural(3, 'ville'));
        $this->assertSame('0 ville', plural(0, 'ville'));
    }

    public function testDrawsStars(): void
    {
        $this->assertSame('★★★☆☆', stars(3));
    }
}
