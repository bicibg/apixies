<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
//        $requestId = app('X-Request-ID');
//
//        Log::info('Incoming request', [
//            'id'=>$requestId,
//            'method'  => $request->method(),
//            'url'     => $request->fullUrl(),
//            'payload' => $request->all(),
//        ]);
//
//        $response = $next($request);
//
//        // Safely grab status and content
//        $status  = $response instanceof SymfonyResponse
//            ? $response->getStatusCode()
//            : ($response->status() ?? null);
//
//        $content = method_exists($response, 'getContent')
//            ? $response->getContent()
//            : '[non-string response]';
//
//        Log::info('Outgoing response', [
//            'id'=>$requestId,
//            'status'   => $status,
//            'response' => $content,
//        ]);
//
//        return $response;
        $next($request);
    }
}
