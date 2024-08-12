<?php

namespace App\Services\Homebase\Import\Services;

use App\Domains\Neo4j\Staff\DataTransferObjects\CreateTimebreakDto;
use App\Domains\Neo4j\Staff\DataTransferObjects\CreateTimecardDto;
use App\Domains\Neo4j\Staff\Models\Shift;
use App\Domains\Neo4j\Staff\Models\Timecard;
use App\Domains\Neo4j\Staff\Services\ShiftReadService;
use App\Domains\Neo4j\Staff\Services\TimebreakReadService;
use App\Domains\Neo4j\Staff\Services\TimebreakWriteService;
use App\Domains\Neo4j\Staff\Services\TimecardReadService;
use App\Domains\Neo4j\Staff\Services\TimecardWriteService;
use App\Services\Homebase\Api\DataTransferObjects\ListTimecardRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\Services\TimecardApiService;
use App\Services\Homebase\Api\ValueObjects\Timebreak;
use App\Services\Homebase\Api\ValueObjects\Timecard as TimecardVO;
use Carbon\CarbonInterface;

readonly class TimecardImportService
{
    public function __construct(
        private TimecardApiService $timecardApiService,
        private TimecardReadService $timecardReadService,
        private ShiftReadService $shiftReadService,
        private TimecardWriteService $timecardWriteService,
        private TimebreakReadService $timebreakReadService,
        private TimebreakWriteService $timebreakWriteService,
    ) {
    }

    /**
     * @throws HomebaseApiException
     */
    public function run(CarbonInterface $startDate, CarbonInterface $endDate): void
    {
        $timecardsResponse = $this->timecardApiService->getAll(ListTimecardRequestDTO::from([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'locationUuid' => config('services.homebase.default_location_uuid'),
        ]));

        foreach ($timecardsResponse as $timecardVO) {
            $shift = $this->shiftReadService->getByColumnOrNull($timecardVO->shiftId, 'homebase_id');
            if (!$shift) {
                continue;
            }
            $timecard = $this->createTimecard($timecardVO, $shift);
            $this->createTimebreaks($timecardVO->timebreaks, $timecard);
        }
    }

    private function createTimecard(TimecardVO $timecardVO, Shift $shift): Timecard
    {
        $timecard = $this->timecardReadService->getByColumnOrNull($timecardVO->id, 'homebase_id');
        $createTimecardDto = CreateTimecardDto::from([
            'approved' => $timecardVO->approved ?? '',
            'shiftId' => $shift->id,
            'clockIn' => $timecardVO->clockIn,
            'clockOut' => $timecardVO->clockOut,
            'homebaseId' => $timecardVO->id,
        ]);

        if (!$timecard) {
            return $this->timecardWriteService->create($createTimecardDto);
        }

        return $this->timecardWriteService->update($timecard->id, $createTimecardDto);
    }

    /**
     * @param Timebreak[] $timebreaks
     * @param Timecard $timecard
     * @return void
     */
    private function createTimebreaks(array $timebreaks, Timecard $timecard): void
    {
        foreach ($timebreaks as $timebreakVO) {
            $timebreak = $this->timebreakReadService->getByColumnOrNull($timebreakVO->id, 'homebase_id');
            $createTimebreakDto = CreateTimebreakDto::from([
                'paid' => $timebreakVO->paid ?? '',
                'timecardId' => $timecard->id,
                'startAt' => $timebreakVO->startAt,
                'endAt' => $timebreakVO->endAt,
                'homebaseId' => $timebreakVO->id,
            ]);

            if (!$timebreak) {
                 $this->timebreakWriteService->create($createTimebreakDto);
                 continue;
            }

            $this->timebreakWriteService->update($timebreak->id, $createTimebreakDto);
        }
    }
}
