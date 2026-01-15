<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt - {{ $purchase->purchase_number }}</title>
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
            border-bottom: 2px solid #000;
        }

        .store-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .store-address {
            font-size: 8px;
            margin-bottom: 1px;
        }

        .divider {
            text-align: center;
            margin: 5px 0;
            font-size: 10px;
        }

        .receipt-info {
            margin-bottom: 8px;
            font-size: 8px;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items-section {
            margin-bottom: 8px;
        }

        .items-header {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 8px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .item {
            font-size: 8px;
            margin-bottom: 4px;
            padding-bottom: 2px;
            border-bottom: 1px dashed #ccc;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .item-details {
            font-size: 7px;
            color: #666;
            margin-bottom: 2px;
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
            margin-bottom: 2px;
            font-size: 9px;
        }

        .final-total {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            font-weight: bold;
            font-size: 11px;
            margin: 4px 0;
        }

        .payment-section {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            margin: 8px 0;
        }

        .payment-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .footer {
            text-align: center;
            font-size: 7px;
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
            width: auto !important;
            height: 40px !important;
            display: inline-block;
        }

        .barcode-number {
            font-family: "Courier New", monospace;
            font-size: 10px;
            letter-spacing: 0.5px;
            font-weight: bold;
        }

        .payment-method {
            text-align: center;
            padding: 4px;
            margin: 5px 0;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-cash {
            background: white;
            border: 1px solid black;
            color: black;
        }

        .payment-debt {
            background: white;
            border: 1px solid black;
            color: black;
        }

        .void-notice {
            text-align: center;
            padding: 8px;
            margin: 8px 0;
            border: 2px dashed #e74c3c;
            background: #fee;
            font-size: 10px;
            font-weight: bold;
            color: #e74c3c;
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

        .bold-text {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .margin-top {
            margin-top: 5px;
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
            <span>Purchase No:</span>
            <span>{{ $purchase->purchase_number }}</span>
        </div>
        <div class="receipt-line">
            <span>Date:</span>
            <span>{{ $purchase->purchase_date->format('d/m/Y') }}</span>
        </div>
        <div class="receipt-line">
            <span>Time:</span>
            <span>{{ $purchase->purchase_date->format('H:i:s') }}</span>
        </div>
        <div class="receipt-line">
            <span>Received By:</span>
            <span>{{ $purchase->user->name }}</span>
        </div>
        @if($purchase->is_voided)
            <div class="receipt-line">
                <span>Voided By:</span>
                <span>{{ $purchase->voidedBy->name }}</span>
            </div>
            <div class="receipt-line">
                <span>Voided At:</span>
                <span>{{ $purchase->voided_at->format('d/m/Y H:i') }}</span>
            </div>
        @endif
        <div class="receipt-line">
            <span>Supplier:</span>
            <span>{{ $purchase->supplier->name }}</span>
        </div>
        @if($purchase->supplier->contact_person)
            <div class="receipt-line">
                <span>Contact:</span>
                <span>{{ $purchase->supplier->contact_person }}</span>
            </div>
        @endif
        @if($purchase->supplier->phone)
            <div class="receipt-line">
                <span>Phone:</span>
                <span>{{ $purchase->supplier->phone }}</span>
            </div>
        @endif
    </div>


    <!-- Payment Method -->
    @if($purchase->is_voided)
        <div class="void-notice">
            *** PURCHASE VOIDED ***
            @if($purchase->void_reason)
                <div style="margin-top: 2px;">Reason: {{ $purchase->void_reason }}</div>
            @endif
        </div>
    @else
        <div class="payment-method @if($purchase->debt_amount == 0) payment-cash @else payment-debt @endif">
            @if($purchase->debt_amount == 0)
                CASH PURCHASE
            @else
                DEBT PURCHASE
            @endif
        </div>
    @endif



    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Items -->
    <div class="items-section">
        <div class="items-header">
            <span style="width: 12%; text-align: left;">QTY</span>
            <span style="width: 42%; text-align: left;">ITEM</span>
            <span style="width: 23%; text-align: right;">COST</span>
            <span style="width: 23%; text-align: right;">TOTAL</span>
        </div>

        @foreach($purchase->purchaseItems as $item)
            <div class="item">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-line">
                    <span style="width: 12%;">{{ number_format($item->quantity, 2) }}</span>
                    <span style="width: 42%;"></span>
                    <span style="width: 23%; text-align: right;">${{ number_format($item->unit_cost, 2) }}</span>
                    <span style="width: 23%; text-align: right;">${{ number_format($item->total_cost, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Totals -->
    <div class="totals-section">
        <div class="total-line">
            <span>Items Count:</span>
            <span>{{ $purchase->purchaseItems->count() }}</span>
        </div>
        <div class="total-line">
            <span>Total Quantity:</span>
            <span>{{ number_format($purchase->purchaseItems->sum('quantity'), 2) }}</span>
        </div>

        <div class="final-total">
            <div style="display: flex; justify-content: space-between;">
                <span>TOTAL:</span>
                <span>${{ number_format($purchase->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="total-line">
            <span>In LBP:</span>
            <span>{{ number_format($purchase->total_amount * 89500, 0) }} ل.ل.</span>
        </div>
    </div>

    <!-- Payment Details -->
    @if(!$purchase->is_voided)
        <div class="payment-section">
            @if($purchase->debt_amount == 0)
                <div class="payment-line">
                    <span>Payment Method:</span>
                    <span>CASH</span>
                </div>
                <div class="payment-line">
                    <span>Total Paid:</span>
                    <span style="color: black; font-weight: bold;">${{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
            @else
                <div class="payment-line">
                    <span>Payment Method:</span>
                    <span>DEBT</span>
                </div>
                <div class="payment-line">
                    <span>Total Paid:</span>
                    <span>${{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="payment-line" style="font-weight: bold;">
                    <span>Balance Due:</span>
                    <span>${{ number_format($purchase->debt_amount, 2) }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Notes -->
    @if($purchase->notes && !$purchase->is_voided)
        <div style="margin: 5px 0; font-size: 8px; border: 1px solid #ccc; padding: 4px;">
            <strong>Notes:</strong><br>
            {{ $purchase->notes }}
        </div>
    @endif

    <div class="divider">- - - - - - - - - - - -</div>

    <!-- Thank You -->
    <div class="thank-you">
        PURCHASE RECEIPT
    </div>
    <div class="text-center" style="font-size: 8px; margin-bottom: 8px;">
        Supplier: {{ $purchase->supplier->name }}
    </div>

    <!-- Exchange Rate -->
    <div class="text-center" style="font-size: 7px; margin-bottom: 8px;">
        Exchange Rate: 1 USD = 89,500 LBP
    </div>

    <!-- Barcode Section -->
    <div class="barcode-section">
        <div class="barcode-image" style="text-align: center;">
            <?php
            $barcodeSvg = DNS1D::getBarcodeSVG($purchase->purchase_number, 'C128', 1, 40, '#000', false);
            echo $barcodeSvg;
            ?>
        </div>
        <div class="barcode-number">{{ $purchase->purchase_number }}</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Printed: {{ now()->format('d/m/Y H:i:s') }}</div>
        <div style="margin-top: 2px; font-size: 6px;">
            RetailOne System v1.0
        </div>
    </div>
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
