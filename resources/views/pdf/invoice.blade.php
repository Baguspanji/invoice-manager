<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 25mm 15mm 25mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 12px;
            background-color: white;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            width: 100%;
        }

        .company-info {
            font-weight: bold;
            width: 60%;
            float: left;
        }

        .invoice-right {
            width: 40%;
            text-align: right;
            float: right;
        }

        .invoice-title {
            text-align: right;
            margin: 0 0 10px 0;
            font-size: 22px;
            font-weight: bold;
        }

        .invoice-details {
            clear: both;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            width: 100%;
        }

        .client-info {
            width: 48%;
            float: left;
        }

        .invoice-info {
            width: 48%;
            float: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            font-size: 12px;
        }

        tfoot th {
            text-align: right;
            font-weight: bold;
        }

        tfoot tr:last-child th,
        tfoot tr:last-child td {
            border-top: 2px solid #000;
        }

        .notes {
            margin-top: 30px;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }

        .status-draft {
            background-color: #6c757d;
        }

        .status-sent {
            background-color: #007bff;
        }

        .status-paid {
            background-color: #28a745;
        }

        .status-overdue {
            background-color: #dc3545;
        }

        footer {
            margin-top: 30px;
            text-align: center;
            color: #777;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="invoice-header">
        <div class="company-info">
            <div style="font-size:16px; margin-bottom:5px;">Your Company Name</div>
            <div>123 Business Street</div>
            <div>Jakarta, Indonesia 12345</div>
            <div>Phone: (021) 123-4567</div>
            <div>Email: contact@yourcompany.com</div>
        </div>

        <div class="invoice-right">
            <h1 class="invoice-title">INVOICE</h1>
            <div>
                <span class="status-badge status-{{ $invoice->status->value }}">
                    {{ $invoice->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <div class="invoice-details clearfix">
        <div class="client-info">
            <h3 style="margin-top:0;">Ditagihkan kepada:</h3>
            <div><strong>{{ $invoice->client->name }}</strong></div>
            <div>{{ $invoice->client->address }}</div>
            @if ($invoice->client->phone)
                <div>Phone: {{ $invoice->client->phone }}</div>
            @endif
            @if ($invoice->client->email)
                <div>Email: {{ $invoice->client->email }}</div>
            @endif
            @if ($invoice->client->npwp)
                <div>NPWP: {{ $invoice->client->npwp }}</div>
            @endif
        </div>

        <div class="invoice-info">
            <table>
                <tr>
                    <th>No. Invoice</th>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <th>Tanggal Terbit</th>
                    <td>{{ $invoice->issue_date?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Jatuh Tempo</th>
                    <td>{{ $invoice->due_date?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="clearfix"></div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Deskripsi</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <!-- Calculate rows for proper rowspan -->
            @php
                $summaryRows = 1; // Start with subtotal
                if($invoice->tax > 0) $summaryRows++;
                if($invoice->discount > 0) $summaryRows++;
            @endphp

            <tr>
                <td colspan="4" rowspan="{{ $summaryRows }}"></td>
                <th>Subtotal</th>
                <td class="text-right">Rp {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if ($invoice->tax > 0)
                <tr>
                    <th>PPN (11%)</th>
                    <td class="text-right">Rp {{ number_format($invoice->tax, 2) }}</td>
                </tr>
            @endif
            @if ($invoice->discount > 0)
                <tr>
                    <th>Diskon</th>
                    <td class="text-right">Rp {{ number_format($invoice->discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4"></td>
                <th>Total</th>
                <td class="text-right total-row">Rp {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($invoice->notes)
        <div class="notes">
            <strong>Catatan:</strong>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div style="height: 40px;"></div>

    <footer>
        <p>Terima kasih atas kerjasamanya!</p>
        <p>Invoice ini dibuat secara elektronik dan sah tanpa tanda tangan.</p>
        <p>Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}</p>
    </footer>
</body>

</html>
