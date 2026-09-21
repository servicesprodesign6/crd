<?php

namespace App\Repositories;

use App\Models\VcardPaymentLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Exception;

class PaymentLinkRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'label',
        'display_type',
        'description',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return VcardPaymentLink::class;
    }

    /**
     * @return mixed
     */
    public function store($input)
    {
        try {
            DB::beginTransaction();

            $paymentLink = VcardPaymentLink::create($input);

            if (isset($input['icon']) && ! empty($input['icon'])) {
                $paymentLink->addMedia($input['icon'])->toMediaCollection(VcardPaymentLink::IMAGE_COLLECTION);
            }

            if (isset($input['image']) && ! empty($input['image']) && $input['display_type'] == VcardPaymentLink::IMAGE) {
                $paymentLink->clearMediaCollection(VcardPaymentLink::DESCRIPTION_COLLECTION);
                $paymentLink->addMedia($input['image'])->toMediaCollection(VcardPaymentLink::DESCRIPTION_COLLECTION);
            }

            DB::commit();

            return $paymentLink;
        } catch (Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return Builder|Builder[]|Collection|Model
     */
    public function update($input, $id)
    {
        try {
            DB::beginTransaction();

            $paymentLink = VcardPaymentLink::findOrFail($id);
            $paymentLink->update($input);

            if (isset($input['icon']) && ! empty($input['icon'])) {
                $paymentLink->clearMediaCollection(VcardPaymentLink::IMAGE_COLLECTION);
                $paymentLink->addMedia($input['icon'])->toMediaCollection(VcardPaymentLink::IMAGE_COLLECTION);
            }

            if (isset($input['image']) && ! empty($input['image']) && $input['display_type'] == VcardPaymentLink::IMAGE) {
                $paymentLink->clearMediaCollection(VcardPaymentLink::DESCRIPTION_COLLECTION);
                $paymentLink->addMedia($input['image'])->toMediaCollection(VcardPaymentLink::DESCRIPTION_COLLECTION);
            }

            DB::commit();

            return $paymentLink;
        } catch (Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
