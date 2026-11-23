<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isSeller();
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $sellerId = $this->user()->id;

        return [
            'store_id' => [
                'required',
                Rule::exists('stores', 'id')->where('user_id', $sellerId),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'food_name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'original_price' => ['required', 'numeric', 'min:0.02', 'max:99999.99'],
            'discounted_price' => ['required', 'numeric', 'min:0.01', 'lt:original_price'],
            'available_quantity' => [
                'required',
                'integer',
                $isUpdate ? 'min:0' : 'min:1',
                'max:10000',
            ],
            'expiry_label' => ['required', 'string', 'max:150'],
            'storage_instructions' => ['required', 'string', 'max:1000'],
            'allergen_info' => ['required', 'string', 'max:1000'],
            'collection_deadline' => ['required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.exists' => 'You can only publish listings for stores that belong to your account.',
            'discounted_price.lt' => 'The discounted price must be strictly lower than the original price.',
            'available_quantity.min' => 'Available quantity must be greater than zero when publishing a new listing.',
            'collection_deadline.after' => 'The collection deadline must be a future date and time.',
            'image.max' => 'Food image must not exceed 2 MB.',
        ];
    }
}
