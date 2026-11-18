<?php

namespace App\Service;

use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Panier du visiteur : lié à son compte s'il est connecté, sinon à un jeton stocké en session.
 */
final class PanierService
{
    private const SESSION_TOKEN = 'panier_token';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PanierRepository $paniers,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    /** Panier courant, ou null s'il n'existe pas encore (rien n'est créé). */
    public function courant(): ?Panier
    {
        $utilisateur = $this->security->getUser();
        if ($utilisateur instanceof Utilisateur) {
            return $this->paniers->findOneBy(['utilisateur' => $utilisateur]);
        }

        return $this->panierAnonyme();
    }

    public function nombreArticles(): int
    {
        return $this->courant()?->getNbArticles() ?? 0;
    }

    /** Ajoute au panier (limité au stock). Retourne la quantité réellement ajoutée. */
    public function ajouter(Produit $produit, int $quantite = 1): int
    {
        $panier = $this->obtenir();
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

    /** À la connexion : rattache le panier anonyme au compte, ou le fusionne avec celui du compte. */
    public function fusionner(Utilisateur $utilisateur): void
    {
        $anonyme = $this->panierAnonyme();
        if (null === $anonyme) {
            return;
        }

        $existant = $this->paniers->findOneBy(['utilisateur' => $utilisateur]);
        if (null === $existant) {
            $anonyme->setUtilisateur($utilisateur)->touch();
        } else {
            foreach ($anonyme->getLignes() as $ligne) {
                $produit = $ligne->getProduit();
                $cible = $this->ligne($existant, $produit);
                if (null === $cible) {
                    $cible = (new LignePanier())->setProduit($produit)->setQuantite(0);
                    $existant->addLigne($cible);
                }
                $cible->setQuantite(min($cible->getQuantite() + $ligne->getQuantite(), max(1, $produit->getStock())));
            }
            $existant->touch();
            $this->em->remove($anonyme);
        }
        $this->em->flush();
        $this->requestStack->getSession()->remove(self::SESSION_TOKEN);
    }

    private function obtenir(): Panier
    {
        $panier = $this->courant();
        if (null !== $panier) {
            return $panier;
        }

        $panier = new Panier();
        $utilisateur = $this->security->getUser();
        if ($utilisateur instanceof Utilisateur) {
            $panier->setUtilisateur($utilisateur);
        } else {
            $this->requestStack->getSession()->set(self::SESSION_TOKEN, $panier->getToken());
        }
        $this->em->persist($panier);

        return $panier;
    }

    private function panierAnonyme(): ?Panier
    {
        $token = $this->requestStack->getSession()->get(self::SESSION_TOKEN);

        return \is_string($token) ? $this->paniers->findOneBy(['token' => $token, 'utilisateur' => null]) : null;
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
