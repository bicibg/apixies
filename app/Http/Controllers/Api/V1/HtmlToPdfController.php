<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Post(
 *     path="/api/v1/html-to-pdf",
 *     summary="HTML to PDF Converter",
 *     description="Convert HTML content to a PDF document with proper formatting and styling",
 *     operationId="htmlToPdf",
 *     tags={"converter"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"html"},
 *             @OA\Property(
 *                 property="html",
 *                 type="string",
 *                 example="<h1>Hello World</h1><p>Sample content</p>"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PDF document generated successfully",
 *         @OA\MediaType(
 *             mediaType="application/pdf"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
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
        $fullHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: white;
        }
        .header {
            background: linear-gradient(to right, #2e51a2, #6e8cd5);
            color: white;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
        }
        .container {
            padding: 20px;
        }
        .nav {
            margin-top: 15px;
        }
        .nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #4c6eaf;
            color: white;
            text-align: left;
            padding:.75rem;
        }
        td {
            padding: .75rem;
            border-top: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            background-color: #f96052;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Apixies.io PDF Converter Test</h1>
        <div class="nav">
            <a href="#">Home</a>
            <a href="#">Docs</a>
            <a href="#">API</a>
            <a href="#">Contact</a>
        </div>
    </div>
    <div class="container">
        $html
    </div>
</body>
</html>
HTML;

        $pdfContent = Browsershot::html($fullHtml)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->showBackground()
            ->format('A4')
            ->pdf();

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

        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: white;
        }

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

        a {
            color: #0066cc;
            text-decoration: underline;
        }

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

        $pdfContent = Browsershot::html($fullHtml)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->showBackground()
            ->format('A4')
            ->pdf();

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'X-Frame-Options' => 'ALLOWALL',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
