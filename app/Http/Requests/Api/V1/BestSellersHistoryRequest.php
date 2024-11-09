<?php

namespace App\Http\Requests\Api\V1;

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
            // Format: single ISBN or semicolon-separated ISBNs
            'isbn' => 'sometimes|string|regex:/^(\d{10}|\d{13})(;\d{10}|\d{13})*$/',
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
        // If ISBN comes as an array, convert it to a semicolon-separated string
        if ($this->has('isbn') && is_array($this->isbn)) {
            $this->merge([
                'isbn' => implode(';', $this->isbn)
            ]);
        }
    }
}
