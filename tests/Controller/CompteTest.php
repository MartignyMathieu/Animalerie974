<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CompteTest extends WebTestCase
{
    private const MOT_DE_PASSE = 'MotDePasse-Test-2026';

    private function email(): string
    {
        return 'test-'.bin2hex(random_bytes(6)).'@example.test';
    }

    private function inscrire(KernelBrowser $client, string $email, string $nom = 'Marie Dupont', string $mdp = self::MOT_DE_PASSE): void
    {
        $crawler = $client->request('GET', '/inscription');
        $client->submit($crawler->selectButton('Créer mon compte')->form([
            'inscription[nom]' => $nom,
            'inscription[email]' => $email,
            'inscription[plainPassword][first]' => $mdp,
            'inscription[plainPassword][second]' => $mdp,
        ]));
    }

    private function connecter(KernelBrowser $client, string $email, string $mdp = self::MOT_DE_PASSE): void
    {
        $crawler = $client->request('GET', '/connexion');
        $client->submit($crawler->selectButton('Se connecter')->form(['_username' => $email, '_password' => $mdp]));
    }

    private function ajouterProduit(KernelBrowser $client, int $index = 0): void
    {
        $crawler = $client->request('GET', '/catalogue');
        $client->submit($crawler->filter('form[action*="/panier/ajouter/"]')->eq($index)->selectButton('Ajouter au panier')->form());
    }

    public function testCompteExigeUneConnexion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compte');

        self::assertResponseRedirects('/connexion');
    }

    public function testInscriptionConnecteAutomatiquement(): void
    {
        $client = static::createClient();
        $email = $this->email();
        $this->inscrire($client, $email);

        self::assertResponseRedirects('/compte');
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Bonjour Marie');
        self::assertSelectorTextContains('.compte', $email);
    }

    public function testInscriptionRefuseUnMotDePasseTropCourt(): void
    {
        $client = static::createClient();
        $this->inscrire($client, $this->email(), 'Marie', 'court');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.formulaire', 'au moins 8 caractères');
    }

    public function testInscriptionRefuseUnEmailDejaUtilise(): void
    {
        $client = static::createClient();
        $email = $this->email();
        $this->inscrire($client, $email);

        $client->request('GET', '/deconnexion');
        $this->inscrire($client, $email);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.formulaire', 'existe déjà');
    }

    public function testConnexionEtDeconnexion(): void
    {
        $client = static::createClient();
        $email = $this->email();
        $this->inscrire($client, $email);
        $client->request('GET', '/deconnexion');
        $client->request('GET', '/compte');
        self::assertResponseRedirects('/connexion');

        $this->connecter($client, $email);
        self::assertResponseRedirects('/compte');
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Bonjour Marie');
    }

    public function testConnexionAvecMauvaisMotDePasse(): void
    {
        $client = static::createClient();
        $email = $this->email();
        $this->inscrire($client, $email);
        $client->request('GET', '/deconnexion');

        $this->connecter($client, $email, 'mauvais-mot-de-passe');
        $client->followRedirect();

        self::assertSelectorExists('.flash--erreur');
    }

    public function testLePanierDuVisiteurSuitLInscription(): void
    {
        $client = static::createClient();
        $this->ajouterProduit($client);
        $this->inscrire($client, $this->email());

        $client->request('GET', '/panier');
        self::assertSelectorCount(1, '.panier__ligne');
    }

    public function testLePanierAnonymeEstFusionneALaConnexion(): void
    {
        $client = static::createClient();
        $email = $this->email();

        // Compte avec un produit A dans son panier.
        $this->ajouterProduit($client, 0);
        $this->inscrire($client, $email);
        $client->request('GET', '/deconnexion');

        // Visiteur anonyme : produit B, puis connexion.
        $this->ajouterProduit($client, 1);
        $this->connecter($client, $email);

        $client->request('GET', '/panier');
        self::assertSelectorCount(2, '.panier__ligne');
        self::assertSelectorTextContains('.actions__badge', '2');
    }
}
