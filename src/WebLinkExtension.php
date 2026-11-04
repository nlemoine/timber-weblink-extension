<?php

declare(strict_types=1);

namespace n5s\TimberWebLink;

use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Symfony\Component\WebLink\Link;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig functions that collect links, sent as a Link HTTP header on wp_footer.
 *
 * @phpstan-type Attributes array<string, scalar|\Stringable|list<scalar|\Stringable>>
 */
final class WebLinkExtension extends AbstractExtension
{
    private GenericLinkProvider $linkProvider;

    public function __construct()
    {
        $this->linkProvider = new GenericLinkProvider();

        \add_action('wp_footer', [$this, 'send_headers'], \PHP_INT_MAX);
    }

    /**
     * Sends the collected links as a Link HTTP header.
     */
    public function send_headers(): void
    {
        if (\headers_sent()) {
            return;
        }

        // Symfony documents the parameter as LinkInterface[]|\Traversable, which PHPStan narrows to Traversable.
        // @phpstan-ignore argument.type
        $header = (new HttpHeaderSerializer())->serialize($this->linkProvider->getLinks());
        if ($header === null) {
            return;
        }

        \header('Link: ' . $header, false);
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('link', $this->link(...)),
            new TwigFunction('preload', $this->preload(...)),
            new TwigFunction('dns_prefetch', $this->dnsPrefetch(...)),
            new TwigFunction('preconnect', $this->preconnect(...)),
            new TwigFunction('prefetch', $this->prefetch(...)),
            new TwigFunction('prerender', $this->prerender(...)),
        ];
    }

    /**
     * Adds a link to the Link HTTP header.
     *
     * @param string $rel The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch").
     * @param Attributes $attributes The attributes of this link (e.g. `{as: 'style'}`, `{crossorigin: 'use-credentials'}`).
     *
     * @return string The URI, unchanged.
     */
    public function link(string $uri, string $rel, array $attributes = []): string
    {
        $link = new Link($rel, $uri);
        foreach ($attributes as $attribute => $value) {
            $link = $link->withAttribute($attribute, $value);
        }

        $this->linkProvider = $this->linkProvider->withLink($link);

        return $uri;
    }

    /**
     * Preloads a resource.
     *
     * @param Attributes $attributes The attributes of this link (e.g. `{as: 'style'}`, `{crossorigin: 'use-credentials'}`).
     *
     * @return string The URI, unchanged.
     */
    public function preload(string $uri, array $attributes = []): string
    {
        return $this->link($uri, 'preload', $attributes);
    }

    /**
     * Resolves a resource origin as early as possible.
     *
     * @param Attributes $attributes The attributes of this link.
     *
     * @return string The URI, unchanged.
     */
    public function dnsPrefetch(string $uri, array $attributes = []): string
    {
        return $this->link($uri, 'dns-prefetch', $attributes);
    }

    /**
     * Initiates an early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
     *
     * @param Attributes $attributes The attributes of this link (e.g. `{crossorigin: true}`).
     *
     * @return string The URI, unchanged.
     */
    public function preconnect(string $uri, array $attributes = []): string
    {
        return $this->link($uri, 'preconnect', $attributes);
    }

    /**
     * Indicates to the client that it should prefetch this resource.
     *
     * @param Attributes $attributes The attributes of this link.
     *
     * @return string The URI, unchanged.
     */
    public function prefetch(string $uri, array $attributes = []): string
    {
        return $this->link($uri, 'prefetch', $attributes);
    }

    /**
     * Indicates to the client that it should prerender this resource.
     *
     * @param Attributes $attributes The attributes of this link.
     *
     * @return string The URI, unchanged.
     */
    public function prerender(string $uri, array $attributes = []): string
    {
        return $this->link($uri, 'prerender', $attributes);
    }
}
