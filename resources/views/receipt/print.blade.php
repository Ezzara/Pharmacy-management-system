<!doctype html>
<html>
<head>
    <title>Receipt</title>
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <div id="receipt-header">
        <h1>{{ $store_name }}</h1>
        <p>{{ $store_address }}</p>
        <p>{{ $store_phone }}</p>
        <p>{{ $store_email }}</p>
        <p>{{ $store_website }}</p>
    </div>

    <div id="receipt-body">
        <h2>Receipt</h2>
        <p>Transaction ID: {{ $transaction_id }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['qty'] }}</td>
                        <td>{{ $item['price'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>Subtotal: {{ $subtotal }}</p>
        <p>Tax ({{ $tax_percentage }}%): {{ $tax }}</p>
        <p>Total: {{ $total }}</p>
    </div>

    <div id="receipt-footer">
        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>