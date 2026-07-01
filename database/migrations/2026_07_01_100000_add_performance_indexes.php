<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Articles: filter by est_actif + categorie_id (most common query pattern)
        Schema::table('articles', function (Blueprint $table) {
            $table->index(['est_actif', 'categorie_id'], 'articles_actif_categorie_idx');
            $table->index('quantite_stock', 'articles_quantite_stock_idx');
        });

        // Mouvement stocks: filter by article_id + date (inventory, cardex, entries/exits)
        Schema::table('mouvement_stocks', function (Blueprint $table) {
            $table->index(['article_id', 'date_mouvement'], 'mouvements_article_date_idx');
            $table->index(['type', 'date_mouvement'], 'mouvements_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('articles_actif_categorie_idx');
            $table->dropIndex('articles_quantite_stock_idx');
        });

        Schema::table('mouvement_stocks', function (Blueprint $table) {
            $table->dropIndex('mouvements_article_date_idx');
            $table->dropIndex('mouvements_type_date_idx');
        });
    }
};
