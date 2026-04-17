<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .order-info {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .order-info-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #64748b;
            font-size: 14px;
        }
        .value {
            font-weight: 600;
            color: #1e293b;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 24px 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }
        .product-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f5f9;
        }
        .product-details {
            flex: 1;
        }
        .product-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .product-price {
            color: #64748b;
            font-size: 14px;
        }
        .product-quantity {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
        }
        .total-section {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 2px solid #e2e8f0;
            text-align: right;
        }
        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }
        .shipping-address {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
            font-size: 14px;
            color: #475569;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 24px;
            text-align: center;
        }
        .button:hover {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
        }
        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mini Shop</h1>
            <p>New Order Received</p>
        </div>
        
        <div class="content">
            <p style="margin-top: 0;">Hello Admin,</p>
            <p>A new order has been placed and requires your attention.</p>
            
            <div class="order-info">
                <div class="order-info-row">
                    <span class="label">Order ID</span>
                    <span class="value">#{{ $order->id }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Order Date</span>
                    <span class="value">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $customer->name }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Email</span>
                    <span class="value">{{ $customer->email }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-pending">{{ ucfirst($order->status) }}</span>
                    </span>
                </div>
            </div>
            
            <h3 class="section-title">📦 Order Items</h3>
            
            @foreach($orderItems as $item)
            <div class="product-item">
                <img src="{{ $item->product->image ?? 'https://via.placeholder.com/60' }}" 
                     alt="{{ $item->product->name }}" 
                     class="product-image">
                <div class="product-details">
                    <div class="product-name">{{ $item->product->name }}</div>
                    <div class="product-price">RM {{ number_format($item->price, 2) }}</div>
                </div>
                <div class="product-quantity">
                    × {{ $item->quantity }}
                </div>
            </div>
            @endforeach
            
            <div class="total-section">
                <div class="total-amount">Total: RM {{ number_format($order->total_amount, 2) }}</div>
            </div>
            
            <h3 class="section-title">📍 Shipping Address</h3>
            <div class="shipping-address">
                {{ $order->shipping_address }}
            </div>
            
            @if($order->notes)
            <h3 class="section-title">📝 Order Notes</h3>
            <div class="shipping-address">
                {{ $order->notes }}
            </div>
            @endif
            
            <div style="text-align: center;">
                <a href="{{ $adminUrl }}" class="button">View Order in Admin Panel</a>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from Mini Shop.</p>
            <p>&copy; {{ date('Y') }} Mini Shop. All rights reserved.</p>
        </div>
    </div>
</body>
</html>