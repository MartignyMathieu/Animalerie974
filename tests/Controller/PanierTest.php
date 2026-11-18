<?php

namespace App\Tests\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PanierTest extends WebTestCase
{
    /** Clique sur « Ajouter au panier » du premier produit de la page d'accueil. */
    private function ajouterPremierProduit(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/');
        $client->submit($crawler->filter('form[action*="/panier/ajouter/"]')->first()->selectButton('Ajouter au panier')->form());
    }

    public function testPanierVideAuDepart(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.vide--panier', 'Votre panier est vide');
        self::assertSelectorTextContains('.actions__badge', '0');
    }

    public function testAjouterAuPanier(): void
    {
        $client = static::createClient();
        $this->ajouterPremierProduit($client);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorTextContains('.flash--succes', 'ajouté à votre panier');
        self::assertSelectorTextContains('.actions__badge', '1');

        $client->request('GET', '/panier');
        self::assertSelectorCount(1, '.panier__ligne');
        self::assertSelectorTextContains('.panier__total', '€');
    }

    public function testAjouterDeuxFoisIncrementeLaQuantite(): void
    {
        $client = static::createClient();
        $this->ajouterPremierProduit($client);
        $this->ajouterPremierProduit($client);

        $crawler = $client->request('GET', '/panier');
        self::assertSelectorCount(1, '.panier__ligne');
        self::assertSame('2', $crawler->filter('.panier__quantite input[name="quantite"]')->attr('value'));
        self::assertSelectorTextContains('.actions__badge', '2');
    }

    public function testModifierLaQuantiteEstLimiteeAuStock(): void
    {
        $client = static::createClient();
        $this->ajouterPremierProduit($client);

        $crawler = $client->request('GET', '/panier');
        $stock = (int) $crawler->filter('.panier__quantite input[name="quantite"]')->attr('max');
        $form = $crawler->filter('.panier__quantite')->first()->selectButton('Mettre à jour')->form(['quantite' => 9999]);
        $client->submit($form);
        $crawler = $client->followRedirect();

        self::assertSame((string) $stock, $crawler->filter('.panier__quantite input[name="quantite"]')->attr('value'));
    }

    public function testQuantiteZeroRetireLaLigne(): void
    {
        $client = static::createClient();
        $this->ajouterPremierProduit($client);

        $crawler = $client->request('GET', '/panier');
        $client->submit($crawler->filter('.panier__quantite')->first()->selectButton('Mettre à jour')->form(['quantite' => 0]));
        $client->followRedirect();

        self::assertSelectorExists('.vide--panier');
    }

    public function testRetirerUnProduit(): void
    {
        $client = static::createClient();
        $this->ajouterPremierProduit($client);

        $crawler = $client->request('GET', '/panier');
        $client->submit($crawler->filter('form[action*="/panier/retirer/"]')->first()->form());
        $client->followRedirect();

        self::assertSelectorTextContains('.flash--succes', 'retiré de votre panier');
        self::assertSelectorExists('.vide--panier');
        self::assertSelectorTextContains('.actions__badge', '0');
    }

    public function testAjoutSansJetonCsrfEstRefuse(): void
    {
        $client = static::createClient();
        $produit = static::getContainer()->get(ProduitRepository::class)->findOneBy(['slug' => 'gamelle-inox']);

        $client->request('POST', '/panier/ajouter/'.$produit->getId());
        $client->followRedirect();

        self::assertSelectorTextContains('.flash--erreur', 'session a expiré');
        self::assertSelectorTextContains('.actions__badge', '0');
    }

    public function testProduitEnRuptureDeStockNePeutPasEtreAjoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue/chats');

        self::assertSelectorExists('button[disabled]');
    }
}
