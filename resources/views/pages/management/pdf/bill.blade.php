<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penerimaan Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 40%;
        }
        .document-info {
            float: right;
            width: 40%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .status-unpaid {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h3>{{ App\Models\Profile::first()->name }}</h3>
            <p>{{ App\Models\Profile::first()->address }}</p>
            <p>{{ App\Models\Profile::first()->phone }}</p>
        </div>
        <div class="document-info">
            <p><strong>No. LPB:</strong> {{ $transaction->code }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($bill->created_at)->translatedFormat('j F Y') }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="title">LAPORAN PENERIMAAN BARANG</div>

    <div style="margin-bottom: 20px;">
        <div style="float: left; width: 50%;">
            <p><strong>Vendor:</strong></p>
            <p>{{ $vendor->name }}</p>
            <p>{{ $vendor->address }}</p>
            <p>{{ $vendor->phone }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Obat</th>
                <th width="35%">Nama Obat</th>
                <th width="15%">Jumlah</th>
                <th width="15%">Harga Satuan</th>
                <th width="15%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->drug()->code }}</td>
                <td>{{ $item->drug()->name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ 'Rp ' . number_format($item->piece_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ 'Rp ' . number_format($item->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: right; margin-top: 20px;">
        <p><strong>Grand Total: {{ 'Rp ' . number_format($transaction->outcome, 0, ',', '.') }}</strong></p>
        @if($bill->status == 'Belum Bayar')
            <p class="status-unpaid"><strong>Status: Belum Bayar</strong></p>
        @endif
    </div>
</body>
</html> 