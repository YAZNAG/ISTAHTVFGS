<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'responsable' => ['required', 'string', 'max:255'],
            'effectif' => ['nullable', 'integer', 'min:1'],
            'effectif_petit_dejeuner' => ['required', 'integer', 'min:1'],
            'effectif_dejeuner' => ['required', 'integer', 'min:1'],
            'effectif_diner' => ['required', 'integer', 'min:1'],

            'menus.petit_dejeuner.entree' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.petit_dejeuner.plat' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.petit_dejeuner.dessert' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.petit_dejeuner.plat_special' => ['nullable', 'integer', 'exists:fiches_techniques,id'],

            'menus.dejeuner.entree' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.dejeuner.plat' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.dejeuner.dessert' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.dejeuner.plat_special' => ['nullable', 'integer', 'exists:fiches_techniques,id'],

            'menus.diner.entree' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.diner.plat' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.diner.dessert' => ['required', 'integer', 'exists:fiches_techniques,id'],
            'menus.diner.plat_special' => ['nullable', 'integer', 'exists:fiches_techniques,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menus.petit_dejeuner.entree'  => 'entrée du petit-déjeuner',
            'menus.petit_dejeuner.plat'    => 'plat du petit-déjeuner',
            'menus.petit_dejeuner.dessert' => 'dessert du petit-déjeuner',
            'menus.petit_dejeuner.plat_special' => 'plat spécial du petit-déjeuner',

            'menus.dejeuner.entree'  => 'entrée du déjeuner',
            'menus.dejeuner.plat'    => 'plat du déjeuner',
            'menus.dejeuner.dessert' => 'dessert du déjeuner',
            'menus.dejeuner.plat_special' => 'plat spécial du déjeuner',

            'menus.diner.entree'  => 'entrée du dîner',
            'menus.diner.plat'    => 'plat du dîner',
            'menus.diner.dessert' => 'dessert du dîner',
            'menus.diner.plat_special' => 'plat spécial du dîner',
        ];
    }
}
