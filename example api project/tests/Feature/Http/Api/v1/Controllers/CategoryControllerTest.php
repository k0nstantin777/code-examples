<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
	use JsonRpcClientMock;
	use RefreshDatabase;

    public function testIndex(): void
	{
		$this->setUpJsonRpcClientMock();

		Sanctum::actingAs(
			ApiUser::factory()->create(),
			[]
		);

		$response = $this->get('/api/v1/categories');

        $response->assertStatus(200);
		$response->assertJsonStructure([
			'result' => [
				'data',
				'meta'
			]
		]);
        $response->assertJson([
			'result' => [
				'data' => [
					[
						'id' => 1,
						'code' => 'TEST1',
						'label' => 'test1'
					],
					[
						'id' => 2,
						'code' => 'TEST2',
						'label' => 'test2'
					],
				],
				'meta' => [
					'offset' => 0,
					'limit' => 100,
					'total' => 1,
				],
			]
		]);
    }

	public function testIndexServerErrorByInvalidResponse(): void
	{
		$this->setUpJsonRpcClientMock([
			'categories' => [
				'data' => [],
			]
		]);

		Sanctum::actingAs(
			ApiUser::factory()->create(),
			[]
		);

		$response = $this->get('/api/v1/categories');

		$response->assertStatus(200);
		$response->assertJson([
			'error' => 'Server error'
		]);
	}

	public function testIndexAccessDenied(): void
	{
		$this->setUpJsonRpcClientMock();

		$response = $this->getJson('/api/v1/categories');

		$response->assertStatus(200);
		$response->assertJson([
			'error' => 'Access Denied'
		]);
	}
}
