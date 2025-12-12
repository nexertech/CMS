<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Part Slip - {{ $spare->item_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .spare-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
            color: #333;
        }
        .info-value {
            flex: 1;
            color: #666;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-in_stock { background: #e8f5e8; color: #ffffff !important; }
        .status-low_stock { background: #fff3e0; color: #ffffff !important; }
        .status-out_of_stock { background: #ffebee; color: #ffffff !important; }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Spare Parts Management System</h1>
        <p>Spare Part Details Slip</p>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="spare-info">
        <div class="info-row">
            <div class="info-label">Product Code:</div>
            <div class="info-value">{{ $spare->product_code ?? 'N/A' }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Brand Name:</div>
            <div class="info-value">{{ $spare->brand_name ?? 'N/A' }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Product Name:</div>
            <div class="info-value">{{ $spare->item_name }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Category:</div>
            <div class="info-value">{{ ucfirst($spare->category ?? 'N/A') }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Unit Price:</div>
            <div class="info-value">PKR {{ number_format($spare->unit_price ?? 0, 2) }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Total Received:</div>
            <div class="info-value">{{ number_format($spare->total_received_quantity ?? 0, 0) }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Issued Quantity:</div>
            <div class="info-value">{{ number_format($spare->issued_quantity ?? 0, 0) }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Balance Quantity:</div>
            <div class="info-value">{{ number_format($spare->stock_quantity ?? 0, 0) }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Threshold Level:</div>
            <div class="info-value">{{ number_format($spare->threshold_level ?? 0, 0) }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Stock Status:</div>
            <div class="info-value">
                <span class="status-badge status-{{ $spare->getStockStatusAttribute() }}">
                    {{ $spare->getStockStatusDisplayAttribute() }}
                </span>
            </div>
        </div>
        
        @if($spare->supplier)
        <div class="info-row">
            <div class="info-label">Supplier:</div>
            <div class="info-value">{{ $spare->supplier }}</div>
        </div>
        @endif
        
        @if($spare->last_stock_in_at)
        <div class="info-row">
            <div class="info-label">Last Stock In:</div>
            <div class="info-value">{{ $spare->last_stock_in_at->format('M d, Y H:i') }}</div>
        </div>
        @endif
        
        @if($spare->last_stock_out)
        <div class="info-row">
            <div class="info-label">Last Stock Out:</div>
            <div class="info-value">{{ $spare->last_stock_out->format('M d, Y H:i') }}</div>
        </div>
        @endif
        
        <div class="info-row">
            <div class="info-label">Last Updated:</div>
            <div class="info-value">{{ $spare->updated_at->format('M d, Y H:i') }}</div>
        </div>
        
        @if($spare->description)
        <div class="info-row">
            <div class="info-label">Description:</div>
            <div class="info-value">{{ $spare->description }}</div>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
        <p>Spare Parts Management System - {{ config('app.name') }}</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

