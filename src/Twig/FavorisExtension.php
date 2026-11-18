<?php

namespace App\Twig;

use App\Entity\Produit;
use App\Service\FavorisService;
use Twig\Attribute\AsTwigFunction;

final class FavorisExtension
{
    public function __construct(private readonly FavorisService $favoris)
    {
    }

    /** Le produit est-il dans les favoris du visiteur ? (cœur plein ou vide sur les cartes) */
    #[AsTwigFunction('est_favori')]
    public function estFavori(Produit $produit): bool
    {
        return $this->favoris->contient($produit);
    }

    /** Nombre de favoris (badge de l'en-tête). */
    #[AsTwigFunction('favoris_nombre')]
    public function nombre(): int
    {
        return $this->favoris->nombre();
    }
}
