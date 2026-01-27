<?php

namespace App\Enums;

enum Meal : string
{
    case PetitDejeuner = 'petit-dejeuner';
    case Dejeuner      = 'dejeuner';
    case Diner         = 'diner';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
