<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:255',
            'bio'       => 'nullable|string',
            'gender'    => 'nullable|in:male,female,other',
            'birthdate' => 'nullable|date|before:today',
            'instagram' => 'nullable|url',
            'linkedin'  => 'nullable|url',
            'github'    => 'nullable|url',
            'website'   => 'nullable|url',
        ];
    }
}
// This request class validates the input for updating a user's profile.
// It allows optional fields like phone, address,
