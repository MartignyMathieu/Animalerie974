<?php

namespace App\Service;

use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Panier du visiteur, retrouvé grâce à un jeton gardé dans sa session (cookie de 30 jours).
 */
final class PanierService
{
    private const SESSION_TOKEN = 'panier_token';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PanierRepository $paniers,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** Panier du visiteur, ou null s'il n'existe pas encore (rien n'est créé). */
    public function courant(): ?Panier
    {
        $token = $this->requestStack->getSession()->get(self::SESSION_TOKEN);

        return \is_string($token) ? $this->paniers->findOneBy(['token' => $token]) : null;
    }

    public function nombreArticles(): int
    {
        return $this->courant()?->getNbArticles() ?? 0;
    }

    /** Ajoute au panier (limité au stock). Retourne la quantité réellement ajoutée. */
    public function ajouter(Produit $produit, int $quantite = 1): int
    {
        $panier = $this->courant() ?? $this->creer();
        $ligne = $this->ligne($panier, $produit);
        $avant = $ligne?->getQuantite() ?? 0;
        $apres = min($avant + max(1, $quantite), $produit->getStock());
        if ($apres <= $avant) {
            return 0;
        }

        if (null === $ligne) {
            $ligne = (new LignePanier())->setProduit($produit);
            $panier->addLigne($ligne);
        }
        $ligne->setQuantite($apres);
        $panier->touch();
        $this->em->flush();

        return $apres - $avant;
    }

    /** Fixe la quantité d'un produit (0 pour le retirer), limitée au stock. */
    public function modifier(Produit $produit, int $quantite): void
    {
        $panier = $this->courant();
        $ligne = $panier ? $this->ligne($panier, $produit) : null;
        if (null === $panier || null === $ligne) {
            return;
        }

        $quantite = min($quantite, $produit->getStock());
        if ($quantite <= 0) {
            $panier->removeLigne($ligne);
        } else {
            $ligne->setQuantite($quantite);
        }
        $panier->touch();
        $this->em->flush();
    }

    public function retirer(Produit $produit): void
    {
        $this->modifier($produit, 0);
    }

    private function creer(): Panier
    {
        $panier = new Panier();
        $this->requestStack->getSession()->set(self::SESSION_TOKEN, $panier->getToken());
        $this->em->persist($panier);

        return $panier;
    }

    private function ligne(Panier $panier, Produit $produit): ?LignePanier
    {
        foreach ($panier->getLignes() as $ligne) {
            if ($ligne->getProduit()?->getId() === $produit->getId()) {
                return $ligne;
            }
        }

        return null;
    }
}
