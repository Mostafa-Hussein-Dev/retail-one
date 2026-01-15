<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المبيعات</title>
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
            text-align: center;
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
        <p>تقرير المبيعات</p>
        <p>التاريخ: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>رقم الإيصال</th>
                <th>التاريخ</th>
                <th>العميل</th>
                <th>الإجمالي</th>
                <th>طريقة الدفع</th>
                <th>الموظف</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->receipt_number }}</td>
                <td>{{ $sale->sale_date?->format('Y-m-d H:i') ?? '-' }}</td>
                <td>{{ $sale->customer?->name ?? '-' }}</td>
                <td>${{ number_format($sale->total_amount, 2) }}</td>
                <td>{{ $sale->getPaymentMethodText() }}</td>
                <td>{{ $sale->user?->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>إجمالي المبيعات</td>
            <td>${{ number_format($totalRevenue, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة نظام نقاط البيع</p>
        <p>{{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
