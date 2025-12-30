<?php

namespace App\Http\Requests;

use App\Enums\FicheType;
use App\Rules\AlreadyEntree;
use App\Rules\ExistsInStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFicheTechniqueRequest extends FormRequest
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
            'nom' => 'required|exists:repas,id',
            'type' => 'required|in:' . implode(',', FicheType::values()),
            'responsable' => 'required|string|max:255',
            'demandeur' => ['nullable', Rule::requiredIf(fn () => auth()->user()->isAdmin()), 'integer', 'exists:users,id'],
            'plat' => 'required|exists:plats,id',
            'effectif' => 'required|integer|min:0',
            'etapes' => 'required|array|min:1',
            'etapes.*.title' => 'required|string|max:255',
            'etapes.*.articles' => 'required|array|min:1',
            // 'etapes.*.articles.*.article_id' => 'required|integer|exists:articles,id',
            'etapes.*.articles.*.article_id' => ['required', 'integer', 'exists:articles,id', new AlreadyEntree],
            'etapes.*.articles.*.quantite' => 'required|numeric|min:1',
        ];
    }


    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

            'type.required' => 'Le type de fiche est obligatoire.',
            'type.enum' => 'Le type sélectionné est invalide.',

            'demandeur.required' => 'Le demandeur est obligatoire.',
            'demandeur.integer' => 'Le demandeur doit être un nombre entier.',
            'demandeur.exists' => 'Le demandeur selectionné est invalide ou introuvable.',

            'responsable.required' => 'Le responsable est obligatoire.',
            'responsable.string' => 'Le responsable doit être une chaîne de caractères.',
            'responsable.max' => 'Le responsable ne peut pas dépasser 255 caractères.',

            'plat.required' => 'Le plat est obligatoire.',
            'plat.string' => 'Le plat doit être une chaîne de caractères.',
            'plat.max' => 'Le plat ne peut pas dépasser 255 caractères.',

            'effectif.required' => 'L’effectif est obligatoire.',
            'effectif.integer' => 'L’effectif doit être un nombre entier.',
            'effectif.min' => 'L’effectif doit être supérieur ou égal à 0.',

            'etapes.required' => 'Vous devez ajouter au moins une étape.',
            'etapes.array' => 'Les étapes doivent être envoyées sous forme de liste.',
            'etapes.min' => 'Vous devez ajouter au moins une étape.',

            'etapes.*.title.required' => 'Le titre de l’étape est obligatoire.',
            'etapes.*.title.string' => 'Le titre de l’étape doit être une chaîne de caractères.',
            'etapes.*.title.max' => 'Le titre de l’étape ne peut pas dépasser 255 caractères.',

            'etapes.*.articles.required' => 'Chaque étape doit contenir au moins un article.',
            'etapes.*.articles.array' => 'Les articles d’une étape doivent être envoyés sous forme de liste.',
            'etapes.*.articles.min' => 'Chaque étape doit contenir au moins un article.',

            'etapes.*.articles.*.article_id.required' => 'L’article est obligatoire.',
            'etapes.*.articles.*.article_id.integer' => 'L’identifiant de l’article doit être un nombre entier.',
            'etapes.*.articles.*.article_id.exists' => 'L’article sélectionné est invalide ou introuvable.',

            'etapes.*.articles.*.quantite.required' => 'La quantité est obligatoire.',
            'etapes.*.articles.*.quantite.integer' => 'La quantité doit être un nombre entier.',
            'etapes.*.articles.*.quantite.min' => 'La quantité doit être au moins égale à 1.',
        ];

    }
}
