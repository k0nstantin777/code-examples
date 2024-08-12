<?php

namespace App\Services\Homebase\Import\Services;

use App\Domains\Neo4j\Staff\DataTransferObjects\CreateShiftDto;
use App\Domains\Neo4j\Staff\Models\Employee;
use App\Domains\Neo4j\Staff\Services\EmployeeReadService;
use App\Domains\Neo4j\Staff\Services\ShiftReadService;
use App\Domains\Neo4j\Staff\Services\ShiftWriteService;
use App\Services\Homebase\Api\DataTransferObjects\ListShiftRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\Services\ShiftApiService;
use App\Services\Homebase\Api\ValueObjects\Shift as ShiftVO;
use Carbon\CarbonInterface;

readonly class ShiftImportService
{
    public function __construct(
        private ShiftApiService $shiftApiService,
        private EmployeeReadService $employeeReadService,
        private ShiftReadService $shiftReadService,
        private ShiftWriteService $shiftWriteService,
    ) {
    }

    /**
     * @throws HomebaseApiException
     */
    public function run(CarbonInterface $startDate, CarbonInterface $endDate): void
    {
        $shiftsResponse = $this->shiftApiService->getAll(ListShiftRequestDTO::from([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'locationUuid' => config('services.homebase.default_location_uuid'),
        ]));

        foreach ($shiftsResponse as $shiftVO) {
            $employee = $this->employeeReadService->getByColumnOrNull($shiftVO->userId, 'homebase_id');
            if (!$employee) {
                continue;
            }
            $this->createShift($shiftVO, $employee);
        }
    }

    private function createShift(ShiftVO $shiftVO, Employee $employee): void
    {
        $shift = $this->shiftReadService->getByColumnOrNull($shiftVO->id, 'homebase_id');
        $createShiftDto = CreateShiftDto::from([
            'role' => $shiftVO->role ?? '',
            'department' => $shiftVO->department ?? '',
            'employeeId' => $employee->id,
            'startAt' => $shiftVO->startAt,
            'endAt' => $shiftVO->endAt,
            'homebaseId' => $shiftVO->id,
        ]);

        if (!$shift) {
            $this->shiftWriteService->create($createShiftDto);
            return;
        }

        $this->shiftWriteService->update($shift->id, $createShiftDto);
    }
}
