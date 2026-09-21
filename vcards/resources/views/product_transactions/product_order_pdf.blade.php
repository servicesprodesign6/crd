<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>{{ __('messages.product_order_invoice') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
        }

        .invoice-box {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .header-table {
            width: 100%;
        }

        .logo {
            width: 50px;
        }

        .app-name {
            font-size: 18px;
            font-weight: 600;
            color: #4f46e5;
        }

        .order-badge {
            background: #e9edf5;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .card {
            background: #fefefe;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .section {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 10px;
        }

        .detail-label {
            font-size: 14px;
            color: #777;
        }

        .detail-value {
            font-size: 14px;
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

        .order-card {
            background: #eef1ff;
            border-radius: 10px;
            padding: 20px;
        }

        .amount {
            font-size: 26px;
            font-weight: 700;
            color: #4f46e5;
            text-align: right;
        }

        .meta {
            font-size: 13px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <img src="{{ getAppLogo() }}" class="logo"><br>
                    <span class="app-name">{{ $appName }}</span>
                </td>
                <td align="right">
                    <div class="invoice-title">{{ __('messages.product_order_invoice') }}</div>
                    <span class="order-badge">
                        {{ __('messages.whatsapp_stores.order_id') }}: #{{ $productTransaction->id ?? 'N/A' }}
                    </span>
                </td>
            </tr>
        </table>

        <table width="100%" class="section">
            <tr>
                <td width="49%" valign="top" class="card">
                    <div class="section-title">
                        {{ strtoupper(__('messages.user.user_details')) }}
                    </div>
                    <div class="detail-label">{{ __('messages.common.name') }}</div>
                    <div class="detail-value">
                        {{ $productTransaction->name ?? 'N/A' }}
                    </div>
                    <div class="detail-label">{{ __('messages.common.email') }}</div>
                    <div class="detail-value">
                        {{ $productTransaction->email ?? 'N/A' }}
                    </div>
                    <div class="detail-label">{{ __('messages.common.phone') }}</div>
                    <div class="detail-value">
                        {{ $productTransaction->phone ?? 'N/A' }}
                    </div>
                    <div class="detail-label">{{ __('messages.user.address') }}</div>
                    <div class="detail-value">
                        {{ $productTransaction->address ?? 'N/A' }}
                    </div>
                </td>
                <td width="4%"></td>
                <td width="49%" valign="top" class="card">
                    <div class="section-title">
                        {{ strtoupper(__('messages.nfc.payment_details')) }}
                    </div>
                    <div class="detail-label">{{ __('messages.payment_type') }}</div>
                    <div class="detail-value">
                        {{ __('messages.' . strtolower(App\Models\Product::PAYMENT_METHOD[$productTransaction->type])) ?? 'N/A' }}
                    </div>
                    <div class="detail-label">{{ __('messages.common.status') }}</div>
                    @if ($productTransaction->status == App\Models\Product::APPROVED)
                        <span class="status-success">{{ __('messages.approved') }}</span>
                    @elseif($productTransaction->status == App\Models\Product::PENDING)
                        <span class="status-warning">
                            {{ __('messages.pending') }}
                        </span>
                    @else
                        <span class="status-danger">
                            {{ __('messages.common.rejected') }}
                        </span>
                    @endif
                </td>
            </tr>
        </table>

        <div class="section">
            <table width="100%" style="margin-bottom: 10px;">
                <tr>
                    <td class="section-title" valign="middle" style="margin-bottom: 0;">
                        {{ strtoupper(__('messages.whatsapp_stores.order_details')) }}
                    </td>
                    <td align="right" valign="middle">
                        <!-- <span class="detail-label">{{ __('messages.nfc.order_status') }}:</span> -->
                        @if ($productTransaction->order_status == App\Models\ProductTransaction::DELIVERED)
                            <span class="status-success">{{ __('messages.delivered') }}</span>
                        @elseif($productTransaction->order_status == App\Models\ProductTransaction::CANCELLED)
                            <span class="status-danger">{{ __('messages.cancelled') }}</span>
                        @elseif($productTransaction->order_status == App\Models\ProductTransaction::PENDING)
                            <span class="status-warning">{{ __('messages.pending') }}</span>
                        @else
                            <span class="status-warning">{{ __('messages.dispatched') }}</span>
                        @endif
                    </td>
                </tr>
            </table>
            <div class="order-card">
                <table width="100%">
                    <tr>
                        <td>
                            <div class="detail-label">
                                {{ __('messages.vcard.product_name') }}
                            </div>
                            <div class="detail-value">
                                {{ $productTransaction->product->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="amount">
                            {{ $productTransaction->currency->currency_icon ?? '$' }}{{ number_format($productTransaction->amount, 2) ?? 0 }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta">
                            {{ __('messages.whatsapp_stores.order_date') }}<br>
                            <div class="detail-value">
                                {{ $productTransaction->created_at->format('d M, Y') ?? 'N/A' }}
                            </div>
                        </td>
                        @if (!empty($productTransaction->transaction_id))
                            <td class="meta" align="right">
                                {{ __('messages.nfc.transaction_id') }}<br>
                                <div class="detail-value">
                                    {{ $productTransaction->transaction_id }}
                                </div>
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
