<?php

namespace App\Http\Api\v1\Requests;

use App\Domains\Webhook\Enums\WebhookType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroyWebhookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'type' => ['required', Rule::in(WebhookType::values())],
        ];
    }
}
