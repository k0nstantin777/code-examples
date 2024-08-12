<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Enums\Ability;
use App\Domains\Account\Models\ApiUser;
use App\Services\FFC\Enums\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class PersonalTokenControllerTest extends TestCase
{
	use JsonRpcClientMock;
	use RefreshDatabase;

    public function testStore(): void
	{
		$this->setUpJsonRpcClientMock();

		$response = $this->postJson('/api/v1/token', [
			'email' => 'test@email.com',
			'password' => 'password',
		]);

        $response->assertStatus(200);

		$this->assertDatabaseHas(ApiUser::class, [
			'id' => 1,
			'name' => 'Test User',
			'email' => 'test@email.com',
			'password' => '',
			'ffc_id' => 11,
		]);

		$this->assertDatabaseHas('personal_access_tokens', [
			'id' => 1,
			'tokenable_type' => ApiUser::class,
			'tokenable_id' => 1,
			'name' => 'test@email.com',
            'abilities' => '[]'
		]);
    }

    public function testStoreWithSetAbilities(): void
    {
        $this->setUpJsonRpcClientMock([
            'auth' => [
                'id' => 11,
                'name' => 'Test User',
                'email' => 'test@email.com',
                'type' => UserType::ADMIN->value,
            ]
        ]);

        $response = $this->postJson('/api/v1/token', [
            'email' => 'test@email.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(ApiUser::class, [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '',
            'ffc_id' => 11,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => 1,
            'tokenable_type' => ApiUser::class,
            'tokenable_id' => 1,
            'name' => 'test@email.com',
            'abilities' => '["' . Ability::ACCESS_PRIVATE_API->value . '"]',
        ]);
    }

	public function testRemove(): void
	{
		$this->setUpJsonRpcClientMock();

		Sanctum::actingAs(
			ApiUser::factory()->create([
				'email' => 'test@email.com'
			]),
			[]
		);

		$response = $this->deleteJson('/api/v1/token');

		$response->assertStatus(200);
		$response->assertJson([
			'result' => 'success'
		]);
	}

	public function testStoreValidateError(): void
	{
		$this->setUpJsonRpcClientMock();

		$response = $this->postJson('/api/v1/token', [
			'email' => 'test@email.com',
		]);

		$response->assertStatus(200);
		$response->assertJson([
			'error' => [
				'password' => [
					'The password field is required.',
				]
			]
		]);
	}
}
