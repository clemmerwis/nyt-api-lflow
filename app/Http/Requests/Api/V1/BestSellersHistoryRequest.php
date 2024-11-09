<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BestSellersHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author' => 'sometimes|string',
            'isbn' => 'sometimes|string|regex:/^(\d{10}|\d{13})(;\d{10}|\d{13})*$/',
            'title' => 'sometimes|string',
            'offset' => 'sometimes|integer|multiple_of:20|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.regex' => 'ISBN must be a 10 or 13 digit number, or multiple ISBNs separated by semicolons'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        if ($this->has('isbn') && is_array($this->isbn)) {
            // If isbn comes in as array convert it to semicolon-separated string
            $this->merge([
                'isbn' => implode(';', $this->isbn)
            ]);
        }
    }
}
