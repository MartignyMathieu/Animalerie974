<?php

namespace App\Tests\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class FavorisTest extends WebTestCase
{
    /** Clique sur le cœur du premier produit de l'accueil. */
    private function basculerPremierProduit(KernelBrowser $client): Crawler
    {
        $crawler = $client->request('GET', '/');
        $client->submit($crawler->filter('form[action*="/favoris/basculer/"]')->first()->form());

        return $client->followRedirect();
    }

    public function testAucunFavoriAuDepart(): void
    {
        $client = static::createClient();
        $client->request('GET', '/favoris');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.vide--panier', "pas encore de favoris");
        self::assertSelectorNotExists('a[href="/favoris"] .actions__badge');
    }

    public function testAjouterUnFavori(): void
    {
        $client = static::createClient();
        $crawler = $this->basculerPremierProduit($client);

        // Retour sur l'accueil, avec le cœur plein et le badge de l'en-tête à 1.
        self::assertSame('/', parse_url($client->getRequest()->getUri(), \PHP_URL_PATH));
        self::assertSelectorExists('.carte__coeur.is-actif[aria-pressed="true"]');
        self::assertSame('1', trim($crawler->filter('a[href="/favoris"] .actions__badge')->text()));

        $client->request('GET', '/favoris');
        self::assertSelectorCount(1, '.carte');
    }

    public function testRetirerUnFavori(): void
    {
        $client = static::createClient();
        $this->basculerPremierProduit($client);

        // Depuis la page Favoris, un second clic sur le cœur retire le produit.
        $crawler = $client->request('GET', '/favoris');
        $client->submit($crawler->filter('form[action*="/favoris/basculer/"]')->first()->form());
        $client->followRedirect();

        self::assertSelectorExists('.vide--panier');
    }

    public function testFavoriSansJetonCsrfEstRefuse(): void
    {
        $client = static::createClient();
        $produit = static::getContainer()->get(ProduitRepository::class)->findOneBy(['slug' => 'gamelle-inox']);

        $client->request('POST', '/favoris/basculer/'.$produit->getId());
        $client->followRedirect();

        self::assertSelectorExists('.vide--panier');
    }

    public function testLesFavorisNeSontPasLeMemePanier(): void
    {
        $client = static::createClient();
        $this->basculerPremierProduit($client);

        $client->request('GET', '/panier');
        self::assertSelectorExists('.vide--panier');
    }
}
