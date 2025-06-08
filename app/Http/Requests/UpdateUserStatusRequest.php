<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa diganti dengan policy jika perlu
    }

    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean',
        ];
    }
}
// This request class validates the input for updating a user's active status.
// It requires the `is_active` field to be present and a boolean value.
// The `authorize` method can be modified to include authorization logic if needed.
