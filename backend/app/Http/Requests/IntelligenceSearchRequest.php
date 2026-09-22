<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntelligenceSearchRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * Reject queries that contain only whitespace (they carry no search terms).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $query = (string) $this->input('query');

            if (trim($query) === '') {
                $validator->errors()->add('query', 'The query must contain searchable text.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'query.required' => 'Please provide a query to search the intelligence repository.',
            'query.string' => 'The query must be text.',
            'query.max' => 'The query must not be longer than 500 characters.',
        ];
    }
}