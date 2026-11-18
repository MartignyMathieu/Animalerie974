<?php

namespace App\Controller;

use App\Entity\MessageContact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AideController extends AbstractController
{
    /** @var list<array{question: string, reponse: string}> */
    private const FAQ = [
        ['question' => 'Livrez-vous partout à La Réunion ?', 'reponse' => "Oui, nous livrons sur toute l'île. Les délais et frais de livraison sont indiqués avant la validation de votre commande."],
        ['question' => 'Quels moyens de paiement acceptez-vous ?', 'reponse' => 'Carte bancaire, virement, et paiement en plusieurs fois selon le montant de votre panier. Les paiements sont sécurisés.'],
        ['question' => 'Puis-je retourner un produit ?', 'reponse' => 'Vous disposez de 14 jours après réception pour retourner un produit non ouvert. Les denrées alimentaires entamées ne sont pas reprises.'],
        ['question' => "Comment choisir l'alimentation de mon animal ?", 'reponse' => 'Cela dépend de son espèce, de son âge et de sa taille. Écrivez-nous via le formulaire ci-dessous : nos conseillers vous répondront.'],
        ['question' => 'Comment modifier mon panier ?', 'reponse' => 'Depuis la page Panier, vous pouvez changer les quantités ou retirer un article. Votre panier est conservé si vous créez un compte.'],
    ];

    #[Route('/aide', name: 'app_aide', methods: ['GET', 'POST'])]
    public function aide(Request $request, EntityManagerInterface $em): Response
    {
        $message = new MessageContact();
        $form = $this->createForm(ContactType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();
            $this->addFlash('succes', 'Merci ! Votre message a bien été envoyé, nous vous répondrons rapidement.');

            return $this->redirectToRoute('app_aide', ['_fragment' => 'contact']);
        }

        return $this->render('animalerie/aide.html.twig', [
            'faq' => self::FAQ,
            'form' => $form,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}
