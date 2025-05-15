<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\SandboxTokenController;
use Illuminate\Support\Facades\Log;

class SandboxTokenUsageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Process the request
        $response = $next($request);

        // After the request is processed, update the token usage if a sandbox token was used
        $token = $request->header('X-Sandbox-Token');

        if ($token) {
            try {
                $tokenController = new SandboxTokenController();
                $updated = $tokenController->incrementUsage($token);
                if ($updated) {
                    Log::info('Sandbox token usage updated for: ' . substr($token, 0, 8) . '...');
                }
            } catch (\Exception $e) {
                Log::error('Error updating token usage: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
