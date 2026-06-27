<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
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
            'expense_date' => ['sometimes', 'required', 'date'],
            'notes' => ['sometimes', 'required', 'string'],
            'details' => ['sometimes', 'required', 'array', 'min:1'],
            'details.*.title' => ['required_with:details', 'string', 'max:255'],
            'details.*.amount' => ['required_with:details', 'numeric', 'min:0'],
        ];
    }
}
