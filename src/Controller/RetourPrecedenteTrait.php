<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/** Après une action (panier, favori), revient sur la page d'où le visiteur est parti. */
trait RetourPrecedenteTrait
{
    private function retour(Request $request, string $routeParDefaut): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        if ('' !== $referer && str_starts_with($referer, $request->getSchemeAndHttpHost().'/')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute($routeParDefaut);
    }
}
