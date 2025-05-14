<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        // Get the HTML content
        $html = $request->input('html');

        try {
            // Wrap the HTML with proper doctype and structure if it doesn't have it
            if (!preg_match('/<html/i', $html)) {
                $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document</title>
    <style>
        /* Basic reset to ensure consistent styling */
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
        }

        /* Add any additional default styling here */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
{$html}
</body>
</html>
HTML;
            }

            // Generate PDF with proper browser settings
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->emulateMedia('screen')
                ->showBackground()
                ->format('A4')
                ->pdf();

            // Return the PDF with proper headers
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"',
                'X-Frame-Options' => 'ALLOWALL',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
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
