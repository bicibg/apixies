<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class TrustProxies extends Middleware
{
    /**
     * The proxies that should be trusted.
     *
     * Using a simple string as default value initially
     */
    protected $proxies = '127.0.0.1,::1';

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Initialize the middleware properly using config.
     */
    public function __construct()
    {
        $this->proxies = config('app.trusted_proxies', '127.0.0.1,::1');
    }

    /**
     * Sets the trusted proxies on the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function setTrustedProxies(\Illuminate\Http\Request $request): void
    {
        $proxies = $this->proxies;

        if (is_string($proxies) && $proxies !== '*') {
            $proxies = array_map('trim', explode(',', $proxies));
        }

        $request::setTrustedProxies(
            $proxies === '*' ? ['127.0.0.1', '::1'] : $proxies,
            $this->getTrustedHeaderNames()
        );
    }
}
