<?php

namespace App\Tests\Controller;

use App\Repository\MessageContactRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AideTest extends WebTestCase
{
    public function testPageAideAfficheLaFaqEtLeFormulaire(): void
    {
        $client = static::createClient();
        $client->request('GET', '/aide');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(5, 'details.faq');
        self::assertSelectorExists('#contact form');
    }

    public function testEnvoiDUnMessageDeContact(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/aide');
        $sujet = 'Question '.bin2hex(random_bytes(4));

        $client->submit($crawler->selectButton('Envoyer mon message')->form([
            'contact[nom]' => 'Jean Test',
            'contact[email]' => 'jean@example.test',
            'contact[sujet]' => $sujet,
            'contact[message]' => 'Bonjour, quelle nourriture pour un chiot ?',
        ]));

        self::assertResponseRedirects('/aide#contact');
        $client->followRedirect();
        self::assertSelectorTextContains('.flash--succes', 'message a bien été envoyé');

        $enregistre = static::getContainer()->get(MessageContactRepository::class)->findOneBy(['sujet' => $sujet]);
        self::assertNotNull($enregistre);
        self::assertSame('jean@example.test', $enregistre->getEmail());
    }

    public function testMessageInvalideEstRefuse(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/aide');

        $client->submit($crawler->selectButton('Envoyer mon message')->form([
            'contact[nom]' => '',
            'contact[email]' => 'pas-un-email',
            'contact[sujet]' => 'Sujet',
            'contact[message]' => 'court',
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('#contact', 'Indiquez votre nom');
        self::assertSelectorTextContains('#contact', 'trop court');
    }
}
