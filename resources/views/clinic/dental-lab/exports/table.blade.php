{{--
    Generic tabular export rendered by mpdf. Standalone HTML (no app layout).
    Expects: $clinic, $title, $periodLabel, $columns[], $rows[][], $totals[].
--}}
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.5px solid #cbd5e1; padding: 4px 6px; text-align: start; }
        thead th { background: #f1f5f9; font-weight: 700; }
        tfoot td { background: #f8fafc; font-weight: 700; }
        tbody tr:nth-child(even) td { background: #fafafa; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        {{ $clinic->name }} · {{ $periodLabel }} · {{ count($rows) }}
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}" style="text-align:center;color:#94a3b8;padding:18px;">—</td></tr>
            @endforelse
        </tbody>
        @if (! empty($totals) && count($rows))
            <tfoot>
                <tr>
                    @foreach ($totals as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
