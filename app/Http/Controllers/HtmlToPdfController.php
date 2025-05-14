<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        // Use the HTML exactly as provided without modifications
        $html = $request->html;

        try {
            // Create a PDF with minimal processing
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->emulateMedia('screen')
                ->showBackground()
                ->pdf();

            // Return the PDF content directly, no caching
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'no-cache',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF generation failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
