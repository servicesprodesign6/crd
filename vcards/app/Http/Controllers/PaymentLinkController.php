<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentLinkRequest;
use App\Http\Requests\UpdatePaymentLinkRequest;
use App\Models\VcardPaymentLink;
use App\Repositories\PaymentLinkRepository;
use Illuminate\Http\JsonResponse;

class PaymentLinkController extends AppBaseController
{
    /**
     * @var PaymentLinkRepository
     */
    private $paymentLinkRepo;

    /**
     * PaymentLinkController constructor.
     */
    public function __construct(PaymentLinkRepository $paymentLinkRepo)
    {
        $this->paymentLinkRepo = $paymentLinkRepo;
    }

    public function store(CreatePaymentLinkRequest $request): JsonResponse
    {
        $input = $request->all();

        $paymentLink = $this->paymentLinkRepo->store($input);

        return $this->sendResponse($paymentLink, __('messages.flash.payment_link_create'));
    }

    public function edit(VcardPaymentLink $paymentLink): JsonResponse
    {
        return $this->sendResponse($paymentLink, 'Payment Link successfully retrieved.');
    }

    public function update(UpdatePaymentLinkRequest $request, VcardPaymentLink $paymentLink): JsonResponse
    {
        $input = $request->all();

        $paymentLink = $this->paymentLinkRepo->update($input, $paymentLink->id);

        return $this->sendResponse($paymentLink, __('messages.flash.payment_link_update'));
    }

    public function destroy(VcardPaymentLink $paymentLink): JsonResponse
    {
        $paymentLink->clearMediaCollection(VcardPaymentLink::IMAGE_COLLECTION);
        $paymentLink->clearMediaCollection(VcardPaymentLink::DESCRIPTION_COLLECTION);
        $paymentLink->delete();

        return $this->sendSuccess(__('messages.flash.payment_link_delete'));
    }
}
