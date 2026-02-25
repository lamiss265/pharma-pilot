<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.receipt') }} - {{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
            line-height: 1.4;
        }
        
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 12px;
            color: #666;
        }
        
        .receipt-info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items {
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            padding: 10px 0;
            margin: 15px 0;
        }
        
        .item {
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            color: #666;
        }
        
        .totals {
            margin-top: 15px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .total-line.final {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .payment-info {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #333;
            font-size: 12px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #333;
            font-size: 10px;
            color: #666;
        }
        
        .barcode {
            text-align: center;
            margin: 15px 0;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 24px;
            letter-spacing: 2px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                max-width: none;
                border: none;
                box-shadow: none;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name', 'PharmaPilot') }}</div>
            <div class="company-info">
                {{ $receipt->receipt_data['company']['address'] ?? 'Your Pharmacy Address' }}<br>
                {{ __('messages.phone') }}: {{ $receipt->receipt_data['company']['phone'] ?? 'Your Phone' }}<br>
                {{ __('messages.email') }}: {{ $receipt->receipt_data['company']['email'] ?? 'your@email.com' }}
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div>
                <span>{{ __('messages.receipt_number') }}:</span>
                <span>{{ $receipt->receipt_number }}</span>
            </div>
            <div>
                <span>{{ __('messages.date') }}:</span>
                <span>{{ $receipt->sale->sale_date->format('Y-m-d H:i') }}</span>
            </div>
            <div>
                <span>{{ __('messages.cashier') }}:</span>
                <span>{{ optional($receipt->sale->user)->name ?? __('messages.unknown_cashier') }}</span>
            </div>
            @if($receipt->sale->customer)
            <div>
                <span>{{ __('messages.customer') }}:</span>
                <span>{{ optional($receipt->sale->customer)->name ?? __('messages.walk_in_customer') }}</span>
            </div>
            @endif
        </div>

        <!-- Items -->
        <div class="items">
            @foreach($receipt->sale->saleItems as $item)
            <div class="item">
                <div class="item-name">{{ optional($item->product)->name ?? __('messages.deleted_product') }}</div>
                <div class="item-details">
                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                    <span>{{ number_format($item->subtotal, 2) }}</span>
                </div>
                @if($item->discount_amount > 0)
                <div class="item-details">
                    <span style="color: #28a745;">{{ __('messages.discount') }}</span>
                    <span style="color: #28a745;">-{{ number_format($item->discount_amount, 2) }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-line">
                <span>{{ __('messages.subtotal') }}:</span>
                <span>{{ number_format($receipt->sale->total_amount, 2) }}</span>
            </div>
            
            @if($receipt->sale->discount_amount > 0)
            <div class="total-line">
                <span>{{ __('messages.discount') }}:</span>
                <span style="color: #28a745;">-{{ number_format($receipt->sale->discount_amount, 2) }}</span>
            </div>
            @endif
            
            @if($receipt->sale->loyalty_points_used > 0)
            <div class="total-line">
                <span>{{ __('messages.loyalty_discount') }}:</span>
                <span style="color: #28a745;">-{{ number_format($receipt->sale->loyalty_points_used * 0.01, 2) }}</span>
            </div>
            @endif
            
            @if($receipt->sale->tax_amount > 0)
            <div class="total-line">
                <span>{{ __('messages.tax') }}:</span>
                <span>{{ number_format($receipt->sale->tax_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="total-line final">
                <span>{{ __('messages.total') }}:</span>
                <span>{{ number_format($receipt->sale->final_amount, 2) }} {{ config('app.currency', 'DH') }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <div style="display: flex; justify-content: space-between;">
                <span>{{ __('messages.payment_method') }}:</span>
                <span>{{ ucfirst($receipt->sale->payment_method) }}</span>
            </div>
            
            @if($receipt->sale->customer && $receipt->sale->loyalty_points_earned > 0)
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span>{{ __('messages.points_earned') }}:</span>
                <span>{{ number_format($receipt->sale->loyalty_points_earned) }}</span>
            </div>
            @endif
            
            @if($receipt->sale->customer)
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span>{{ __('messages.current_points') }}:</span>
                <span>{{ number_format($receipt->sale->customer->loyalty_points) }}</span>
            </div>
            @endif
        </div>

        <!-- Barcode -->
        <div class="barcode">
            *{{ $receipt->receipt_number }}*
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>{{ __('messages.thank_you_visit') }}</div>
            <div>{{ __('messages.receipt_generated_at') }}: {{ now()->format('Y-m-d H:i:s') }}</div>
            @if($receipt->sale->customer)
            <div style="margin-top: 10px;">
                <strong>{{ __('messages.return_policy') }}</strong><br>
                {{ __('messages.return_policy_text') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Print Controls -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; margin-right: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            {{ __('messages.print_receipt') }}
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
            {{ __('messages.close') }}
        </button>
        <a href="{{ route('pos.download-receipt', $receipt->id) }}" style="display: inline-block; padding: 10px 20px; margin-left: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">
            {{ __('messages.download_pdf') }}
        </a>
    </div>

    <script>
        // Auto-print if requested
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
