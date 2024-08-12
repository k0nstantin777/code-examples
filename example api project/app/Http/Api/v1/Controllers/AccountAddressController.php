<?php

namespace App\Http\Api\v1\Controllers;

use App\Http\Api\v1\Requests\CreateAccountAddressRequest;
use App\Http\Controllers\Controller;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateAccountAddressRequestDto;
use App\Services\FFC\Services\AccountAddressService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

/**
 * @group Account
 *
 */
class AccountAddressController extends Controller
{
    /**
     * Create account address
     *
     * @responseFile storage/responses/account/addresses/create.json
     *
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     */
    public function store(
        CreateAccountAddressRequest $request,
        AccountAddressService $accountAddressService,
    ) {
        $result = $accountAddressService->create(CreateAccountAddressRequestDto::from([
            'userId' => $request->user()->ffc_id,
            'postal' => $request->get('postal'),
            'state' => $request->get('state'),
            'address1' => $request->get('address1'),
            'city' => $request->get('city'),
            'email' => $request->get('email'),
            'salutation' => $request->get('salutation'),
            'firstname' => $request->get('firstname') ?? '',
            'lastname' => $request->get('lastname') ?? '',
            'company' => $request->get('company') ?? '',
            'telephone' => $request->get('telephone') ?? '',
            'address2' => $request->get('address2') ?? '',
        ]));

        return response()->apiSuccess($result);
    }
}
