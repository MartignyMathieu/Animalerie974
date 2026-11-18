<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Produits favoris du visiteur : leurs identifiants sont gardés dans sa session (cookie de 30 jours).
 */
final class FavorisService
{
    private const SESSION_CLE = 'favoris';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProduitRepository $produits,
    ) {
    }

    /** @return list<int> */
    public function ids(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_CLE, []);
    }

    public function contient(Produit $produit): bool
    {
        return \in_array($produit->getId(), $this->ids(), true);
    }

    public function nombre(): int
    {
        return \count($this->ids());
    }

    /** Ajoute le produit aux favoris, ou l'en retire s'il y est déjà. Retourne true s'il est désormais favori. */
    public function basculer(Produit $produit): bool
    {
        $ids = $this->ids();

        if ($this->contient($produit)) {
            $ids = array_values(array_diff($ids, [$produit->getId()]));
            $favori = false;
        } else {
            $ids[] = $produit->getId();
            $favori = true;
        }

        $this->requestStack->getSession()->set(self::SESSION_CLE, $ids);

        return $favori;
    }

    /** @return Produit[] */
    public function produits(): array
    {
        $ids = $this->ids();

        return [] === $ids ? [] : $this->produits->findBy(['id' => $ids], ['nom' => 'ASC']);
    }
}
