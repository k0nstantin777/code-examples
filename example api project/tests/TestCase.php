<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;
use Tests\Stubs\FakeLogger\LogFake;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();

		Log::swap(new LogFake());
	}
}
