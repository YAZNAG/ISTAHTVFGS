<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
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
            'nom'                  => ['required', 'string', 'max:255'],
            'effectif'             => ['required', 'integer', 'min:1'],
            'motif'                => ['nullable', 'string'],
            'articles'             => ['required', 'array', 'min:1'],
            'articles.*.article_id'=> ['required', 'integer', 'exists:articles,id'],
            'articles.*.quantite' => ['required', 'integer', 'min:1'],
        ];
    }
}
