<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\CreateAccountGraveRequest;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountGraveRequestDto;
use App\Services\FFC\Services\AccountGraveService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

/**
 * @group Account
 *
 */
class AccountGraveController extends Controller
{
    /**
     * Create account grave
     *
     * @responseFile storage/responses/account/graves/create.json
     *
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     */
    public function store(
        CreateAccountGraveRequest $request,
        AccountGraveService $accountGraveService,
    ) {
        $result = $accountGraveService->create(CreateAccountGraveRequestDto::from([
            'userId' => $request->user()->ffc_id,
            'cemeteryId' => $request->get('cemetery_id'),
            'lovedInfo' => $request->get('loved_info'),
            'contactPhone' => $request->get('contact_phone'),
            'section' => $request->get('section') ?? '',
            'lot' => $request->get('lot') ?? '',
            'space' => $request->get('space') ?? '',
            'building' => $request->get('building') ?? '',
            'tier' => $request->get('tier') ?? '',
            'notes' => $request->get('notes') ?? '',
        ]));

        return response()->apiSuccess($result);
    }
}
