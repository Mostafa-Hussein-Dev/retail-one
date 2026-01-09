<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->receipt_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            direction: ltr;
            background: white;
        }

        .receipt {
            width: 58mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 3mm;
            background: white;
            border: 1px solid #000;
        }

        .store-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .store-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .store-address {
            font-size: 9px;
            margin-bottom: 1px;
        }

        .divider {
            text-align: center;
            margin: 5px 0;
            font-size: 10px;
        }

        .receipt-info {
            margin-bottom: 8px;
            font-size: 9px;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .items-section {
            margin-bottom: 8px;
        }

        .items-header {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 2px 0;
            font-size: 9px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .item {
            font-size: 9px;
            margin-bottom: 3px;
            padding-bottom: 2px;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .totals-section {
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-bottom: 8px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
            font-size: 9px;
        }

        .final-total {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-weight: bold;
            font-size: 10px;
            margin: 3px 0;
        }

        .customer-info {
            margin-bottom: 5px;
            font-size: 8px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px dotted #999;
        }

        .thank-you {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin: 8px 0;
        }

        .barcode-section {
            text-align: center;
            margin: 10px 0;
            padding: 3px;
            border: 1px solid #000;
            width: 100%;
        }

        .barcode-image {
            text-align: center;
            margin-bottom: 5px;
        }

        .barcode-image svg {
            width: auto !important; /* Natural width */
            height: 40px !important;
            display: inline-block; /* Center it */
        }

        .barcode-number {
            font-family: "Courier New", monospace;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt {
                width: 100%;
                margin: 0;
                padding: 0;
                border: none;
            }

            .no-print {
                display: none !important;
            }

            @page {
                margin: 0;
                size: 58mm auto;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: transparent;
            color: #1abc9c;
            padding: 12px 24px;
            border: 2px solid #1abc9c;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 1rem;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background-color: rgba(26, 188, 156, 0.1);
        }

        .void-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            border: 2px solid #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            color: #e74c3c;
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>
<body>

<button class="print-button no-print" onclick="window.print()">طباعة</button>

<div class="receipt">
    <!-- Store Header -->
    <div class="store-header">
        <div class="store-name">AL-HEJAZI STORE</div>
        <div class="store-address">Baabda, Lebanon</div>
        <div class="store-address">Tel: 01-123456</div>
    </div>

    <!-- Receipt Info -->
    <div class="receipt-info">
        <div class="receipt-line">
            <span>Receipt No:</span>
            <span>{{ $sale->receipt_number }}</span>
        </div>
        <div class="receipt-line">
            <span>Date:</span>
            <span>{{ $sale->sale_date->format('d/m/Y') }}</span>
        </div>
        <div class="receipt-line">
            <span>Time:</span>
            <span>{{ $sale->sale_date->format('H:i:s') }}</span>
        </div>
        <div class="receipt-line">
            <span>Cashier:</span>
            <span>{{ $sale->user->name }}</span>
        </div>
    </div>

    <!-- Customer Info -->
    @if($sale->customer)
        <div class="customer-info">
            <div><strong>Customer:</strong> {{ $sale->customer->name }}</div>
            @if($sale->customer->phone)
                <div><strong>Phone:</strong> {{ $sale->customer->phone }}</div>
            @endif
        </div>
    @endif

    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Items -->
    <div class="items-section">
        <div class="items-header">
            <span>QTY</span>
            <span>DESCRIPTION</span>
            <span>PRICE</span>
            <span>TOTAL</span>
        </div>

        @foreach($sale->saleItems as $item)
            <div class="item">
                <div class="item-line">
                    <span>{{ number_format($item->quantity, 1) }}</span>
                    <span>{{ $item->product->name ?: $item->product->name_ar }}</span>
                    <span>${{ number_format($item->unit_price, 2) }}</span>
                    <span>${{ number_format($item->total_price, 2) }}</span>
                </div>
                @if($item->discount_amount > 0)
                    <div class="item-line" style="font-size: 8px; color: #666;">
                        <span></span>
                        <span>Discount</span>
                        <span></span>
                        <span>-${{ number_format($item->discount_amount, 2) }}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Totals -->
    <div class="totals-section">
        <div class="total-line">
            <span>Items Count:</span>
            <span>{{ $sale->saleItems->count() }}</span>
        </div>
        <div class="total-line">
            <span>Total Quantity:</span>
            <span>{{ number_format($sale->saleItems->sum('quantity'), 1) }}</span>
        </div>
        <div class="total-line">
            <span>Subtotal:</span>
            <span>${{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if($sale->discount_amount > 0)
            <div class="total-line">
                <span>Total Discount:</span>
                <span>-${{ number_format($sale->discount_amount, 2) }}</span>
            </div>
        @endif

        <div class="final-total">
            <div style="display: flex; justify-content: space-between;">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="total-line">
            <span>In LBP:</span>
            <span>LL {{ number_format($sale->total_amount * 89500, 0) }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($sale->notes && !str_contains($sale->notes, 'VOIDED'))
        <div style="margin: 5px 0; font-size: 8px; border: 1px solid #ccc; padding: 2px;">
            <strong>Notes:</strong><br>
            {{ $sale->notes }}
        </div>
    @endif

    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Thank You -->
    <div class="thank-you">
        THANK YOU FOR SHOPPING
    </div>
    <div style="text-align: center; font-size: 8px; margin-bottom: 8px;">
        Welcome back anytime
    </div>

    <!-- Exchange Rate -->
    <div style="text-align: center; font-size: 7px; margin-bottom: 8px;">
        Exchange Rate: 1 USD = 89,500 LBP
    </div>

    <!-- Barcode Section -->
    <div class="barcode-section">
        <div class="barcode-image" style="text-align: center;">
            <?php
            // Generate with thicker bars for better visibility
            $barcodeSvg = DNS1D::getBarcodeSVG($sale->receipt_number, 'C128', 1, 40, '#000', false);
            echo $barcodeSvg;
            ?>
        </div>
        <div class="barcode-number">{{ $sale->receipt_number }}</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Printed: {{ now()->format('d/m/Y H:i:s') }}</div>
        <div style="margin-top: 2px; font-size: 7px;">
            POS System
        </div>
    </div>

    <!-- Void Stamp -->
    @if(str_contains($sale->notes ?? '', 'VOIDED'))
        <div style="position: relative; height: 40px;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); border: 2px solid #e74c3c; background: rgba(231, 76, 60, 0.1); padding: 5px 10px; font-size: 10px; font-weight: bold; color: #e74c3c; text-align: center;">
                VOID
            </div>
        </div>
    @endif
</div>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            window.focus();
        }, 100);
    });

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
</script>
</body>
</html>
