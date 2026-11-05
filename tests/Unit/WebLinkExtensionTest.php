<?php

declare(strict_types=1);

namespace n5s\TimberWebLink\Tests\Unit;

use n5s\TimberWebLink\WebLinkExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Link\LinkInterface;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class WebLinkExtensionTest extends TestCase
{
    private WebLinkExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new WebLinkExtension();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function functions(): iterable
    {
        yield 'preload' => ['preload', 'preload'];
        yield 'dns_prefetch' => ['dns_prefetch', 'dns-prefetch'];
        yield 'preconnect' => ['preconnect', 'preconnect'];
        yield 'prefetch' => ['prefetch', 'prefetch'];
        yield 'prerender' => ['prerender', 'prerender'];
    }

    #[DataProvider('functions')]
    public function testFunctionAddsALinkAndReturnsTheUri(string $function, string $rel): void
    {
        $this->assertSame('/app.css', $this->render("{{ {$function}('/app.css') }}"));
        $this->assertSame('</app.css>; rel="' . $rel . '"', $this->header());
    }

    public function testLinkTakesTheRelation(): void
    {
        $this->assertSame('/feed', $this->render("{{ link('/feed', 'alternate') }}"));
        $this->assertSame('</feed>; rel="alternate"', $this->header());
    }

    public function testAttributes(): void
    {
        $this->render("{{ preload('/font.woff2', {as: 'font', type: 'font/woff2', crossorigin: true}) }}");

        $this->assertSame('</font.woff2>; rel="preload"; as="font"; type="font/woff2"; crossorigin', $this->header());
    }

    public function testLinksAddUp(): void
    {
        $this->render("{{ preload('/app.css', {as: 'style'}) }}{{ preconnect('https://fonts.example') }}");
        $this->render("{{ preload('/app.js', {as: 'script'}) }}");

        $this->assertSame(
            '</app.css>; rel="preload"; as="style",<https://fonts.example>; rel="preconnect",</app.js>; rel="preload"; as="script"',
            $this->header()
        );
        $this->assertSame(
            ['/app.css', '/app.js'],
            \array_map(
                static fn (LinkInterface $link): string => $link->getHref(),
                \array_values($this->extension->getLinksByRel('preload'))
            )
        );
    }

    public function testNoLinks(): void
    {
        $this->assertSame([], $this->extension->getLinks());
        $this->assertNull($this->header());
    }

    private function render(string $template): string
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]));
        $twig->addExtension($this->extension);

        return $twig->render('template');
    }

    private function header(): ?string
    {
        return (new HttpHeaderSerializer())->serialize($this->extension->getLinks());
    }
}
