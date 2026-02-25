<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.receipt') }} - {{ $sale->receipt_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .logo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .pharmacy-info {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .receipt-info {
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .items-table th,
        .items-table td {
            text-align: left;
            padding: 3px 0;
            font-size: 10px;
        }
        
        .items-table th {
            border-bottom: 1px solid #333;
            font-weight: bold;
        }
        
        .item-name {
            width: 60%;
        }
        
        .item-qty {
            width: 15%;
            text-align: center;
        }
        
        .item-price {
            width: 25%;
            text-align: right;
        }
        
        .totals {
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .final-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 9px;
        }
        
        .customer-info {
            background: #f9f9f9;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .prescription-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .loyalty-points {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 5px;
            margin-bottom: 10px;
            border-radius: 3px;
            font-size: 10px;
            text-align: center;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .receipt {
                border: none;
                box-shadow: none;
                max-width: none;
            }
        }
        
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Libre Barcode 128', monospace;
            font-size: 24px;
            letter-spacing: 2px;
        }
        
        .qr-code {
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo">PharmaPilot</div>
            <div class="pharmacy-info">
                {{ __('messages.parapharmacy_management_system') }}<br>
                Tel: +1 234 567 8900<br>
                Email: info@pharmapilot.com
            </div>
        </div>

        <!-- Receipt Information -->
        <div class="receipt-info">
            <div>
                <span>{{ __('messages.receipt_number') }}:</span>
                <span>{{ $sale->receipt_number }}</span>
            </div>
            <div>
                <span>{{ __('messages.date') }}:</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span>{{ __('messages.cashier') }}:</span>
                <span>{{ optional($sale->user)->name ?? __('messages.unknown') }}</span>
            </div>
            @if($sale->customer)
            <div>
                <span>{{ __('messages.customer') }}:</span>
                <span>{{ $sale->customer->customer_number }}</span>
            </div>
            @endif
        </div>

        <!-- Customer Information -->
        @if($sale->customer)
        <div class="customer-info">
            <strong>{{ $sale->customer->name }}</strong><br>
            @if($sale->customer->phone)
                {{ __('messages.phone') }}: {{ $sale->customer->phone }}<br>
            @endif
            {{ __('messages.tier') }}: {{ ucfirst($sale->customer->tier) }}<br>
            {{ __('messages.points_before') }}: {{ number_format($sale->customer->loyalty_points, 0) }}
        </div>
        @endif

        <!-- Prescription Information -->
        @if($sale->is_prescription)
        <div class="prescription-info">
            <strong>{{ __('messages.prescription_sale') }}</strong><br>
            {{ __('messages.prescription_number') }}: {{ $sale->prescription_number }}
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-name">{{ __('messages.item') }}</th>
                    <th class="item-qty">{{ __('messages.qty') }}</th>
                    <th class="item-price">{{ __('messages.price') }}</th>
                </tr>
            </thead>
            <tbody>
                @if($sale->saleItems && $sale->saleItems->count() > 0)
                    @foreach($sale->saleItems as $item)
                    <tr>
                        <td class="item-name">
                            {{ optional($item->product)->name ?? __('messages.unknown_product') }}
                            @if($item->batch)
                                <br><small>{{ __('messages.batch') }}: {{ $item->batch->batch_number }}</small>
                            @endif
                        </td>
                        <td class="item-qty">{{ $item->quantity }}</td>
                        <td class="item-price">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    <!-- Single item sale (legacy) -->
                    <tr>
                        <td class="item-name">{{ optional(optional($sale->saleItems->first())->product)->name ?? __('messages.unknown_product') }}</td>
                        <td class="item-qty">{{ $sale->quantity }}</td>
                        <td class="item-price">${{ number_format($sale->price * $sale->quantity, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>{{ __('messages.subtotal') }}:</span>
                <span>${{ number_format($sale->subtotal ?: $sale->total_amount, 2) }}</span>
            </div>
            
            @if($sale->discount_amount > 0)
            <div class="total-row">
                <span>{{ __('messages.discount') }} ({{ ucfirst($sale->discount_type) }}):</span>
                <span>-${{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif
            
            @if($sale->tax_amount > 0)
            <div class="total-row">
                <span>{{ __('messages.tax') }}:</span>
                <span>${{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="total-row final-total">
                <span>{{ __('messages.total') }}:</span>
                <span>${{ number_format($sale->final_amount ?: $sale->total_amount, 2) }}</span>
            </div>
            
            <div class="total-row">
                <span>{{ __('messages.payment_method') }}:</span>
                <span>{{ ucfirst($sale->payment_method ?: 'cash') }}</span>
            </div>
        </div>

        <!-- Loyalty Points Earned -->
        @if($sale->customer)
        @php
            $pointsEarned = floor(($sale->final_amount ?: $sale->total_amount) / 10);
        @endphp
        @if($pointsEarned > 0)
        <div class="loyalty-points">
            {{ __('messages.points_earned') }}: {{ $pointsEarned }}
        </div>
        @endif
        @endif

        <!-- Notes -->
        @if($sale->notes)
        <div style="margin-bottom: 15px; font-size: 10px; background: #f8f9fa; padding: 8px; border-radius: 3px;">
            <strong>{{ __('messages.notes') }}:</strong><br>
            {{ $sale->notes }}
        </div>
        @endif

        <!-- Barcode -->
        <div class="barcode">
            *{{ $sale->receipt_number }}*
        </div>

        <!-- Footer -->
        <div class="footer">
            {{ __('messages.thank_you_for_shopping') }}<br>
            {{ __('messages.return_policy') }}: {{ __('messages.30_days_with_receipt') }}<br>
            {{ __('messages.customer_service') }}: +1 234 567 8900<br>
            <br>
            {{ __('messages.visit_us_online') }}: www.pharmapilot.com<br>
            {{ __('messages.follow_us') }}: @PharmaPilot<br>
            <br>
            <small>{{ __('messages.receipt_generated_at') }}: {{ now()->format('d/m/Y H:i:s') }}</small>
        </div>
    </div>

    <script>
        // Auto-print when opened in print mode
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>
</html>
