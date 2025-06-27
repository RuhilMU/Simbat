<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header, .table { width: 100%; margin-bottom: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; text-align: center; }
        .title { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 10px; }
        .right { text-align: right; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td><b>{{ $profile->name }}</b><br>{{ $profile->address }}<br>{{ $profile->phone }}</td>
            <td class="right"><b>No. Invoice:</b> {{ $no_invoice }}<br><b>Tanggal:</b> {{ $tanggal_transaksi }}</td>
        </tr>
    </table>

    <div class="title">INVOICE</div>

    <table class="table">
        <tr>
            <th>No</th>
            <th>Kode Obat</th>
            <th>Nama Obat</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Total</th>
        </tr>
        @foreach ($details as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->drug_code ?? '-' }}</td>
            <td>{{ $item->drug_name ?? '-' }}</td>
            <td>{{ $item->quantity }} </td>
            <td>Rp {{ number_format($item->piece_price, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="right"><b>Subtotal :</b></td>
            <td><b>Rp {{ number_format($subtotal, 0, ',', '.') }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="right"><b>Diskon :</b></td>
            <td><b>Rp {{ number_format($discount, 0, ',', '.') }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="right"><b>Total :</b></td>
            <td><b>Rp {{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </table>

</body>
</html> 