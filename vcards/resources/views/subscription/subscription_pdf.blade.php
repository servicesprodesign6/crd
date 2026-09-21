<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>{{ __('messages.subscription.subscription_invoice') }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #444;
            margin: 0;
        }

        .invoice-container {
            width: 100%;
            background: #fff;
        }

        .header {
            background: #4f46e5;
            color: #fff;
            padding: 20px;
        }

        .logo {
            width: 65px;
        }

        .app-name {
            font-size: 20px;
            font-weight: bold;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .invoice-meta {
            text-align: right;
            font-size: 16px;
            padding-top: 43px;
        }

        .card {
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            padding: 15px;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            table-layout: fixed;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            color: #888;
        }

        .value {
            font-weight: 600;
            word-break: break-all;
            word-wrap: break-word;
        }

        .plan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .plan-table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }

        .plan-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .badge {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 4px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .total-box {
            width: 50%;
            margin-top: 20px;
            float: right;
        }

        .total-table {
            width: 100%;
        }

        .total-table td {
            padding: 6px;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #ddd;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <div class="invoice-container">
        <table width="100%" class="header">
            <tr>
                <td width="30%">
                    <img src="{{ getAppLogo() }}" class="logo">
                    <div class="app-name">
                        {{ $appName }}
                    </div>
                </td>
                <div class="invoice-title">
                    {{ __('messages.subscription.subscription_invoice') }}
                </div>
                <td width="30%" class="invoice-meta">
                    <div>
                        {{ \Carbon\Carbon::now()->format('d M, Y') ?? 'N/A' }}
                    </div>
                    @if($subscription->transaction_id)
                        <div>
                            {{ __('messages.subscription.invoice_id') }}: {{ $subscription->transaction_id ?? 'N/A' }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <table width="100%" style="margin-top:20px; border-collapse: separate; border-spacing: 0;">
            <tr>
                <td width="48%" valign="top" class="card">
                    @php
                        $tenantUser = $subscription->tenant->user;
                        $orgParentUser = $tenantUser && $tenantUser->organisation_id ? \App\Models\User::find($tenantUser->organisation_id) : null;
                    @endphp
                    <div class="section-title">
                        {{ __('messages.user.user_details') }}
                    </div>
                    <table class="info-table">
                        <tr>
                            <td class="label">{{ __('messages.common.name') }}</td>
                            <td class="value">
                                @if($orgParentUser)
                                    {{ $orgParentUser->organisation_name ?? 'N/A' }}
                                @else
                                    {{ $tenantUser->full_name ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('messages.common.email') }}</td>
                            <td class="value">
                                @if($orgParentUser)
                                    {{ $orgParentUser->email ?? 'N/A' }}
                                @else
                                    {{ $tenantUser->email ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('messages.common.phone') }}</td>
                            <td class="value">
                                @if($orgParentUser)
                                    {{ $orgParentUser->contact ?? 'N/A' }}
                                @else
                                    {{ $tenantUser->contact ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="4%"></td>
                <td width="48%" valign="top" class="card">
                    <div class="section-title">
                        {{ __('messages.subscription.plan_details') }}
                    </div>
                    <table class="info-table">
                        <tr>
                            <td class="label">{{ __('messages.payment_method') }}</td>
                            <td class="value">
                                @if (($subscription->discount == null && $subscription->payable_amount == 0) || ($subscription->plan_amount == $subscription->discount && $subscription->payable_amount == 0))
                                    N/A
                                @else
                                    {{ \App\Models\Subscription::PAYMENT_GATEWAY[$subscription->payment_type] ?? $subscription->payment_type ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('messages.nfc.payment_status') }}</td>
                            <td class="value">
                                @if($subscription->payment_type == \App\Models\Subscription::TYPE_FREE)
                                    <span class="badge badge-success">{{ __('messages.free') }}</span>
                                @elseif($subscription->discount == null && $subscription->payable_amount == 0)
                                    <span class="badge badge-success">{{ __('messages.paid') }}</span>
                                @elseif($subscription->plan_amount == $subscription->discount && $subscription->payable_amount == 0)
                                    <span class="badge badge-success">{{ __('messages.paid') }}</span>
                                @elseif($subscription->payment_type == 'Cash' && ($subscription->status == \App\Models\Subscription::ACTIVE || $subscription->status == \App\Models\Subscription::INACTIVE))
                                    <span class="badge badge-success">{{ __('messages.paid') }}</span>
                                @else
                                    <span
                                        class="badge {{ $subscription->transaction_id ? 'badge-success' : 'badge-danger' }}">
                                        {{ $subscription->transaction_id ? __('messages.paid') :  __('messages.subscription.unpaid') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('messages.nfc.transaction_id') }}</td>
                            <td class="value" style="max-width:120px;">{{ $subscription->transactions->transaction_id ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-top:25px;font-weight:bold;font-size:16px;">
            {{ __('messages.subscription.subscription_details') }}
        </div>

        <table class="plan-table">
            <thead>
                <tr>
                    <th>{{ __('messages.subscription.plan_name') }}</th>
                    <th>{{ __('messages.subscription.subscribed_date') }}</th>
                    <th>{{ __('messages.subscription.expired_date') ?? 'N/A' }}</th>
                    <th style="text-align:right">
                        {{ __('messages.subscription.amount') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $subscription->plan->name ?? 'N/A' }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($subscription->starts_at)->format('d M, Y') ?? 'N/A' }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($subscription->ends_at)->format('d M, Y') ?? 'N/A' }}
                    </td>
                    <td style="text-align:right">
                        {{ $subscription->plan_amount ?? 0 }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <table class="total-table">
                @if ($subscription->tax_amount > 0)
                    <tr>
                        <td>{{ $taxName }}</td>
                        <td style="text-align:right">
                            + {{ $subscription->tax_amount ?? 'N/A' }}
                        </td>
                    </tr>
                @endif
                @php
                    $remainingBalance = ($subscription->plan_amount ?? 0) - ($subscription->discount ?? 0) + ($subscription->tax_amount ?? 0) - ($subscription->payable_amount ?? 0);
                 @endphp
                 @if (empty($subscription->trial_ends_at) && $remainingBalance > 0)
                     <tr>
                         <td>{{ __('messages.plan.remaining_balance') }}</td>
                         <td style="text-align:right">
                             - {{ $remainingBalance ?? 'N/A' }}
                         </td>
                     </tr>
                 @endif
                @if (isset($subscription->coupon_code_meta['couponCode']))
                    <tr>
                        <td>{{ __('messages.coupon_code.coupon_discount') }}</td>
                        <td style="text-align:right">
                            - {{ $subscription->discount ?? 'N/A' }}
                        </td>
                    </tr>
                @endif

                <tr class="total">
                    <td>{{ __('messages.common.total_amount') }}</td>
                    <td style="text-align:right">
                        {{ $subscription->payable_amount ?? 0 }}
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear:both"></div>
    </div>
</body>
</html>
