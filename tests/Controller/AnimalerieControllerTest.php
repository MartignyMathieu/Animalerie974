<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnimalerieControllerTest extends WebTestCase
{
    public function testAccueil(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Le bonheur de vos animaux');
    }

    public function testCatalogueComplet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tous les produits');
    }

    public function testAccueilAfficheUniversEtAnimauxAvecPhotos(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(7, '.univers-carte');
        self::assertSelectorCount(10, '.animal');
        self::assertSelectorExists('.univers-carte img');
        self::assertSelectorExists('.actions svg'); // icônes SVG inline
    }

    public function testCatalogueUniversInclutSesAnimaux(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/nac');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.banniere h1', 'NAC');
        self::assertSelectorExists('.puces');
        // Produits de l'univers NAC et de ses sous-catégories (lapins, hamsters, cochons d'Inde).
        self::assertGreaterThanOrEqual(4, $client->getCrawler()->filter('.carte')->count());
    }

    public function testCatalogueAnimalFiltreSesProduits(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/canaris');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, '.carte');
        self::assertSelectorTextContains('.carte__nom', 'canari');
    }

    public function testCatalogueUniversInconnu(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/dragons');

        self::assertResponseStatusCodeSame(404);
    }
}
