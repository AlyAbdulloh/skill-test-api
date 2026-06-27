<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseRequest extends FormRequest
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
            'house_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('mst_houses', 'house_number')->ignore($this->route('house')),
            ],
            'address' => ['sometimes', 'required', 'string'],
            'occupancy_status' => ['sometimes', 'required', 'in:occupied,unoccupied'],
        ];
    }
}
