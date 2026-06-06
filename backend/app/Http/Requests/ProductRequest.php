<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by 'role:admin' middleware
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', "unique:products,slug,{$productId}"],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ];
    }
}
