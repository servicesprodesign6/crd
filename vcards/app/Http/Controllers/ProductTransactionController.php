<?php

namespace App\Http\Controllers;

use App\Mail\ProductOrderStatusMail;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProductTransactionController extends AppBaseController
{
    public function index()
    {
        return view('product_transactions.index');
    }

    public function show($id)
    {
        $productTransaction = ProductTransaction::whereId($id)->first();

        return view('product_transactions.show', compact('productTransaction'));
    }

    public function updateSendCustomerMail(Request $request)
    {
        $setting = UserSetting::where('user_id', Auth::id())->where('key', 'product_order_send_mail_customer')->first();
        try {
            DB::beginTransaction();
            if (! $setting) {
                $setting = UserSetting::create([
                    'user_id' => Auth::id(),
                    'key' => 'product_order_send_mail_customer',
                    'value' => $request->customer_mail ?? '1',
                ]);
            } else {
                $setting->update(['value' => $request->customer_mail ?? '1']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess(__('messages.flash.send_email_to_customer_success'));
    }

    public function updateSendUserMail(Request $request)
    {
        $setting = UserSetting::where('user_id', Auth::id())->where('key', 'product_order_send_mail_user')->first();
        try {
            DB::beginTransaction();
            if (! $setting) {
                $setting = UserSetting::create([
                    'user_id' => Auth::id(),
                    'key' => 'product_order_send_mail_user',
                    'value' => $request->user_mail ?? '1',
                ]);
            } else {
                $setting->update(['value' => $request->user_mail ?? '1']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess(__('messages.flash.send_email_to_user_success'));
    }

    public function updateOrderStatus(Request $request, ProductTransaction $productTransaction)
    {
        $newStatus = $request->input('status');
        $currentStatus = $productTransaction->order_status;

        if (($newStatus == ProductTransaction::DISPATCHED || $newStatus == ProductTransaction::DELIVERED) && $productTransaction->status != Product::APPROVED) {
            return $this->sendError(__('messages.nfc.cannot_dispatch_if_payment_not_approved'));
        }

        if ($currentStatus == ProductTransaction::PENDING) {
            if (!in_array($newStatus, [ProductTransaction::DISPATCHED, ProductTransaction::CANCELLED])) {
                return $this->sendError(__('messages.placeholder.invalid_request_parameters'));
            }
        } elseif ($currentStatus == ProductTransaction::DISPATCHED) {
            if (!in_array($newStatus, [ProductTransaction::DELIVERED])) {
                return $this->sendError(__('messages.placeholder.invalid_request_parameters'));
            }
        } else {
             return $this->sendError(__('messages.placeholder.invalid_request_parameters'));
        }

        if ($newStatus == ProductTransaction::CANCELLED) {
            $productTransaction->update(['status' => Product::REJECT]);
        }

        $productTransaction->update(['order_status' => $newStatus]);
        $productTransaction->load(['product', 'currency']);

        Mail::to($productTransaction->email)->send(new ProductOrderStatusMail($productTransaction, $newStatus));

        return $this->sendSuccess(__('messages.nfc.order_status_update_success'));
    }

    public function downloadProductOrderPdf($id)
    {
        $productTransaction = ProductTransaction::with(['product', 'currency'])->findOrFail($id);
        $appLogo = getLogoUrl();
        $appName = getAppName();

        $pdf = \PDF::loadView('product_transactions.product_order_pdf', compact('productTransaction', 'appLogo', 'appName'));

        return $pdf->download('product-order-' . $productTransaction->id . '.pdf');
    }
}
