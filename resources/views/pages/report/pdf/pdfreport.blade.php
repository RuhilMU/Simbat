<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Obat</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .date-range { text-align: center; margin: 10px 0; font-weight: bold; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Stok Obat</h2>
    
    @if(isset($startDate) && isset($endDate))
        <div class="date-range">
            Periode Expired: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('j F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('j F Y') }}
        </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Stok</th>
                <th>Expired Terdekat</th>
                <th>Expired Terbaru</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->drug->code }}</td>
                <td>{{ $item->drug->name }}</td>
                <td>{{ floor($item->quantity / $item->drug->piece_netto) }} pcs</td>
                <td>{{ $item->oldest ? \Carbon\Carbon::parse($item->oldest)->translatedFormat('j F Y') : '-' }}</td>
                <td>{{ $item->latest ? \Carbon\Carbon::parse($item->latest)->translatedFormat('j F Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
