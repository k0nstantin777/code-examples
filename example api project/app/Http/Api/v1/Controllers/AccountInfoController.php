<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\AccountInfoRequest;
use App\Http\Api\v1\Resources\AccountInfoResource;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\AccountInfoRequestDto;
use App\Services\FFC\Services\AccountInfoService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group Account
 *
 * APIs for managing account
 */
class AccountInfoController extends Controller
{
    /**
     * Get account information
     *
     * Use this endpoint for retrieve your addresses or graves
     *
     * @responseFile storage/responses/account/index.json
     *
     * @param AccountInfoRequest $request
     * @param AccountInfoService $accountInfoService
     * @return JsonResponse
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function index(
        AccountInfoRequest $request,
        AccountInfoService $accountInfoService,
    ): JsonResponse {

        $accountInfo = $accountInfoService->get(new AccountInfoRequestDto([
            'includes' => $request->getIncludes(),
            'user_id' => $request->user()->ffc_id,
        ]));

        return response()->apiSuccess(new AccountInfoResource($accountInfo));
    }
}
