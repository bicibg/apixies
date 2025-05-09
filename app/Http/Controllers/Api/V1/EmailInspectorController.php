<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmailInspectorService;
use App\Helpers\ApiResponse;         // ← import the helper
use Symfony\Component\HttpFoundation\Response;

class EmailInspectorController extends Controller
{
    protected EmailInspectorService $inspector;

    public function __construct(EmailInspectorService $inspector)
    {
        $this->inspector = $inspector;
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
        ]);

        // perform the inspection
        $result = $this->inspector->inspect($request->input('email'));

        // wrap it in our standard API envelope
        return ApiResponse::success(
            $result,
            'Email inspection successful',
            Response::HTTP_OK
        );
    }
}
