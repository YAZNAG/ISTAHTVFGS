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
        // La fiche technique signee n'est requise que pour une demande PEDAGOGIQUE
        'fiche_technique' => [
            'nullable',
            Rule::requiredIf($this->demandable_type === 'pedagogique'),
            'mimes:pdf,doc,docx,png,jpg,jpeg',
            'max:5120',
        ],

        'motif'            => 'nullable|string|max:500',

        // cible polymorphique
        'demandable_type'  => 'required|in:collectivite,pedagogique,restaurant',
        'demandable_id'    => [
            'required',
            'integer',
            function ($attr, $val, $fail) {
                $type = request('demandable_type');
                $class = match ($type) {
                    'collectivite' => \App\Models\MenuCollectivite::class,
                    'restaurant'   => \App\Models\Restaurant::class,
                    default        => \App\Models\FicheTechnique::class,
                };

                if (! $class::find($val)) {
                    $fail("La cible ({$type}) selectionnee n'existe pas.");
                }
            },
        ],
    ];
    }
}
