<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseRequest extends FormRequest
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
            'house_number' => ['required', 'string', 'max:20', 'unique:mst_houses,house_number'],
            'address' => ['required', 'string'],
            'occupancy_status' => ['required', 'in:occupied,unoccupied'],
        ];
    }
}
