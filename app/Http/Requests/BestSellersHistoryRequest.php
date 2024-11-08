<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BestSellersHistoryRequest extends FormRequest
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
            'author' => 'sometimes|string',
            'isbn' => 'sometimes|array',
            'isbn.*' => ['regex:/^(?:\d{10}|\d{13})$/'], // Must be 10 or 13 digits
            'title' => 'sometimes|string',
            'offset' => 'sometimes|integer|multiple_of:20|min:0'
        ];
    }

    /**
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'isbn.*.regex' => 'Each ISBN must be exactly 10 or 13 digits'
        ];
    }

    public function prepareForValidation(): void
    {
        // If ISBN comes as a single string, convert it to an array
        if ($this->has('isbn') && is_string($this->isbn)) {
            $this->merge([
                'isbn' => explode(',', $this->isbn)
            ]);
        }
    }
}
