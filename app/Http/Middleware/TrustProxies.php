<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Create a new middleware instance.
     */
    public function __construct()
    {
        // Trust all proxies in local/development environments
        // In production, this should be set to specific proxy IPs or CIDR ranges
        $this->proxies = config('app.env') === 'production'
            ? null  // Trust no proxies by default in production
            : '*';  // Trust all proxies in development (Docker nginx)
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
