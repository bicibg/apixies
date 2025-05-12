<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\UserAgentInspectorService;

class UserAgentInspectorController extends Controller
{
    public function __invoke(Request $request, UserAgentInspectorService $inspector)
    {
        $validated = $request->validate([
            'user_agent' => ['required', 'string', 'max:1024'],
        ]);

        $data = $inspector->inspect($validated['user_agent']);

        return ApiResponse::success($data, 'User‑Agent inspection successful');
    }
}
