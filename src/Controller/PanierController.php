<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class PanierController extends AbstractController
{
    use RetourPrecedenteTrait;

    #[Route('', name: 'app_panier', methods: ['GET'])]
    public function index(PanierService $panier): Response
    {
        return $this->render('animalerie/panier.html.twig', ['panier' => $panier->courant()]);
    }

    #[Route('/ajouter/{id}', name: 'app_panier_ajouter', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function ajouter(Produit $produit, Request $request, PanierService $panier): Response
    {
        if (!$this->jetonValide($request)) {
            return $this->retour($request, 'app_panier');
        }

        if ($panier->ajouter($produit, max(1, $request->request->getInt('quantite', 1))) > 0) {
            $this->addFlash('succes', \sprintf('« %s » a été ajouté à votre panier.', $produit->getNom()));
        } else {
            $this->addFlash('erreur', \sprintf('« %s » n\'est plus disponible en quantité suffisante.', $produit->getNom()));
        }

        return $this->retour($request, 'app_panier');
    }

    #[Route('/modifier/{id}', name: 'app_panier_modifier', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function modifier(Produit $produit, Request $request, PanierService $panier): Response
    {
        if ($this->jetonValide($request)) {
            $panier->modifier($produit, $request->request->getInt('quantite'));
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/retirer/{id}', name: 'app_panier_retirer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function retirer(Produit $produit, Request $request, PanierService $panier): Response
    {
        if ($this->jetonValide($request)) {
            $panier->retirer($produit);
            $this->addFlash('succes', \sprintf('« %s » a été retiré de votre panier.', $produit->getNom()));
        }

        return $this->redirectToRoute('app_panier');
    }

    private function jetonValide(Request $request): bool
    {
        if ($this->isCsrfTokenValid('panier', $request->request->getString('_token'))) {
            return true;
        }
        $this->addFlash('erreur', 'Votre session a expiré, veuillez réessayer.');

        return false;
    }
}
