<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);
        $userHtml = $request->html;

        // Render a full HTML document (head + body) via Blade
        $fullHtml = view('docs.pdf.wrapper', ['content' => $userHtml])->render();

        $pdf = Browsershot::html($fullHtml)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->showBackground()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'X-Frame-Options'     => 'ALLOWALL',
        ]);
    }
}
