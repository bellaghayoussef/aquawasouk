<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .vendor-info h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .invoice-details {
            text-align: right;
            float: right;
        }
        .invoice-details h2 {
            margin: 0;
            color: #7f8c8d;
        }
        .clear {
            clear: both;
        }
        .billing-info {
            margin-bottom: 30px;
        }
        .billing-to {
            float: left;
            width: 50%;
        }
        .billing-from {
            float: right;
            width: 50%;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #2c3e50;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            float: right;
            width: 40%;
        }
        .total-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row.final {
            border-bottom: 2px solid #2c3e50;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p>#{{ $order->order_number }}</p>
                <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
                
            </div>
            <div class="vendor-info">
                <h1>{{ $vendor->shop_name }}</h1>
                <p>{{ $vendor->email }}</p>
                @if($vendor->phone) <p>{{ $vendor->phone }}</p> @endif
            </div>
            <div class="clear"></div>
        </div>

        <div class="billing-info">
            <div class="billing-to">
                <h3>Bill To:</h3>
                <p><strong>{{ $order->user->name }}</strong></p>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</p>
                <p>{{ $order->shipping_country }}</p>
                <p>{{ $order->phone }}</p>
                <p>{{ $order->user->email }}</p>
            </div>
            <div class="billing-from">
                <h3>Ship From:</h3>
                <p><strong>{{ $vendor->shop_name }}</strong></p>
                <p>{{ $vendor->address }}</p>
                <p>{{ $vendor->city }}, {{ $vendor->postal_code }}</p>
                <p>{{ $vendor->country }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                    </td>
                    <td class="text-right">{{ number_format($item->price, 2) }} TND</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }} TND</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span style="float:left">Subtotal:</span>
                <span style="float:right">{{ number_format($items->sum(fn($i) => $i->price * $i->quantity), 2) }} TND</span>
                <div class="clear"></div>
            </div>
            <div class="total-row">
                <span style="float:left">Shipping:</span>
                <span style="float:right">{{ number_format($order->shipping_cost, 2) }} TND</span>
                <div class="clear"></div>
            </div>
            <div class="total-row final">
                <span style="float:left">Total:</span>
                <span style="float:right">{{ number_format($items->sum(fn($i) => $i->price * $i->quantity) + $order->shipping_cost, 2) }} TND</span>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For any questions, please contact us at {{ $vendor->email }}</p>
        </div>
    </div>
</body>
</html>
