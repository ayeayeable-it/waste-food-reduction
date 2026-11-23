<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'acknowledged_product_info' => ['accepted'],
            'acknowledged_expiry' => ['accepted'],
            'acknowledged_allergens' => ['accepted'],
            'acknowledged_storage' => ['accepted'],
            'acknowledged_collection' => ['accepted'],
            'disclaimer_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'acknowledged_product_info.accepted' => 'You must acknowledge the product description.',
            'acknowledged_expiry.accepted' => 'You must acknowledge the expiry/date label.',
            'acknowledged_allergens.accepted' => 'You must acknowledge the allergen information.',
            'acknowledged_storage.accepted' => 'You must acknowledge the storage instructions.',
            'acknowledged_collection.accepted' => 'You must acknowledge the store collection instructions.',
            'disclaimer_accepted.accepted' => 'You must accept the RescueBite platform disclaimer before reserving.',
        ];
    }
}
