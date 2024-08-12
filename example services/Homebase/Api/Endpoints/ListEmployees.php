<?php

namespace App\Services\Homebase\Api\Endpoints;

use App\Services\Homebase\Api\DataTransferObjects\ListLocationRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Employee;
use App\Services\Homebase\Api\ValueObjects\Job;
use Illuminate\Support\Carbon;

class ListEmployees extends BaseEndpoint
{
    protected const ENDPOINT = '/locations/:location_uuid/employees';

    /**
     * @throws HomebaseApiException
     * @return Employee[]
     */
    public function execute(...$arguments): array
    {
        /** @var ListLocationRequestDTO $listRequestDto */
        [$listRequestDto] = $arguments;

        $url = str_replace(':location_uuid', $listRequestDto->locationUuid, self::ENDPOINT);

        $response = $this->client->get($url, [
            'page' => $listRequestDto->page,
            'per_page' => $listRequestDto->perPage,
        ]);

        $items = [];
        foreach ($response as $itemData) {
            $items[] = Employee::from([
                'id' => $itemData['id'],
                'firstName' => $itemData['first_name'],
                'lastName' => $itemData['last_name'],
                'email' => $itemData['email'],
                'phone' => $itemData['phone'],
                'createdAt' => Carbon::parse($itemData['created_at']),
                'updatedAt' => Carbon::parse($itemData['updated_at']),
                'job' => Job::from([
                    'id' => $itemData['job']['id'],
                    'level' => $itemData['job']['level'],
                    'locationUuid' => $itemData['job']['location_uuid'],
                    'pin' => $itemData['job']['pin'],
                    'defaultRole' => $itemData['job']['default_role'],
                    'payrollId' => $itemData['job']['payroll_id'],
                    'wageRate' => $itemData['job']['wage_rate'],
                    'wageType' => $itemData['job']['wage_type'],
                    'roles' => $itemData['job']['roles'],
                ]),
            ]);
        }

        return $items;
    }
}
