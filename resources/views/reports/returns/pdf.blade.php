<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المرتجعات</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            direction: rtl;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #2c3e50;
        }
        .totals {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 2px solid #333;
        }
        .totals table {
            border: none;
        }
        .totals td {
            border: none;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $storeName }}</h1>
        <p>تقرير المرتجعات</p>
        <p>التاريخ: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>رقم المرتج</th>
                <th>التاريخ</th>
                <th>رقم الإيصال</th>
                <th>العميل</th>
                <th>الإجمالي</th>
                <th>طريقة الاسترداد</th>
                <th>السبب</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $return)
            <tr>
                <td>{{ $return->return_number }}</td>
                <td>{{ $return->return_date->format('Y-m-d H:i') }}</td>
                <td>{{ $return->sale?->receipt_number ?? '-' }}</td>
                <td>{{ $return->sale?->customer?->name ?? '-' }}</td>
                <td>${{ number_format($return->total_return_amount, 2) }}</td>
                <td>{{ $return->getPaymentMethodText() }}</td>
                <td>{{ $return->reason }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>إجمالي المرتجعات</td>
                <td>${{ number_format($totalReturnAmount, 2) }}</td>
            </tr>
            <tr>
                <td>استرداد نقدي</td>
                <td>${{ number_format($totalCashRefund, 2) }}</td>
            </tr>
            <tr>
                <td>تخفيض ديون</td>
                <td>${{ number_format($totalDebtReduction, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة نظام نقاط البيع</p>
        <p>{{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
