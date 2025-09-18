<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class IndexProfilesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // For demo purposes
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100'
            ],
            'sort' => [
                'sometimes',
                'string',
                'in:username,name,likes_count,followers_count,last_scraped_at,created_at'
            ],
            'order' => [
                'sometimes',
                'string',
                'in:asc,desc'
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be a valid number.',
            'page.min' => 'Page must be at least 1.',
            'limit.integer' => 'Limit must be a valid number.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'sort.in' => 'Sort field must be one of: username, name, likes_count, followers_count, last_scraped_at, created_at.',
            'order.in' => 'Order must be either asc or desc.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit' => (int) ($this->limit ?? 20),
            'sort' => strtolower($this->sort ?? 'created_at'),
            'order' => strtolower($this->order ?? 'desc'),
        ]);
    }

    /**
     * Get the validated and prepared data.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        
        return [
            'page' => $validated['page'] ?? 1,
            'limit' => $validated['limit'] ?? 20,
            'sort' => $validated['sort'] ?? 'created_at',
            'order' => $validated['order'] ?? 'desc',
        ];
    }
}