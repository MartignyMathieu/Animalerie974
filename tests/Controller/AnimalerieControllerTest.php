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
        // Les graines pour canari et le canari lui-même.
        self::assertSelectorCount(2, '.carte');
        self::assertSelectorTextContains('.carte__nom', 'Canari chanteur');
    }

    public function testCatalogueFiltreParType(): void
    {
        $client = static::createClient();

        $client->request('GET', '/catalogue?type=animal');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(11, '.carte');
        self::assertSelectorTextContains('h1', 'Animaux');

        $client->request('GET', '/catalogue?type=mode');
        self::assertSelectorCount(3, '.carte');
    }

    public function testFiltreParTypeDansUnUnivers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/nac?type=alimentation');

        self::assertResponseIsSuccessful();
        // Menu lapin, mélange cobaye : seule l'alimentation de l'univers NAC.
        self::assertSelectorCount(2, '.carte');
        self::assertSelectorNotExists('.carte__type:contains("Animaux")');
    }

    public function testTypeInconnuRenvoieUneErreur(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue?type=dragon');

        self::assertResponseStatusCodeSame(404);
    }

    public function testLePanierDureTrenteJours(): void
    {
        static::bootKernel();

        $options = static::getContainer()->getParameter('session.storage.options');
        self::assertSame(30 * 24 * 3600, $options['cookie_lifetime']);
    }

    public function testCatalogueUniversInconnu(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/dragons');

        self::assertResponseStatusCodeSame(404);
    }
}
