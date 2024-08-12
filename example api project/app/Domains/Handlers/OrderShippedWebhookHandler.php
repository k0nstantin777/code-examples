<?php

namespace App\Domains\Handlers;

use App\Domains\Webhook\DataTransferObjects\CreateWebhookEventDto;
use App\Domains\Webhook\Models\Webhook;
use App\Domains\Webhook\Services\WebhookEventService;
use App\Services\FFC\Enums\ShipmentStatus;
use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use App\Services\FFC\RequestDTOs\ShipmentsRequestDto;
use App\Services\FFC\Services\ShipmentService;
use App\Services\FFC\ValueObjects\Shipment;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrderShippedWebhookHandler implements WebhookHandler
{
    public function __construct(
        private readonly ShipmentService $shipmentService,
        private readonly WebhookEventService $webhookEventService,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     */
    public function handle(Webhook $webhook) : void
    {
        $shipments = $this->getShipmentsForLastMonth($webhook->user->ffc_id);

        foreach ($shipments as $shipment) {
            $webhookEvent = $this->webhookEventService->getByWebhookIdAndDataId($webhook->id, $shipment->id);

            if (!$webhookEvent) {
                $this->webhookEventService->create(CreateWebhookEventDto::from([
                    'webhookId' => $webhook->id,
                    'data' => $shipment->toArray(),
                ]));
            }
        }
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws InvalidSchemaException
     * @return Collection|Shipment[]
     */
    private function getShipmentsForLastMonth(int $userId): Collection
    {
        return $this->shipmentService->getList(ShipmentsRequestDto::from([
            'userId' => $userId,
            'status' => ShipmentStatus::SHIPPED,
            'shipFrom' => now()->subMonth()
        ]))->getData();
    }
}