<?php

namespace App\Http\Api\v1\Controllers;

use App\Domains\Account\DataTransferObjects\CreateApiUserDto;
use App\Domains\Account\DataTransferObjects\CreatePersonalTokenDto;
use App\Domains\Account\Repositories\Contracts\ApiUserRepository;
use App\Domains\Account\Services\ApiUserService;
use App\Domains\Account\Services\PersonalTokenAbilitiesService;
use App\Domains\Account\Services\PersonalTokenService;
use App\Http\Api\v1\Requests\AuthRequest;
use App\Http\Controllers\Controller;
use App\Services\FFC\Services\AuthService;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Personal token
 *
 * APIs for managing personal token
 */
class PersonalTokenController extends Controller
{
	/**
	 * Generate personal api token
	 *
	 * A new personal token will be generated every time a request is sent.
	 * @bodyParam email string required Your email from FFC account
	 * @bodyParam password string required Your password from FFC account
	 * @responseFile storage/responses/token/create.json
	 *
	 * @unauthenticated
	 * @throws AuthenticationException|InvalidSchemaException|ValidationException
     */
	public function store(
		AuthRequest $authRequest,
		AuthService $authService,
		ApiUserRepository $apiUserRepository,
		ApiUserService $apiUserService,
		PersonalTokenService $personalTokenService,
        PersonalTokenAbilitiesService $personalTokenAbilitiesService,
	) : JsonResponse
	{
		$email = $authRequest->get('email');
		$ffcUser = $authService->authorize($email, $authRequest->get('password'));

		$apiUser = $apiUserRepository->pushCondition(['email', $email])->first();

		if (null === $apiUser) {
			$apiUser = $apiUserService->create(new CreateApiUserDto([
				'email' => $ffcUser->getEmail(),
				'password' => '',
				'name' => $ffcUser->getName(),
				'ffc_id' => $ffcUser->getId(),
			]));
		}

		$personalToken = $personalTokenService->change(
			$apiUser,
			new CreatePersonalTokenDto([
				'name' => $email,
				'abilities' => $personalTokenAbilitiesService->getByFFCUser($ffcUser),
			])
		);

		return response()->apiSuccess($personalToken->plainTextToken);
	}

	/**
	 * Delete your personal api token
	 *
	 * @responseFile storage/responses/token/destroy.json
	 *
	 * @param Request $request
	 * @param PersonalTokenService $personalTokenService
	 * @return JsonResponse
	 */
	public function destroy(
		Request $request,
		PersonalTokenService $personalTokenService
	) : JsonResponse
	{
		$personalTokenService->deleteCurrent($request->user());

		return response()->apiSuccess('success');
	}
}
