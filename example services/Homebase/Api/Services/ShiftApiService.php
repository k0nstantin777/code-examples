<?php

namespace App\Services\Homebase\Api\Services;

use App\Services\Homebase\Api\Client;
use App\Services\Homebase\Api\DataTransferObjects\ListShiftRequestDTO;
use App\Services\Homebase\Api\Endpoints\ListShifts;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Shift;

readonly class ShiftApiService
{
    public function __construct(
        private array $apiConfig,
    ) {
    }

    /**
     * @return Shift[]
     * @throws HomebaseApiException
     */
    public function getList(ListShiftRequestDTO $listShiftRequestDTO): array
    {
        $endpoint = app(ListShifts::class, ['client' => new Client($this->apiConfig)]);

        return $endpoint->execute($listShiftRequestDTO);
    }

    /**
     * @return Shift[]
     * @throws HomebaseApiException
     */
    public function getAll(ListShiftRequestDTO $listShiftRequestDTO): array
    {
        $response = $this->getList($listShiftRequestDTO);
        $result = $response;

        $page = 1;
        while (count($response) >= $listShiftRequestDTO->perPage) {
            $page++;
            $response = $this->getList(ListShiftRequestDTO::from([
                'page' => $page,
                'perPage' => $listShiftRequestDTO->perPage,
                'locationUuid' => $listShiftRequestDTO->locationUuid,
                'startDate' => $listShiftRequestDTO->startDate,
                'endDate' => $listShiftRequestDTO->endDate,
                'open' => $listShiftRequestDTO->open,
                'withNote' => $listShiftRequestDTO->withNote,
                'dateFilter' => $listShiftRequestDTO->dateFilter,
            ]));
            $result = array_merge($response, $result);
        }

        return $result;
    }
}
