<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\CreateFlowerProgramRequest;
use App\Http\Api\v1\Requests\FlowerProgramsPaginationRequest;
use App\Http\Api\v1\Resources\FlowerProgramResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateFlowerProgramRequestDto;
use App\Services\FFC\RequestDTOs\FlowerProgramsRequestDto;
use App\Services\FFC\Services\FlowerProgramService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Flower Programs
 *
 * APIs for managing flower programs
 */
class FlowerProgramController extends Controller
{
    /**
     * Get list account flower programs
     *
     * @responseFile storage/responses/flower-programs/index.json
     *
     * @param FlowerProgramsPaginationRequest $request
     * @param FlowerProgramService $flowerProgramService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function index(
        FlowerProgramsPaginationRequest $request,
        FlowerProgramService $flowerProgramService,
    ): JsonResponse {
        $orderList = $flowerProgramService->getList(FlowerProgramsRequestDto::from([
            'sort' => $request->getSort(),
            'sortDirection' => $request->getSortDirection(),
            'limit' => $request->getLimit(),
            'offset' => $request->getOffset(),
            'includes' => $request->getIncludes(),
            'status' => $request->getStatus(),
            'search' => $request->getSearch(),
            'userId' => $request->user()->ffc_id,
            'from' => $request->getFrom(),
            'to' => $request->getTo()
        ]));

        $result = [
            'data' => FlowerProgramResource::collection($orderList->getData()),
            'meta' => [
                'offset' => $orderList->getMeta()->getOffset(),
                'limit' => $orderList->getMeta()->getLimit(),
                'total' => $orderList->getMeta()->getTotal(),
            ]
        ];

        return response()->apiSuccess($result);
    }

    /**
     * Create flower program
     *
     * @responseFile storage/responses/flower-programs/create.json
     * @bodyParam monument object required Questionnaire on the monument
     * @bodyParam placements object[] required List of placements
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     */
    public function store(
        CreateFlowerProgramRequest $request,
        FlowerProgramService $flowerProgramService,
    ) {

        $result = $flowerProgramService->create(new CreateFlowerProgramRequestDto([
            'user_id' => $request->user()->ffc_id,
            'delivery_address_id' => $request->get('delivery_address_id'),
            'payment_address_id' => $request->get('payment_address_id'),
            'shipping_type' => $request->get('shipping_type'),
            'placements' => $request->get('placements'),
            'grave_id' => $request->get('grave_id'),
            'comment' => $request->get('comment'),
            'coupon' => $request->get('coupon'),
            'monument' => $request->get('monument'),
            'has_expired_notify' => $request->get('has_expired_notify'),
            'shipping_service' => $request->get('shipping_service'),
            'payment_service' => $request->get('payment_service'),
        ]));

        return response()->apiSuccess($result);
    }
}
