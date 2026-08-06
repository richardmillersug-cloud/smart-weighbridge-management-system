@props(['title' => 'Document'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #111827;
            background: #f3f4f6;
            padding: 24px;
        }
        .sheet {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d1d5db;
            padding: 32px 40px;
        }
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #111827; padding-bottom: 16px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .muted { color: #6b7280; }
        .doc-type { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; text-align: right; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin: 16px 0; }
        .meta-grid dt { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
        .meta-grid dd { font-weight: 600; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #111827; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 8px 10px; text-align: left; }
        td { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; }
        .totals { margin-left: auto; width: 280px; }
        .totals td { padding: 6px 10px; }
        .totals .grand { font-size: 16px; font-weight: 700; border-top: 2px solid #111827; }
        .sign-row { display: flex; justify-content: space-between; gap: 40px; margin-top: 48px; }
        .sign-box { flex: 1; border-top: 1px solid #9ca3af; padding-top: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; text-align: center; }
        .footer-note { margin-top: 32px; font-size: 11px; color: #9ca3af; text-align: center; }
        .print-bar { max-width: 720px; margin: 0 auto 16px; display: flex; justify-content: space-between; }
        .print-bar a, .print-bar button {
            display: inline-block; padding: 8px 18px; border: 1px solid #374151; border-radius: 6px;
            background: #111827; color: #fff; font-size: 13px; cursor: pointer; text-decoration: none;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: none; max-width: none; padding: 0; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-bar">
        <a href="javascript:history.back()">&larr; Back</a>
        <button onclick="window.print()">Print</button>
    </div>
    <div class="sheet">
        {{ $slot }}
    </div>
</body>
</html>
