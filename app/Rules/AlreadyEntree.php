<?php

namespace App\Rules;

use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\MouvementStock;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AlreadyEntree implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $alreadyEntree = BonCommandeArticle::where('article_id', $value)->first();

        if (!$alreadyEntree) {
            $article = Article::withNonExists()->find($value);
            $articleName = $article ? $article->designation : 'Inconnu';
            $fail("L'article « {$articleName} » n'a jamais été enregistré dans un stock d'entrée.");
        }
    }
}
