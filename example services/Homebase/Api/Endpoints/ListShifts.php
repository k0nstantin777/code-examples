<?php

namespace App\Services\Homebase\Api\Endpoints;

use App\Services\Homebase\Api\DataTransferObjects\ListShiftRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Shift;
use App\Services\Homebase\Api\ValueObjects\ShiftLabor;
use Illuminate\Support\Carbon;

class ListShifts extends BaseEndpoint
{
    protected const ENDPOINT = '/locations/:location_uuid/shifts';

    /**
     * @throws HomebaseApiException
     * @return Shift[]
     */
    public function execute(...$arguments): array
    {
        /** @var ListShiftRequestDTO $listRequestDto */
        [$listRequestDto] = $arguments;

        $url = str_replace(':location_uuid', $listRequestDto->locationUuid, self::ENDPOINT);

        $response = $this->client->get($url, [
            'page' => $listRequestDto->page,
            'per_page' => $listRequestDto->perPage,
            'start_date' => $listRequestDto->startDate->toDateTimeString(),
            'end_date' => $listRequestDto->endDate->toDateTimeString(),
            'date_filter' => $listRequestDto->dateFilter,
            'open' => $listRequestDto->open,
            'with_note' => $listRequestDto->withNote,
        ]);

        $items = [];
        foreach ($response as $itemData) {
            $items[] = Shift::from([
                'id' => $itemData['id'],
                'firstName' => $itemData['first_name'],
                'lastName' => $itemData['last_name'],
                'userId' => $itemData['user_id'],
                'timecardId' => $itemData['timecard_id'],
                'jobId' => $itemData['job_id'],
                'role' => $itemData['role'],
                'department' => $itemData['department'],
                'wageRate' => $itemData['wage_rate'],
                'open' => $itemData['open'],
                'published' => $itemData['published'],
                'scheduled' => $itemData['scheduled'],
                'createdAt' => Carbon::parse($itemData['created_at']),
                'updatedAt' => Carbon::parse($itemData['updated_at']),
                'startAt' => Carbon::parse($itemData['start_at']),
                'endAt' => Carbon::parse($itemData['end_at']),
                'labor' => ShiftLabor::from([
                    'scheduledHours' => $itemData['labor']['scheduled_hours'],
                    'scheduledOvertime' => $itemData['labor']['scheduled_overtime'],
                    'scheduledRegular' => $itemData['labor']['scheduled_regular'],
                    'scheduledDailyOvertime' => $itemData['labor']['scheduled_daily_overtime'],
                    'scheduledWeeklyOvertime' => $itemData['labor']['scheduled_weekly_overtime'],
                    'scheduledDoubleOvertimes' => $itemData['labor']['scheduled_double_overtimes'],
                    'scheduledSeventhDayOvertime15' => $itemData['labor']['scheduled_seventh_day_overtime_15'],
                    'scheduledSeventhDayOvertime20' => $itemData['labor']['scheduled_seventh_day_overtime_20'],
                    'scheduledUnpaidBreaksHours' => $itemData['labor']['scheduled_unpaid_breaks_hours'],
                    'scheduledCosts' => $itemData['labor']['scheduled_costs'],
                    'scheduledOvertimeCosts' => $itemData['labor']['scheduled_overtime_costs'],
                    'scheduledSpreadOfHours' => $itemData['labor']['scheduled_spread_of_hours'],
                    'scheduledBlueLawsHours' => $itemData['labor']['scheduled_blue_laws_hours'],
                    'wageType' => $itemData['labor']['wage_type'],
                ]),
            ]);
        }

        return $items;
    }
}
