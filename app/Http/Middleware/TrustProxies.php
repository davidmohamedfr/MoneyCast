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
        // Trust Docker internal networks and private IP ranges
        // Docker default bridge: 172.17.0.0/16
        // Docker user-defined networks: 172.16.0.0/12
        // Private networks: 192.168.0.0/16, 10.0.0.0/8
        $this->proxies = config('app.env') === 'production'
            ? null  // Trust no proxies by default in production (must configure explicitly)
            : ['172.16.0.0/12', '192.168.0.0/16', '10.0.0.0/8'];  // Docker + private networks
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
