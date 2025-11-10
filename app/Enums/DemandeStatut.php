<?php

namespace App\Enums;

enum DemandeStatut: string
{

    # cree ─┬─> en_attente_livraison ─┬─> livree
    #       │                                      
    #       └─> annulee                            
    
    case CREE = 'cree';
    case ANNULEE = 'annulee';
    case REJETEE = 'rejetee';
    case EN_ATTENTE_VALIDATION = 'en_attente_validation';
    case VALIDEE = 'validee';

    public function label(): string
    {
        return match ($this) {
            self::CREE => 'Crée',
            self::EN_ATTENTE_VALIDATION => 'en attente de validation',
            self::VALIDEE => 'Validee',
            self::REJETEE => 'Rejetee',
            self::ANNULEE => 'Annulée',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}