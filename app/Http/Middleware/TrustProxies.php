<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class TrustProxies extends Middleware
{
    /**
     * The proxies that should be trusted.
     *
     * You can either list them explicitly, or use '*' to trust all.
     * Here we trust only the local proxy, but you can also use '*'.
     */
    protected $proxies = ['127.0.0.1', '::1'];
    // or: protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
