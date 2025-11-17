<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeRequest extends FormRequest
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
        'demandeur'        => ['nullable', Rule::requiredIf(fn () => auth()->user()->isAdmin()), 'integer', 'exists:users,id'],
        'fiche_technique' => [
            'nullable',
            Rule::requiredIf($this->demandable_type !== 'restaurant'),
            'mimes:pdf,doc,docx,png,jpg,jpeg',
            'max:5120',
        ],

        'motif'            => 'nullable|string|max:500',

        // polymorphic target
        'demandable_type'  => 'required|in:collectivite,pedagogique,restaurant', // extend when you add more
        'demandable_id'    => [
            'required',
            'integer',
            function ($attr, $val, $fail) {
                $type = request('demandable_type');
                $class = $type === 'restaurant'
                    ? \App\Models\Restaurant::class
                    : \App\Models\FicheTechnique::class;

                if (! $class::find($val)) {
                    $fail("The chosen {$type} does not exist.");
                }
            },
        ],
    ];
    }
}
