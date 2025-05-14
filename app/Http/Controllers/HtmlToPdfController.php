<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        // Use the HTML exactly as provided
        $html = $request->html;

        try {
            // Create a PDF with minimal processing
            // These settings more closely match Postman's PDF display
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->emulateMedia('screen')
                ->showBackground()
                ->format('A4')  // Standard paper size
                ->margins(10, 10, 10, 10)  // Minimal margins
                ->disableJavascript()  // Disable JS to match simpler rendering in Postman
                ->pdf();

            // Stream the PDF directly
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
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
