<?php

namespace App\Http\Controllers;

use App\Models\VcardService;
use Illuminate\Http\JsonResponse;
use App\Models\ScheduleAppointment;
use App\Repositories\VcardServiceRepository;
use App\Http\Requests\CreateVcardServiceRequest;
use App\Http\Requests\UpdateVcardServiceRequest;

class VcardServiceController extends AppBaseController
{
    /**
     * @var VcardServiceRepository
     */
    private $vcardServiceRepo;

    /**
     * VcardServiceController constructor.
     */
    public function __construct(VcardServiceRepository $vcardServiceRepo)
    {
        $this->vcardServiceRepo = $vcardServiceRepo;
    }

    /**
     * @return mixed
     */
    public function index($id)
    {
    }

    public function store(CreateVcardServiceRequest $request): JsonResponse
    {
        $input = $request->all();

        $service = $this->vcardServiceRepo->store($input);

        return $this->sendResponse($service, __('messages.flash.create_service'));
    }

    public function edit(VcardService $vcardService): JsonResponse
    {
        return $this->sendResponse($vcardService, 'vCard  successfully retrieved.');
    }

    public function update(UpdateVcardServiceRequest $request, VcardService $vcardService): JsonResponse
    {
        $input = $request->all();

        $service = $this->vcardServiceRepo->update($input, $vcardService->id);

        return $this->sendResponse($service, __('messages.flash.update_service'));
    }

    public function destroy(VcardService $vcardService): JsonResponse
    {
        $isUsed = ScheduleAppointment::where('service_id', $vcardService->id)->exists();

        if ($isUsed) {
            return $this->sendError(__('messages.flash.service_already_in_use'));
        }
        $vcardService->clearMediaCollection(VcardService::SERVICES_PATH);
        $vcardService->delete();

        return $this->sendSuccess(__('messages.flash.delete_service'));
    }
}
