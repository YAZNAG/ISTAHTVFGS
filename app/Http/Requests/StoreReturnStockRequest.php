<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReturnStockRequest extends FormRequest
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
            'date'                  => ['required', 'date'],
            'motif'                 => ['nullable', 'string'],
            'returner_id'           => ['required', 'exists:users,id'],
            'articles'              => ['required', 'array', 'min:1'],
            'articles.*.article_id' => ['required', 'integer', 'exists:articles,id'],
            'articles.*.quantite'   => ['required', 'integer', 'min:1'],
        ];
    }
}
