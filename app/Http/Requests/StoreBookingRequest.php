<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'showtime_id' => ['required', 'exists:showtimes,id'],
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['required', 'exists:seats,id'],
            'payment_method' => ['required', Rule::in(['card', 'cash', 'online'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'showtime_id.required' => 'Please select a showtime.',
            'showtime_id.exists' => 'The selected showtime is invalid.',
            'seat_ids.required' => 'Please select at least one seat.',
            'seat_ids.array' => 'Invalid seat selection.',
            'seat_ids.min' => 'Please select at least one seat.',
            'seat_ids.*.exists' => 'One or more selected seats are invalid.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'The selected payment method is invalid.',
        ];
    }
} 