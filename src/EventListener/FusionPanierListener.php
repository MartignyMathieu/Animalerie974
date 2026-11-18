<?php

namespace App\EventListener;

use App\Entity\Utilisateur;
use App\Service\PanierService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/** Récupère le panier du visiteur anonyme quand il se connecte ou crée son compte. */
#[AsEventListener]
final class FusionPanierListener
{
    public function __construct(private readonly PanierService $panier)
    {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $utilisateur = $event->getUser();
        if ($utilisateur instanceof Utilisateur) {
            $this->panier->fusionner($utilisateur);
        }
    }
}
