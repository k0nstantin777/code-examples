<?php

namespace App\Services\FFC\Services;

use App\Services\FFC\Endpoints\CreateFlowerProgram;
use App\Services\FFC\Endpoints\FlowerProgramList;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\CreateFlowerProgramRequestDto;
use App\Services\FFC\RequestDTOs\FlowerProgramsRequestDto;
use App\Services\FFC\ValueObjects\FlowerProgramList as FlowerProgramListValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Validation\ValidationException;

class FlowerProgramService
{
	public function __construct(
		private readonly FlowerProgramList $flowerProgramListEndpoint,
		private readonly CreateFlowerProgram $createFlowerProgramEndpoint,
	) {
	}

    /**
     * @param FlowerProgramsRequestDto $dto
     * @return FlowerProgramListValueObject
     * @throws InvalidSchemaException
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
	public function getList(FlowerProgramsRequestDto $dto) : FlowerProgramListValueObject
	{
		return $this->flowerProgramListEndpoint->execute($dto);
	}

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     */
    public function create(CreateFlowerProgramRequestDto $dto) : array
    {
        return $this->createFlowerProgramEndpoint->execute($dto);
    }
}