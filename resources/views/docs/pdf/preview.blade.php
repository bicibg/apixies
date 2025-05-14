<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PDF Preview</title>

    {{-- Load your compiled CSS so the PDF is styled correctly --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Any other assets your layout needs --}}
</head>
<body class="font-sans text-gray-900">
{{-- Insert the user’s HTML safely --}}
{!! $content !!}
</body>
</html>
