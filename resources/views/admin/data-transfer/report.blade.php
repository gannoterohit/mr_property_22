<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} Report</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f4f6f9;color:#0f172a;font-family:Arial,Helvetica,sans-serif}
        .report{max-width:1180px;margin:0 auto;padding:28px}
        .toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}
        .toolbar a,.toolbar button{display:inline-flex;min-height:38px;align-items:center;justify-content:center;border:1px solid #dbe1ea;border-radius:10px;background:#fff;color:#334155;padding:9px 14px;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer}
        .sheet{overflow:hidden;border:1px solid #dbe1ea;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.07)}
        .head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;border-bottom:1px solid #e5e7eb;padding:22px}
        h1{margin:0;font-size:24px;line-height:1.2}
        p{margin:5px 0 0;color:#64748b;font-size:12px}
        .meta{text-align:right;font-size:11px;color:#64748b}
        .table-wrap{overflow:auto}
        table{width:100%;min-width:900px;border-collapse:collapse;font-size:11px}
        th,td{padding:10px 12px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:top}
        th{background:#f8fafc;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
        td{color:#1e293b}
        tr:nth-child(even) td{background:#fcfdff}
        .count{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:5px 9px;font-size:11px;font-weight:800}
        @page{margin:12mm}
        @media print{
            body{background:#fff}
            .report{max-width:none;padding:0}
            .toolbar{display:none}
            .sheet{border:0;border-radius:0;box-shadow:none}
            .head{padding:0 0 14px}
            table{min-width:0;font-size:9px}
            th,td{padding:6px 7px}
        }
    </style>
</head>
<body>
    <main class="report">
        <div class="toolbar">
            <a href="{{ route('admin.dashboard') }}">Back to Admin</a>
            <button type="button" onclick="window.print()">Download / Save PDF</button>
        </div>
        <section class="sheet">
            <header class="head">
                <div>
                    <h1>{{ $title }} Report</h1>
                    <p>Generated from admin panel. Use browser print to save this report as PDF.</p>
                </div>
                <div class="meta">
                    <strong class="count">{{ $rows->count() }} rows</strong>
                    <p>{{ $generatedAt->format('d M Y, h:i A') }}</p>
                </div>
            </header>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ str_replace('_', ' ', $column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach($row as $value)
                                    <td>{{ is_bool($value) ? (int) $value : $value }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) }}">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
