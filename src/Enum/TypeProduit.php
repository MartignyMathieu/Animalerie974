<?php

namespace App\Enum;

/** Ce que l'on peut acheter sur le site : des animaux, ou des produits pour animaux. */
enum TypeProduit: string
{
    case Animal = 'animal';
    case Alimentation = 'alimentation';
    case Loisirs = 'loisirs';
    case Mode = 'mode';

    public function libelle(): string
    {
        return match ($this) {
            self::Animal => 'Animaux',
            self::Alimentation => 'Alimentation',
            self::Loisirs => 'Loisirs et accessoires',
            self::Mode => 'Mode',
        };
    }
}
