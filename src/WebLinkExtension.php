<?php

namespace HelloNico\Twig;

use Psr\Link\LinkProviderInterface;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Symfony\Component\WebLink\Link;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebLinkExtension extends AbstractExtension
{
    private $linkProvider;

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('wp_footer', [$this, 'send_headers'], PHP_INT_MAX);
    }

    /**
     * Send headers.
     */
    public function send_headers()
    {
        if (headers_sent() || !$this->linkProvider instanceof LinkProviderInterface) {
            return;
        }

        $links = $this->linkProvider->getLinks();
        if (empty($links)) {
            return;
        }

        header(sprintf('Link: %s', (new HttpHeaderSerializer())->serialize($links)), false);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('link', [$this, 'link']),
            new TwigFunction('preload', [$this, 'preload']),
            new TwigFunction('dns_prefetch', [$this, 'dnsPrefetch']),
            new TwigFunction('preconnect', [$this, 'preconnect']),
            new TwigFunction('prefetch', [$this, 'prefetch']),
            new TwigFunction('prerender', [$this, 'prerender']),
        ];
    }

    /**
     * Adds a "Link" HTTP header.
     *
     * @param string $uri        The relation URI
     * @param string $rel        The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch")
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The relation URI
     */
    public function link($uri, $rel, array $attributes = [])
    {
        $link = new Link($rel, $uri);
        foreach ($attributes as $key => $value) {
            $link = $link->withAttribute($key, $value);
        }

        $linkProvider = $this->linkProvider ?? new GenericLinkProvider();
        $this->linkProvider = $linkProvider->withLink($link);

        return $uri;
    }

    /**
     * Preloads a resource.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('crossorigin' => 'use-credentials')")
     *
     * @return string The path of the asset
     */
    public function preload($uri, array $attributes = [])
    {
        return $this->link($uri, 'preload', $attributes);
    }

    /**
     * Resolves a resource origin as early as possible.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function dnsPrefetch($uri, array $attributes = [])
    {
        return $this->link($uri, 'dns-prefetch', $attributes);
    }

    /**
     * Initiates a early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function preconnect($uri, array $attributes = [])
    {
        return $this->link($uri, 'preconnect', $attributes);
    }

    /**
     * Indicates to the client that it should prefetch this resource.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function prefetch($uri, array $attributes = [])
    {
        return $this->link($uri, 'prefetch', $attributes);
    }

    /**
     * Indicates to the client that it should prerender this resource .
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function prerender($uri, array $attributes = [])
    {
        return $this->link($uri, 'prerender', $attributes);
    }
}
