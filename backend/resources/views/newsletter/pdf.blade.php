<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $composition->title }} — {{ $composition->subtitle }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: {{ (int) config('cojmip.newsletter.pdf.margins_mm', 14) }}mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        @include('newsletter.partials.sheet-styles')
    </style>
</head>
<body>
    @include('newsletter.sheet', [
        'composition' => $composition,
        'layout' => $layout,
        'context' => 'pdf',
        'density' => $density,
        'masthead' => $masthead,
    ])
</body>
</html>