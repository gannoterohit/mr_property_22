<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} Report</title>
<link rel="stylesheet" href="{{ asset('css/admin-data-transfer-report.css') }}">
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
