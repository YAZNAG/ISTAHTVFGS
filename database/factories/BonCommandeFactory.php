<?php

namespace Database\Factories;

use App\Models\BonCommande;
use App\Models\Categorie;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BonCommande>
 */
class BonCommandeFactory extends Factory
{
    protected $model = BonCommande::class;

    public function definition(): array
    {
        $date = Carbon::parse($this->faker->date());
        return [
            'reference' => 'BC-' . $this->faker->unique()->numerify('####'),
            'objet' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'categorie_id' => Categorie::query()->inRandomOrder()->value('id'),
            'fournisseur_id' => Fournisseur::inRandomOrder()->first()->id,
            'statut' => $this->faker->randomElement([
                BonCommande::STATUT_CREE,
                BonCommande::STATUT_ATTENTE_LIVRAISON,
                BonCommande::STATUT_LIVRE_PARTIELLEMENT,
            ]),
            'date_mise_ligne' => $date,
            'date_limite_reception' => $date->addMonth(1),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::inRandomOrder()->first()->id,
            'pieces_jointes' => [],
        ];
    }

    /**
     * Indique que la commande est complètement livrée.
     */
    public function livreCompletement(): static
    {
        return $this->state(fn () => ['statut' => BonCommande::STATUT_LIVRE_COMPLETEMENT]);
    }

    /**
     * Indique que la commande est annulée.
     */
    public function annule(): static
    {
        return $this->state(fn () => ['statut' => BonCommande::STATUT_ANNULE]);
    }
}
