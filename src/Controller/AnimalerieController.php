<?php

namespace App\Controller;

use App\Enum\TypeProduit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class AnimalerieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function accueil(CategorieRepository $categorieRepository, ProduitRepository $produitRepository): Response
    {
        return $this->render('animalerie/accueil.html.twig', [
            'univers' => $categorieRepository->findBy(['parent' => null], ['id' => 'ASC']),
            'animaux' => $categorieRepository->findSousCategories(),
            'meilleuresVentes' => $produitRepository->vedettes(),
        ]);
    }

    #[Route('/catalogue/{slug}', name: 'app_catalogue', defaults: ['slug' => null])]
    public function catalogue(
        CategorieRepository $categorieRepository,
        ProduitRepository $produitRepository,
        ?string $slug = null,
        #[MapQueryParameter] ?string $q = null,
        #[MapQueryParameter] ?TypeProduit $type = null,
    ): Response {
        $categorie = null;
        if (null !== $slug) {
            $categorie = $categorieRepository->findOneBy(['slug' => $slug])
                ?? throw $this->createNotFoundException('Univers introuvable.');
        }

        return $this->render('animalerie/catalogue.html.twig', [
            'categorie' => $categorie,
            // Sous-catégories de l'univers courant (ou frères si on est déjà sur un animal).
            'animaux' => $categorie ? $categorieRepository->findBy(['parent' => $categorie->getUnivers()], ['id' => 'ASC']) : [],
            'recherche' => $q,
            'type' => $type,
            'types' => TypeProduit::cases(),
            'produits' => $produitRepository->rechercher($categorie, $q, $type),
        ]);
    }
}
