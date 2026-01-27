<?php

namespace App\Enums;

enum FicheType : string
{
    case COLLECTIVITE = 'collectivite';
    case PEDAGOGIQUE = 'pedagogique';
    case RESTAURANT = 'restaurant';

    public function label(): string
    {
        return match ($this) {
            self::COLLECTIVITE => 'Collectivité',
            self::PEDAGOGIQUE => 'Pédagogique',
            // self::RESTAURANT => 'Restaurant',
        };
    }

    public static function toSelect(): array
    {
        return [
            [
                'value' => self::COLLECTIVITE->value,
                'label' => self::COLLECTIVITE->label(),
            ],
            [
                'value' => self::PEDAGOGIQUE->value,
                'label' => self::PEDAGOGIQUE->label(),
            ],
            // [
            //     'value' => self::RESTAURANT->value,
            //     'label' => self::RESTAURANT->label(),
            // ],
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
