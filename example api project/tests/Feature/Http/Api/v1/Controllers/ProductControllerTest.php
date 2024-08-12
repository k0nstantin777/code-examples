<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class ProductControllerTest extends TestCase
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

		$response = $this->get('/api/v1/products');

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
						'label' => 'test1',
						'price' => 10.05,
						'image' => '/storage/img.jpg',
						'category' => [
							'id' => 1,
							'code' => 'CTEST1',
							'label' => 'ctest1',
						]
					],
				],
				'meta' => [
					'offset' => 0,
					'limit' => 100,
					'total' => 1,
				]
			]
		]);
    }

	public function testIndexServerErrorByInvalidResponse(): void
	{
		$this->setUpJsonRpcClientMock([
			'products' => [
				'data' => [],
			]
		]);

		Sanctum::actingAs(
			ApiUser::factory()->create(),
			[]
		);

		$response = $this->get('/api/v1/products');

		$response->assertStatus(200);
		$response->assertJson([
			'error' => 'Server error'
		]);
	}

	public function testIndexAccessDenied(): void
	{
		$this->setUpJsonRpcClientMock();

		$response = $this->getJson('/api/v1/products');

		$response->assertStatus(200);
		$response->assertJson([
			'error' => 'Access Denied'
		]);
	}

	/**
	 * @param string $includes
	 * @param bool $hasError
	 * @return void
	 * @dataProvider validateIncludesDataProvider
	 */
	public function testIndexValidateIncludes(string $includes, bool $hasError): void
	{
		$this->setUpJsonRpcClientMock();

		Sanctum::actingAs(
			ApiUser::factory()->create(),
			[]
		);

		$response = $this->getJson('/api/v1/products?includes=' .$includes);

		$response->assertStatus(200);

		if ($hasError) {
			$response->assertJson([
				'error' => [
					'includes' => [
						"Includes contains invalid value(s)"
					]
				]
			]);
		} else {
			$response->assertJson([
				'result' => [
					'data' => [],
					'meta' => [],
				]
			]);
		}
	}

	public function validateIncludesDataProvider() : array
	{
		return [
			'Invalid includes word' => [
				'random',
				true,
			],
			'Valid includes' => [
				'price, category, image',
				false,
			],
			'Invalid includes separator' => [
				'price; category; image',
				true,
			]
		];
	}
}
