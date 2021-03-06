<?php

namespace Psr7Middlewares\Middleware;

use Psr7Middlewares\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware to redirect to https protocol.
 */
class Https
{
    use Utils\RedirectTrait;

    const HEADER = 'Strict-Transport-Security';

    /**
     * @param int One year by default
     */
    private $maxAge = 31536000;

    /**
     * @param bool Whether include subdomains
     */
    private $includeSubdomains = false;

    /**
     * Set basic config.
     */
    public function __construct()
    {
        $this->redirect(301);
    }

    /**
     * Configure the max-age HSTS in seconds.
     *
     * @param int $maxAge
     * 
     * @return self
     */
    public function maxAge($maxAge)
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Configure the includeSubDomains HSTS directive.
     *
     * @param bool $includeSubdomains
     * 
     * @return self
     */
    public function includeSubdomains($includeSubdomains = true)
    {
        $this->includeSubdomains = $includeSubdomains;

        return $this;
    }

    /**
     * Execute the middleware.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $uri = $request->getUri();

        if (strtolower($uri->getScheme()) !== 'https') {
            return self::getRedirectResponse($this->redirectStatus, $uri->withScheme('https'), $response);
        }

        if (!empty($this->maxAge)) {
            $response = $response->withHeader(self::HEADER, sprintf('max-age=%d%s', $this->maxAge, $this->includeSubdomains ? ';includeSubDomains' : ''));
        }

        return $next($request, $response);
    }
}
