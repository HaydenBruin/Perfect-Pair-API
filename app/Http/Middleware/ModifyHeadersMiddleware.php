<?php

namespace App\Http\Middleware;

use Closure;

class ModifyHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        $response = $next( $request );
        $response->header( 'Access-Control-Allow-Origin', 'https://perfectpair.nz' );
        $response->header( 'Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT' );
        $response->header( 'Access-Control-Allow-Headers', 'Origin, Content-Type' );
        $response->header( 'Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
