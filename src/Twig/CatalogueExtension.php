<?php

namespace App\Twig;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Twig\Attribute\AsTwigFunction;

/** Données du catalogue nécessaires à toutes les pages (navigation, pied de page). */
final class CatalogueExtension
{
    /** @var list<Categorie>|null */
    private ?array $univers = null;

    public function __construct(private readonly CategorieRepository $categories)
    {
    }

    /**
     * Univers racines (chiens, chats...) dans l'ordre d'affichage.
     *
     * @return list<Categorie>
     */
    #[AsTwigFunction('univers_racines')]
    public function racines(): array
    {
        return $this->univers ??= $this->categories->findBy(['parent' => null], ['id' => 'ASC']);
    }
}
