<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // This request class validates the input for storing a food log entry.
    // It requires the `meal_time`, `foods`, and optionally `time`, `symptoms`, and `concerns` fields.
    // The `meal_time` must be one of the specified values (pagi, siang, malam).
    public function rules(): array
    {
        return [
            'meal_time' => 'required|string|in:pagi,siang,malam',
            'time'      => 'nullable|date_format:H:i',
            'foods'     => 'required|string',
            'symptoms'  => 'nullable|string',
            'concerns'  => 'nullable|string',
        ];
    }
}
