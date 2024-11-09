<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BestSellersHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function passedValidation(): void
    {
        if ($this->has('isbn')) {
            $isbnInput = $this->input('isbn');

            // Convert array to semicolon-separated string
            $isbnString = implode(';', array_map('trim', $isbnInput));

            // Merge the semicolon-separated string back into the request data
            $this->merge([
                'isbn' => $isbnString,
            ]);
        }

        Log::info('After Validation', [
            'isbn' => $this->input('isbn'),
            'isbnType' => gettype($this->input('isbn')),
            'hasIsbn' => $this->has('isbn'),
        ]);
    }

    public function rules(): array
    {
        return [
            'author' => 'sometimes|string',
            'isbn' => 'sometimes|array',
            'isbn.*' => [
                'required',
                'string',
                'regex:/^\d{10}(\d{3})?$/',
            ],
            'title' => 'sometimes|string',
            'offset' => 'sometimes|integer|multiple_of:20|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.*.required' => 'Each ISBN present cannot be empty.',
            'isbn.*.regex' => 'Each ISBN must be exactly 10 or 13 digits.',
        ];
    }
}
