<?php

namespace App\Services\Homebase\Api\Services;

use App\Services\Homebase\Api\Client;
use App\Services\Homebase\Api\DataTransferObjects\ListLocationRequestDTO;
use App\Services\Homebase\Api\Endpoints\ListEmployees;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Employee;

readonly class EmployeeApiService
{
    public function __construct(
        private array $apiConfig,
    ) {
    }

    /**
     * @return Employee[]
     * @throws HomebaseApiException
     */
    public function getList(ListLocationRequestDTO $listLocationRequestDTO): array
    {
        $endpoint = app(ListEmployees::class, ['client' => new Client($this->apiConfig)]);

        return $endpoint->execute($listLocationRequestDTO);
    }

    /**
     * @return Employee[]
     * @throws HomebaseApiException
     */
    public function getAll(ListLocationRequestDTO $listLocationRequestDTO): array
    {
        $response = $this->getList($listLocationRequestDTO);
        $result = $response;

        $page = 1;
        while (count($response) >= $listLocationRequestDTO->perPage) {
            $page++;
            $response = $this->getList(ListLocationRequestDTO::from([
                'page' => $page,
                'perPage' => $listLocationRequestDTO->perPage,
                'locationUuid' => $listLocationRequestDTO->locationUuid,
            ]));
            $result = array_merge($response, $result);
        }

        return $result;
    }
}
