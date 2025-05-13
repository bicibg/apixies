<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class HtmlToPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        $pdfContent = Browsershot::html($request->html)
            ->noSandbox()
            ->setWaitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->showBackground()
            ->pdf();

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="document.pdf"',
        ]);
    }



}
