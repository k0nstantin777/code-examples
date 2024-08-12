<?php

namespace App\Http\Api\v1\Controllers;

use App\Domains\Webhook\DataTransferObjects\CreateWebhookDto;
use App\Domains\Webhook\Enums\WebhookType;
use App\Domains\Webhook\Services\WebhookService;
use App\Http\Api\v1\Requests\CreateWebhookRequest;
use App\Http\Api\v1\Requests\DestroyWebhookRequest;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @group Webhooks
 *
 * APIs for managing webhooks.
 * If you subscribe to a webhook, a POST request with data will be sent to the specified url, depending on the webhook type.
 * With the order_shipped webhook, the Shipment object will be sent (see <a href="/docs/#shipment-GETapi-v1-shipments">Shipments List</a>) after the order is shipped.
 */
class WebhookController extends Controller
{
    /**
     * Subscribe to webhook
     *
     * @responseFile storage/responses/webhooks/create.json
     *
     * @param CreateWebhookRequest $request
     * @param WebhookService $webhookService
     * @return JsonResponse
     */
	public function store(
		CreateWebhookRequest $request,
        WebhookService $webhookService,
	) : JsonResponse
	{
        $type = WebhookType::from($request->get('type'));
        $userId =  $request->user()->id;
        $dto = CreateWebhookDto::from([
            'userId' => $userId,
            'type' => $type,
            'url' => $request->get('url'),
        ]);

        $webhook = $webhookService->getByUserAndType($userId, $type);

        if ($webhook) {
            $webhookService->update($webhook->id, $dto);
        } else {
            $webhookService->create($dto);
        }

		return response()->apiSuccess('success');
	}

    /**
     * Delete webhook
     *
     * @responseFile storage/responses/webhooks/destroy.json
     *
     * @param DestroyWebhookRequest $request
     * @param WebhookService $webhookService
     * @return JsonResponse
     */
    public function destroy(
        DestroyWebhookRequest $request,
        WebhookService $webhookService
    ) : JsonResponse
    {
        $webhook = $webhookService->getByUserAndType($request->user()->id, WebhookType::from($request->get('type')));

        if (!$webhook) {
            throw new ModelNotFoundException('Webhook not found');
        }

        $webhookService->delete($webhook->id);

        return response()->apiSuccess('success');
    }
}
