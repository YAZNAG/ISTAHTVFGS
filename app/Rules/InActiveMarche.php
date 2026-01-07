<?php

namespace App\Rules;

use App\Models\Article;
use App\Models\BonCommandeArticle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InActiveMarche implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = BonCommandeArticle::where('article_id', $value)
            ->whereHas('bonCommande', fn ($q) => $q
                ->whereDate('date_debut', '<=', today())
                ->whereDate('date_fin',   '>=', today())
            )
            ->exists();

        if (! $exists) {
            $articleName = Article::where('id', $value)->value('designation');
            $fail('L’article « ' . $articleName . ' » n’est pas présent dans un bon de commande actif');
        }
    }
}
