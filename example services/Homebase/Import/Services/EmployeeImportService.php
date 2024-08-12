<?php

namespace App\Services\Homebase\Import\Services;

use App\Domains\Neo4j\Staff\DataTransferObjects\CreateEmployeeDto;
use App\Domains\Neo4j\Staff\DataTransferObjects\CreateJobDto;
use App\Domains\Neo4j\Staff\Models\Employee;
use App\Domains\Neo4j\Staff\Services\EmployeeReadService;
use App\Domains\Neo4j\Staff\Services\EmployeeWriteService;
use App\Domains\Neo4j\Staff\Services\JobReadService;
use App\Domains\Neo4j\Staff\Services\JobWriteService;
use App\Services\Homebase\Api\DataTransferObjects\ListLocationRequestDTO;
use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use App\Services\Homebase\Api\Services\EmployeeApiService;
use App\Services\Homebase\Api\ValueObjects\Employee as EmployeeVO;
use App\Services\Homebase\Api\ValueObjects\Job as JobVO;

readonly class EmployeeImportService
{
    public function __construct(
        private EmployeeApiService $employeeApiService,
        private EmployeeWriteService $employeeWriteService,
        private EmployeeReadService $employeeReadService,
        private JobWriteService $jobWriteService,
        private JobReadService $jobReadService,
    ) {
    }

    /**
     * @throws HomebaseApiException
     */
    public function run(): void
    {
        $employeesResponse = $this->employeeApiService->getAll(ListLocationRequestDTO::from([
            'locationUuid' => config('services.homebase.default_location_uuid'),
        ]));

        foreach ($employeesResponse as $employeeVO) {
            $employee = $this->createEmployee($employeeVO);
            $this->createJob($employeeVO->job, $employee);
        }
    }

    private function createEmployee(EmployeeVO $employeeVO): Employee
    {
        $employee = $this->employeeReadService->getByColumnOrNull($employeeVO->id, 'homebase_id');
        $createEmployeeDto = CreateEmployeeDto::from([
            'firstName' => $employeeVO->firstName,
            'lastName' => $employeeVO->lastName,
            'email' => $employeeVO->email ?? '',
            'phone' => $employeeVO->phone ?? '',
            'homebaseId' => $employeeVO->id,
            'startDate' => $employee?->start_date,
        ]);

        if (!$employee) {
            return $this->employeeWriteService->create($createEmployeeDto);
        }

        return $this->employeeWriteService->update($employee->id, $createEmployeeDto);
    }

    private function createJob(JobVO $jobVO, Employee $employee): void
    {
        $job = $this->jobReadService->getByColumnOrNull($jobVO->id, 'homebase_id');

        $createJobDto = CreateJobDto::from([
            'level' => $jobVO->level,
            'employeeId' => $employee->id,
            'homebaseId' => $jobVO->id,
            'defaultRole' => $jobVO->defaultRole ?? '',
            'wageRate' => $jobVO->wageRate ?? '',
            'wageType' => $jobVO->wageType ?? '',
        ]);

        if (!$job) {
            $this->jobWriteService->create($createJobDto);
            return;
        }

        $this->jobWriteService->update($job->id, $createJobDto);
    }
}
