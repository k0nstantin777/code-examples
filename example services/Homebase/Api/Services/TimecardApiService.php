<?php

namespace App\Services\Homebase\Api\Services;

use App\Services\Homebase\Api\Client;
use App\Services\Homebase\Api\DataTransferObjects\ListTimecardRequestDTO;
use App\Services\Homebase\Api\Endpoints\ListTimecards;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Timecard;

readonly class TimecardApiService
{
    public function __construct(
        private array $apiConfig,
    ) {
    }

    /**
     * @return Timecard[]
     * @throws HomebaseApiException
     */
    public function getList(ListTimecardRequestDTO $listTimecardRequestDTO): array
    {
        $endpoint = app(ListTimecards::class, ['client' => new Client($this->apiConfig)]);

        return $endpoint->execute($listTimecardRequestDTO);
    }

    /**
     * @return Timecard[]
     * @throws HomebaseApiException
     */
    public function getAll(ListTimecardRequestDTO $listShiftRequestDTO): array
    {
        $response = $this->getList($listShiftRequestDTO);
        $result = $response;

        $page = 1;
        while (count($response) >= $listShiftRequestDTO->perPage) {
            $page++;
            $response = $this->getList(ListTimecardRequestDTO::from([
                'page' => $page,
                'perPage' => $listShiftRequestDTO->perPage,
                'locationUuid' => $listShiftRequestDTO->locationUuid,
                'startDate' => $listShiftRequestDTO->startDate,
                'endDate' => $listShiftRequestDTO->endDate,
                'dateFilter' => $listShiftRequestDTO->dateFilter,
            ]));
            $result = array_merge($response, $result);
        }

        return $result;
    }
}
