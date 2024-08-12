<?php

namespace App\Services\FFC\RequestDTOs;

class FlowerProgramsRequestDto extends ListRequestDto
{
    public function __construct(
        public string $includes,
        public string $status,
        public string $search = '',
        public ?string $from = null,
        public ?string $to = null,
        public ?int $userId = null,
        int $limit = 100,
        int $offset = 0,
        string $sort = 'id',
        string $sortDirection = 'desc'
    ) {
        $this->status = $this->convertStatus();

        parent::__construct($limit, $offset, $sort, $sortDirection);
    }

    private function convertStatus(): string
    {
        $result = '';
        switch ($this->status) {
            case 'active':
                $result = 'awaiting_shipment';
                break;
            case 'shipped':
                $result = 'shipped';
                break;
            case 'cancelled':
                $result = 'cancelled';
                break;
            default:
                break;
        }

        return $result;
    }
}
