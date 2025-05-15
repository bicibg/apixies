<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        // Get the HTML content
        $html = $request->input('html');

        // Check if this is a sandbox request
        $isSandbox = $request->attributes->get('sandbox_mode', false);

        try {
            if ($isSandbox) {
                Log::info('Processing HTML to PDF in sandbox mode');
                return $this->generateSandboxPdf($html);
            } else {
                // Regular API mode
                return $this->generateApiPdf($html);
            }
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'PDF generation failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF for API calls
     */
    private function generateApiPdf(string $html): \Illuminate\Http\Response
    {
        // For API calls, use standard Browsershot without additional CSS
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
    }

    /**
     * Generate PDF for sandbox mode with proper CSS
     */
    private function generateSandboxPdf(string $html): \Illuminate\Http\Response
    {
        // Add base HTML structure and reset CSS for sandbox mode
        $fullHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document</title>
    <style>
        /* CSS Reset to remove Apixies global styles */
        html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym,
        address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var,
        b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead,
        tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav,
        output, ruby, section, summary, time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }

        /* Document defaults */
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: white;
        }

        /* Basic element styling */
        h1, h2, h3, h4, h5, h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: bold;
        }

        h1 { font-size: 2em; }
        h2 { font-size: 1.5em; }
        h3 { font-size: 1.3em; }
        h4 { font-size: 1.1em; }

        p { margin-bottom: 1em; }

        ul, ol {
            margin-left: 2em;
            margin-bottom: 1em;
        }

        /* Table styling */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 1em;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Links */
        a {
            color: #0066cc;
            text-decoration: underline;
        }

        /* Code blocks */
        pre, code {
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            background-color: #f5f5f5;
            padding: 0.2em 0.4em;
            border-radius: 3px;
        }

        pre {
            padding: 1em;
            overflow: auto;
            margin-bottom: 1em;
        }

        /* Ensure page breaks don't cut elements awkwardly */
        h1, h2, h3, h4, h5, h6 {
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        table, figure {
            page-break-inside: avoid;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
$html
</body>
</html>
HTML;

        // Create a temporary file to ensure CSS is properly applied
        $tempFilename = 'temp_' . uniqid() . '.html';
        Storage::disk('local')->put($tempFilename, $fullHtml);
        $tempFilePath = Storage::disk('local')->path($tempFilename);

        try {
            // Generate PDF from the file to ensure proper CSS application
            $pdfContent = Browsershot::url('file://' . $tempFilePath)
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
        } finally {
            // Clean up temporary file
            Storage::disk('local')->delete($tempFilename);
        }
    }
}
