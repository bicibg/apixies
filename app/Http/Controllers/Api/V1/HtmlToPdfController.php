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
        // Detect if it's a complete HTML document
        $isCompleteHtml = str_contains(strtolower($html), '<!doctype') || str_contains(strtolower($html), '<html');

        if ($isCompleteHtml) {
            // Use original HTML with centering wrapper
            $processedHtml = $this->centerInvoiceHtml($html);
        } else {
            // For fragments, use template
            $processedHtml = $this->wrapInTemplate($html);
        }

        $pdfContent = Browsershot::html($processedHtml)
            ->noSandbox()
            ->setOption('args', [
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-web-security',
                '--disable-extensions',
                '--no-first-run',
                '--disable-default-apps',
                '--single-process'
            ])
            ->timeout(60)
            ->waitUntilNetworkIdle(false)
            ->setDelay(2000)
            ->emulateMedia('screen')
            ->showBackground()
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->setOption('viewport', ['width' => 892, 'height' => 1262]) // Match the original design
            ->pdf();

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'X-Frame-Options' => 'ALLOWALL',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Generate PDF for sandbox mode
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
            ->timeout(45)
            ->waitUntilNetworkIdle(false)
            ->setDelay(1000)
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
     * Wrap invoice HTML with centering container
     */
    private function centerInvoiceHtml(string $html): string
    {
        // Fix fonts first
        $html = $this->fixFontsOnly($html);

        // Wrap the entire content in a centering container
        $centeredHtml = <<<HTML
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="" xml:lang="">
<head>
    <title>Payment Confirmation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        /* Reset and centering styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background: white;
            font-family: Arial, sans-serif;
        }

        .pdf-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px 0;
            background: white;
        }

        .invoice-wrapper {
            width: 892px;
            min-height: 1262px;
            position: relative;
            background: white;
            margin: 0 auto;
            /* Scale down if needed */
            transform: scale(0.85);
            transform-origin: top center;
        }

        /* Preserve all original positioning */
        .invoice-wrapper * {
            font-family: Arial, sans-serif !important;
        }

        @media print {
            .pdf-container {
                width: 100%;
                height: 100%;
                padding: 0;
            }
            .invoice-wrapper {
                transform: scale(1);
                margin: 0;
            }
        }
    </style>
HTML;

        // Extract the content between <body> tags from the original HTML
        preg_match('/<body[^>]*>(.*?)<\/body>/s', $html, $bodyMatch);
        $bodyContent = $bodyMatch[1] ?? $html;

        // Extract styles from the original HTML
        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $styleMatches);
        $originalStyles = implode("\n", $styleMatches[1] ?? []);

        $centeredHtml .= "<style>\n" . $originalStyles . "\n</style>";
        $centeredHtml .= <<<HTML
</head>
<body>
    <div class="pdf-container">
        <div class="invoice-wrapper">
            $bodyContent
        </div>
    </div>
</body>
</html>
HTML;

        return $centeredHtml;
    }

    /**
     * Only fix fonts, keep everything else intact
     */
    private function fixFontsOnly(string $html): string
    {
        // Replace font URLs with fallback
        $html = str_replace(
            "font-family: 'GT Walsheim';",
            "font-family: Arial, sans-serif;",
            $html
        );

        // Remove @font-face declarations that might cause loading issues
        $html = preg_replace('/@font-face\s*\{[^}]*\}/s', '', $html);

        return $html;
    }

    /**
     * Simple template wrapper
     */
    private function wrapInTemplate(string $html): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Document</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Apixies.io PDF Converter</h1>
        <div class="nav">
            <a href="#">Home</a>
            <a href="#">API</a>
        </div>
    </div>
    <div class="container">
        $html
    </div>
</body>
</html>
HTML;
    }
}
