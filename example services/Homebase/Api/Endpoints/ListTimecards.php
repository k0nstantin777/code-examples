<?php

namespace App\Services\Homebase\Api\Endpoints;

use App\Services\Homebase\Api\DataTransferObjects\ListTimecardRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\ValueObjects\Timebreak;
use App\Services\Homebase\Api\ValueObjects\TimecardLabor;
use App\Services\Homebase\Api\ValueObjects\Timecard;
use Illuminate\Support\Carbon;

class ListTimecards extends BaseEndpoint
{
    protected const ENDPOINT = '/locations/:location_uuid/timecards';

    /**
     * @throws HomebaseApiException
     * @return Timecard[]
     */
    public function execute(...$arguments): array
    {
        /** @var ListTimecardRequestDTO $listRequestDto */
        [$listRequestDto] = $arguments;

        $url = str_replace(':location_uuid', $listRequestDto->locationUuid, self::ENDPOINT);

        $response = $this->client->get($url, [
            'page' => $listRequestDto->page,
            'per_page' => $listRequestDto->perPage,
            'start_date' => $listRequestDto->startDate->toDateTimeString(),
            'end_date' => $listRequestDto->endDate->toDateTimeString(),
            'date_filter' => $listRequestDto->dateFilter
        ]);

        $items = [];
        foreach ($response as $itemData) {
            $items[] = Timecard::from([
                'id' => $itemData['id'],
                'firstName' => $itemData['first_name'],
                'lastName' => $itemData['last_name'],
                'userId' => $itemData['user_id'],
                'payrollId' => $itemData['payroll_id'],
                'jobId' => $itemData['job_id'],
                'shiftId' => $itemData['shift_id'],
                'role' => $itemData['role'],
                'department' => $itemData['department'],
                'timebreaks' => $this->fillTimebreaks($itemData['timebreaks']),
                'approved' => $itemData['approved'],
                'createdAt' => Carbon::parse($itemData['created_at']),
                'updatedAt' => Carbon::parse($itemData['updated_at']),
                'clockIn' => $itemData['clock_in'] ? Carbon::parse($itemData['clock_in']) : null,
                'clockOut' => $itemData['clock_out'] ? Carbon::parse($itemData['clock_out']) : null,
                'labor' => TimecardLabor::from([
                    'breakPenalty' => $itemData['labor']['break_penalty'],
                    'costs' => $itemData['labor']['costs'],
                    'cashTips' => $itemData['labor']['cash_tips'],
                    'creditTips' => $itemData['labor']['credit_tips'],
                    'weeklyOvertime' => $itemData['labor']['weekly_overtime'],
                    'paidTimeOffHours' => $itemData['labor']['paid_time_off_hours'],
                    'timeOffHours' => $itemData['labor']['time_off_hours'],
                    'unpaidBreakHours' => $itemData['labor']['unpaid_break_hours'],
                    'regularHours' => $itemData['labor']['regular_hours'],
                    'paidHours' => $itemData['labor']['paid_hours'],
                    'scheduledHours' => $itemData['labor']['scheduled_hours'],
                    'dailyOvertime' => $itemData['labor']['daily_overtime'],
                    'doubleOvertime' => $itemData['labor']['double_overtime'],
                    'seventhDayOvertime15' => $itemData['labor']['seventh_day_overtime_15'],
                    'seventhDayOvertime20' => $itemData['labor']['seventh_day_overtime_20'],
                    'wageRate' => $itemData['labor']['wage_rate'],
                    'wageType' => $itemData['labor']['wage_type'],
                ]),
            ]);
        }

        return $items;
    }

    private function fillTimebreaks(array $timebreaks): array
    {
        $result = [];
        foreach ($timebreaks as $timebreakData) {
            $result[] = Timebreak::from([
                'id' => $timebreakData['id'],
                'mandatedBreakId' => $timebreakData['mandated_break_id'],
                'timecardId' => $timebreakData['timecard_id'],
                'paid' => $timebreakData['paid'],
                'duration' => $timebreakData['duration'],
                'workPeriod' => $timebreakData['work_period'],
                'createdAt' => Carbon::parse($timebreakData['created_at']),
                'updatedAt' => Carbon::parse($timebreakData['updated_at']),
                'startAt' => Carbon::parse($timebreakData['start_at']),
                'endAt' => Carbon::parse($timebreakData['end_at']),
            ]);
        }

        return $result;
    }
}
