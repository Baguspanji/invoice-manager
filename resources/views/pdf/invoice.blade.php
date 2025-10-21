<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 18mm 15mm 18mm;
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

        .payment-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            color: white;
        }

        .paid {
            background-color: #28a745;
        }

        .unpaid {
            background-color: #dc3545;
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
            margin: 0;
            font-size: 52px;
            font-weight: bold;
            color: #1E9DA6;
        }

        .invoice-date {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #666;
        }

        .invoice-details {
            clear: both;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            width: 100%;
        }

        .address-section {
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: normal;
        }

        .address-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .client-info {
            width: 48%;
            float: left;
        }

        .invoice-info {
            width: 48%;
            float: right;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .invoice-table th:first-child {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        .invoice-table th:last-child {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
        }

        .invoice-table th {
            background-color: #1E9DA6;
            color: white;
            padding: 10px 20px;
            text-align: left;
            font-size: 22px;
            font-weight: normal;
            border: none;
        }

        .invoice-table td {
            padding: 10px;
            border-bottom: 4px solid #ababab;
            font-size: 16px;
        }

        .invoice-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .notes-section {
            margin-top: 20px;
            font-size: 16px;
            font-weight: normal;
            color: red;
            width: 60%;
            float: left;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
            width: 40%;
            float: right;
        }

        .subtotal-line {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .grand-total-line {
            font-size: 12px;
            font-weight: bold;
            text-align: left;
            margin: 0;
        }

        .grand-total {
            display: inline-block;
            background-color: #1E9DA6;
            color: white;
            padding: 8px 18px;
            font-size: 24px;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 4px;
        }

        .information-data {
            clear: both;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .bank-details {
            margin-top: 30px;
            font-size: 22px;
            font-weight: bold;
            width: 60%;
            float: left;
            margin-bottom: 70px;
        }

        .bank-account {
            font-weight: normal;
            font-size: 14px;
        }

        .bank-logo {
            height: 30px;
            vertical-align: bottom;
        }

        .bank-account-number {
            margin-top: 5px;
            font-size: 18px;
            font-weight: bold;
        }

        .qr-code {
            width: 40%;
            float: right;
            width: 200px;
            text-align: center;
            margin-top: 10px;
        }

        .qr-code img {
            width: 180px;
            height: 180px;
        }

        .thank-you {
            margin-top: 30px;
            text-align: start;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #1E9DA6;
            font-size: 18px;
            font-weight: bold;
        }

        .contact-details {
            text-align: center;
            margin-top: 16px;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
        }

        .contact-item {
            margin-right: 30px;
            display: inline-block;
            vertical-align: middle;
        }

        .contact-icon {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <!-- First section with logo and invoice title -->
    <div class="invoice-header">
        <div class="company-info">
            <div class="logo">
                <img src="{{ $logo_base64 }}" alt="Company Logo" style="max-height: 80px;">
            </div>
        </div>

        <div class="invoice-right">
            <h1 class="invoice-title">INVOICE</h1>
            <div class="invoice-date">
                {{ $invoice->issue_date?->translatedFormat('d - F - Y') ?? now()->translatedFormat('d - F - Y') }}
            </div>
            @if ($useBadge)
                <div class="payment-badge {{ $invoice->status == 'paid' ? 'paid' : 'unpaid' }}">
                    {{ $invoice->status == 'paid' ? 'TERBAYAR' : 'BELUM TERBAYAR' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Second section with addresses -->
    <div class="invoice-details" style="margin-top: 7rem; padding: 0 20px;">
        <div class="company-info">
            <div class="address-section">
                <div class="address-title">Address Office</div>
                <div>Jl.Malang-Surabaya</div>
                <div>Purwosari, Kab.Pasuruan</div>
                <div>67162</div>
            </div>
        </div>

        <div class="invoice-right">
            <div class="address-section" style="text-align: left;">
                <div class="address-title">To</div>
                <div><strong>{{ $invoice->client->name }}</strong></div>
                <div>{{ $invoice->client->address }}</div>
                @if ($invoice->client->city || $invoice->client->province)
                    <div>
                        {{ $invoice->client->city }}{{ $invoice->client->province ? ', ' . $invoice->client->province : '' }}
                    </div>
                @endif
                @if ($invoice->client->postal_code)
                    <div>{{ $invoice->client->postal_code }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Clear floats -->
    <div style="clear: both; margin-bottom: 20px;"></div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>DeskripsiPaket</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if ($item->description)
                            <div style="color: #666; font-size: 11px; margin-top: 3px;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td>{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="information-data">
        <div class="notes-section">
            <span>Note : Test catatan</span>
        </div>

        <div class="total-section">
            <div class="subtotal-line">
                <strong>Subtotal :</strong> {{ number_format($invoice->subtotal, 0, ',', '.') }}
            </div>

            @if ($invoice->tax > 0)
                <div class="subtotal-line">
                    <strong>PPN 11% :</strong> {{ number_format($invoice->tax, 0, ',', '.') }}
                </div>
            @endif

            @if ($invoice->down_payment > 0)
                <div class="subtotal-line">
                    <strong>Dp :</strong> {{ number_format($invoice->discount, 0, ',', '.') }}
                </div>
            @endif

            <div style="display: inline-block;">
                <div class="grand-total-line" style="font-size: 18px;">Grand Total</div>
                <div class="grand-total">Rp.{{ number_format($invoice->total, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>


    <div class="information-data">
        <div class="bank-details">
            <div>No.Rekening</div>
            <div class="bank-account">
                <div>A/n Riziq Sofyan</div>
            </div>
            <div class="bank-account-number">
                <img src="{{ $logo_bca_base64 }}" alt="BCA" class="bank-logo">
                <strong>8945191399</strong>
            </div>
        </div>

        <div class="qr-code">
            @if ($useQr)
                <img src="{{ $qrcode }}" alt="QR Code">
            @endif
        </div>
    </div>

    <div class="thank-you">
        ThankYouForYourBusiness
    </div>

    <div class="contact-details">
        <div class="contact-item">
            <img src="{{ $logo_instagram_base64 }}" alt="Instagram"
                style="margin-bottom: -9px; max-height: 30px; color: #1E9DA6;" />
            @pas.desain
        </div>
        <div class="contact-item">
            <img src="{{ $logo_whatsapp_base64 }}" alt="Instagram"
                style="margin-bottom: -9px; max-height: 30px; color: #1E9DA6;" />
            085745526763
        </div>
        <div class="contact-item">
            <img src="{{ $logo_website_base64 }}" alt="Instagram"
                style="margin-bottom: -9px; max-height: 30px; color: #1E9DA6;" />
            osidesain.com
        </div>
    </div>
</body>

</html>
