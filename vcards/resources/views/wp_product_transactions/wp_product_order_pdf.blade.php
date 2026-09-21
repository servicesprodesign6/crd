<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>{{ __('messages.whatsapp_product_order_invoice') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo {
            width: 36px;
        }

        .app-name {
            font-size: 15px;
            font-weight: 600;
            color: #6366f1;
        }

        .order-badge {
            background: #ebecf6;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: 700;
        }


        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px;
            background: #fefefe;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .label {
            font-size: 13px;
            color: #777;
        }

        .value {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .status-success {
            background: #daffe8;
            color: #159a46;
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .status-warning {
            background: #ffeccc;
            color: #e99300;
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .status-danger {
            background: #ffdbdb;
            color: #ef4444;
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .lineitems-box {
            background: #eef1ff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .product-table th {
            font-size: 13px;
            color: #6b7280;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .product-table td {
            font-size: 13px;
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .right {
            text-align: right;
        }

        .total-box {
            float: right;
            width: 240px;
            margin-top: 15px;
            margin-right: 18px;
        }

        .total-box-value {
            font-size: 13px;
            color: #6b7280;
            font-weight: 700;
        }

        .grand-total-box {
            margin-top: 5px;
            width: 240px;
            float: right;
            background: #F0F2FF;
            border-radius: 12px;
            padding: 10px 18px;
            border: 1px solid #e5e7eb;
            border-bottom: 3px solid #d6d9f7;
        }

        .grand-total-label {
            font-size: 16px;
            color: #7c8df6;
            font-weight: 700;
            vertical-align: middle;
        }

        .grand-total-value {
            font-size: 16px;
            font-weight: 700;
            color: #7c8df6;
            text-align: right;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <table style="border-collapse:collapse;">
                    <tr>
                        <td style="vertical-align:middle;">
                            <img src="{{ getAppLogo() }}" class="logo">
                        </td>
                        <td style="vertical-align:middle; padding-left:6px;">
                            <span class="app-name">{{ $appName }}</span>
                        </td>
                        <td style="vertical-align:middle; padding:0 12px; color:#9ca3af;"> | </td>

                        <td style="vertical-align:middle;">
                            <span class="order-badge">
                                {{ __('messages.whatsapp_stores.order_id') }}: #{{ $wpProductOrder->order_id }}
                            </span>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding-top:10px;">
                <div class="invoice-title">
                    {{ __('messages.whatsapp_product_order_invoice') }}
                </div>
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td width="48%" class="card" style="vertical-align:top;">
                <div class="section-title">{{ strtoupper(__('messages.user.user_details')) }}</div>
                <div class="label">{{ __('messages.common.name') }}</div>
                <div class="value">{{ $wpProductOrder->name }}</div>
                <div class="label">{{ __('messages.common.phone') }}</div>
                <div class="value">+{{ $wpProductOrder->region_code }} {{ $wpProductOrder->phone }}</div>
                <div class="label">{{ __('messages.user.address') }}</div>
                <div class="value">{{ $wpProductOrder->address }}</div>
            </td>
            <td width="4%"></td>
            <td width="48%" class="card" style="vertical-align:top;">
                <div class="section-title">{{ strtoupper(__('messages.whatsapp_stores.order_details')) }}</div>
                <div class="label">{{ __('messages.whatsapp_stores.store_name') }}</div>
                <div class="value">{{ $wpProductOrder->wpStore->store_name }}</div>
                <div class="label">{{ __('messages.common.status') }}</div>
                @if ($wpProductOrder->status == App\Models\WpOrder::DELIVERED)
                    <span class="status-success">{{ __('messages.delivered') }}</span>
                @elseif($wpProductOrder->status == App\Models\WpOrder::PENDING)
                    <span class="status-warning">{{ __('messages.pending') }}</span>
                @elseif($wpProductOrder->status == App\Models\WpOrder::DISPATCHED)
                    <span class="status-success">{{ __('messages.dispatched') }}</span>
                @else
                    <span class="status-danger">{{ __('messages.cancelled') }}</span>
                @endif

                <div style="margin-top:12px" class="label">{{ __('messages.whatsapp_stores.order_date') }}</div>
                <div class="value">{{ $wpProductOrder->created_at->format('d M, Y') }}</div>

                @if($wpProductOrder->order_type)
                    <div style="margin-top:12px" class="label">{{ __('messages.whatsapp_stores.order_type') }}</div>
                    <div class="value">{{ $wpProductOrder->order_type == 1 ? __('messages.whatsapp_stores.take_away') : __('messages.whatsapp_stores.delivery') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <br><br>
    <div class="section-title">{{ strtoupper(__('messages.vcard.product_details')) }}</div>
    <div class="lineitems-box">
        <table class="product-table">
            <thead>
                <tr>
                    <th width="50%">{{ __('messages.common.name') }}</th>
                    <th width="10%">{{ __('messages.whatsapp_stores_templates.quantity') }}</th>
                    <th width="20%" style="text-align: right;">{{ __('messages.common.price') }}</th>
                    <th width="20%" style="text-align: right;">{{ __('messages.whatsapp_stores_templates.total_price') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($wpProductOrder->products as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="right">{{ number_format($item->price, 2) }}</td>
                        <td class="right">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total-box">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td class="total-box-value">
                        {{ __('messages.nfc.total') }}
                    </td>
                    <td class="total-box-value right">
                        {{ number_format($wpProductOrder->products->sum('total_price'), 2) }}
                    </td>
                </tr>
                @if($wpProductOrder->discount_amount > 0)
                <tr>
                    <td class="total-box-value" style="padding-top: 5px;">
                        {{ __('messages.whatsapp_stores.discount') }}
                    </td>
                    <td class="total-box-value right" style="padding-top: 5px;">
                        - {{ number_format($wpProductOrder->discount_amount, 2) }}
                    </td>
                </tr>
                @endif
                @if($wpProductOrder->delivery_charge > 0)
                    <tr>
                        <td class="total-box-value" style="padding-top: 5px;">
                            + {{ __('messages.whatsapp_stores.delivery_charge') }}
                        </td>
                        <td class="total-box-value right" style="padding-top: 5px;">
                            + {{ number_format($wpProductOrder->delivery_charge, 2) }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>
        <div style="clear:both"></div>
        <div class="grand-total-box">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td class="grand-total-label">
                        {{ __('messages.whatsapp_stores.grand_total') }}
                    </td>
                    <td class="grand-total-value">
                        {{ number_format($wpProductOrder->grand_total, 2) }}
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear:both"></div>
    </div>
</body>

</html>
