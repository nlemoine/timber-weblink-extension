<?php

declare(strict_types=1);

namespace n5s\TimberWebLink;

use Psr\Link\LinkInterface;
use Psr\Link\LinkProviderInterface;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig functions that collect links, sent as a Link HTTP header by send_headers().
 *
 * @phpstan-type Attributes array<string, scalar|\Stringable|list<scalar|\Stringable>>
 */
final class WebLinkExtension extends AbstractExtension implements LinkProviderInterface
{
    private GenericLinkProvider $linkProvider;

    public function __construct()
    {
        $this->linkProvider = new GenericLinkProvider();
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
     *
     * @throws \InvalidArgumentException When the URI is empty.
     */
    public function link(string $uri, string $rel, array $attributes = []): string
    {
        // Twig passes false, which many WordPress functions return, as an empty string.
        if ($uri === '') {
            throw new \InvalidArgumentException('The URI of a link must not be empty.');
        }

        $link = new Link($rel, $uri);
        foreach ($attributes as $attribute => $value) {
            $link = $link->withAttribute($attribute, $value);
        }

        // A template can be rendered several times in a page, for example in a loop: send each link once.
        if (!$this->hasLink($link)) {
            $this->linkProvider = $this->linkProvider->withLink($link);
        }

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

    /**
     * @return array<LinkInterface>
     */
    public function getLinks(): array
    {
        return $this->linkProvider->getLinks();
    }

    /**
     * @return array<LinkInterface>
     */
    public function getLinksByRel(string $rel): array
    {
        return $this->linkProvider->getLinksByRel($rel);
    }

    private function hasLink(LinkInterface $link): bool
    {
        foreach ($this->linkProvider->getLinks() as $existing) {
            if (
                $existing->getHref() === $link->getHref()
                && $existing->getRels() === $link->getRels()
                && $existing->getAttributes() === $link->getAttributes()
            ) {
                return true;
            }
        }

        return false;
    }
}
