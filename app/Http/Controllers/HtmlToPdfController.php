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

        // Render a Blade wrapper that includes your CSS
        $fullHtml = view('docs.pdf.wrapper', [
            'content' => $userHtml,
        ])->render();

        $pdfContent = Browsershot::url(route('pdf.preview', ['html'=> base64_encode($fullHtml)]))
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->showBackground()
            ->pdf();


        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'X-Frame-Options' => 'ALLOWALL',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

}
