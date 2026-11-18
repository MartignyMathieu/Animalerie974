<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\FavorisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favoris')]
final class FavorisController extends AbstractController
{
    use RetourPrecedenteTrait;

    #[Route('', name: 'app_favoris', methods: ['GET'])]
    public function index(FavorisService $favoris): Response
    {
        return $this->render('animalerie/favoris.html.twig', ['produits' => $favoris->produits()]);
    }

    #[Route('/basculer/{id}', name: 'app_favori_basculer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function basculer(Produit $produit, Request $request, FavorisService $favoris): Response
    {
        if ($this->isCsrfTokenValid('favoris', $request->request->getString('_token'))) {
            $favoris->basculer($produit);
        }

        return $this->retour($request, 'app_favoris');
    }
}
