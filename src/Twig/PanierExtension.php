<?php

namespace App\Twig;

use App\Service\PanierService;
use Twig\Attribute\AsTwigFunction;

final class PanierExtension
{
    public function __construct(private readonly PanierService $panier)
    {
    }

    /** Nombre d'articles du panier courant (badge de l'en-tête). */
    #[AsTwigFunction('panier_nombre')]
    public function nombre(): int
    {
        return $this->panier->nombreArticles();
    }
}
