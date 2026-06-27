<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'expense_date' => ['required', 'date'],
            'notes' => ['required', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.title' => ['required', 'string', 'max:255'],
            'details.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
