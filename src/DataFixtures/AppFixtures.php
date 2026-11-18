<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /** slug => [nom, image] */
    private const UNIVERS = [
        'chiens' => ['Chiens', 'images/univers/chiens.jpg'],
        'chats' => ['Chats', 'images/univers/chats.jpg'],
        'nac' => ['NAC (lapins, rongeurs...)', 'images/univers/nac.jpg'],
        'oiseaux' => ['Oiseaux', 'images/univers/oiseaux.jpg'],
        'poissons' => ['Poissons', 'images/univers/poissons.jpg'],
        'reptiles' => ['Reptiles', 'images/univers/reptiles.jpg'],
        'equides' => ['Équidés', 'images/univers/equides.jpg'],
    ];

    /** slug, nom, univers parent, image */
    private const ANIMAUX = [
        ['lapins', 'Lapins', 'nac', 'images/animaux/lapin.jpg'],
        ['hamsters', 'Hamsters', 'nac', 'images/animaux/hamster.jpg'],
        ['cobayes', "Cochons d'Inde", 'nac', 'images/animaux/cochon-inde.jpg'],
        ['perruches', 'Perruches', 'oiseaux', 'images/animaux/perruche.jpg'],
        ['canaris', 'Canaris', 'oiseaux', 'images/animaux/canari.jpg'],
        ['poissons-rouges', 'Poissons rouges', 'poissons', 'images/animaux/poisson-rouge.jpg'],
        ['lezards', 'Lézards', 'reptiles', 'images/animaux/lezard.jpg'],
        ['serpents', 'Serpents', 'reptiles', 'images/animaux/serpent.jpg'],
        ['chevaux', 'Chevaux', 'equides', 'images/animaux/cheval.jpg'],
        ['poneys', 'Poneys', 'equides', 'images/animaux/poney.jpg'],
    ];

    /** slug, nom, catégorie, description, prix, promo, stock, image (optionnelle) */
    private const PRODUITS = [
        ['royal-canin-medium-adult', 'Royal Canin Medium Adult', 'chiens', 'Croquettes chien – 15 kg', '89.90', '79.90', 25, 'images/packshots/royal-canin.jpg'],
        ['arbre-a-chat-multi-niveaux', 'Arbre à chat multi-niveaux', 'chats', 'Confort & jeu', '59.90', null, 12, 'images/packshots/arbre-a-chat.jpg'],
        ['kit-aquarium-60l', 'Kit aquarium complet 60 L', 'poissons', 'Éclairage LED, filtre et accessoires', '119.90', null, 8, 'images/packshots/aquarium.jpg'],
        ['clapier-exterieur-bois', 'Clapier extérieur bois', 'lapins', 'Pour lapins et petits rongeurs', '149.90', null, 5, 'images/produits/lapin.jpg'],
        ['panier-confort-chien', 'Panier confort pour chien', 'chiens', 'Coussin déhoussable, lavable en machine', '44.90', null, 18, 'images/produits/chien-tapis.jpg'],
        ['laisse-reglable-nylon', 'Laisse réglable nylon', 'chiens', 'Longueur 1,2 à 2 m, mousqueton renforcé', '14.90', '11.90', 40, 'images/packshots/laisse.jpg'],
        ['croquettes-chat-sterilise', 'Croquettes chat stérilisé', 'chats', 'Poulet et riz – 4 kg', '32.50', null, 30, 'images/packshots/whiskas.jpg'],
        ['griffoir-carton', 'Griffoir carton ondulé', 'chats', 'Jeu et griffade, avec herbe à chat', '12.90', null, 0, 'images/produits/chat-gris.jpg'],
        ['menu-complet-lapin', 'Menu complet pour lapin', 'lapins', 'Alimentation complète – 1 kg', '7.90', null, 60, 'images/packshots/vitakraft-lapin.jpg'],
        ['roue-silencieuse-hamster', 'Roue silencieuse pour hamster', 'hamsters', 'Ø 20 cm, axe silencieux', '16.90', null, 22, 'images/animaux/hamster.jpg'],
        ['foin-cobaye-vitamine-c', 'Mélange cobaye vitamine C', 'cobayes', 'Granulés enrichis – 2 kg', '9.90', '8.50', 35, 'images/animaux/cochon-inde.jpg'],
        ['cage-perruche', 'Cage pour perruches', 'perruches', 'Avec perchoirs, mangeoires et balançoire', '69.90', '59.90', 9, 'images/produits/perruche.jpg'],
        ['graines-perruche', 'Mélange de graines perruche', 'perruches', 'Sans colorant – 1 kg', '6.50', null, 45, 'images/packshots/prestige.jpg'],
        ['graines-canari', 'Mélange de graines canari', 'canaris', 'Riche en vitamines – 1 kg', '7.20', null, 38, 'images/animaux/canari.jpg'],
        ['nourriture-poissons-rouges', 'Flocons pour poissons rouges', 'poissons-rouges', 'Aliment complet – 250 ml', '5.90', null, 70, 'images/animaux/poisson-rouge.jpg'],
        ['terrarium-lampe-uvb', 'Lampe UVB pour terrarium', 'lezards', '10.0 – 26 W, favorise la synthèse de vitamine D', '29.90', null, 14, 'images/produits/lezard.jpg'],
        ['terrarium-serpent', 'Terrarium pour serpent', 'serpents', 'Ventilation optimisée, fermeture sécurisée', '129.00', '109.00', 4, 'images/animaux/serpent.jpg'],
        ['couverture-cheval', 'Couverture imperméable cheval', 'chevaux', 'Doublure polaire, 200 g', '84.90', '74.90', 6, 'images/produits/cheval.jpg'],
        ['brosse-poney', 'Kit de pansage poney', 'poneys', 'Brosses, étrille et cure-pied', '24.90', null, 11, 'images/animaux/poney.jpg'],
        ['caisse-de-transport', 'Caisse de transport', 'chiens', 'Porte sécurisée, ventilation latérale', '39.90', null, 16, 'images/packshots/cage-transport.jpg'],
        ['harnais-confort', 'Harnais confort réglable', 'chiens', 'Maille respirante, bandes réfléchissantes', '24.90', '19.90', 28, 'images/packshots/harnais.jpg'],
        ['gamelle-inox', 'Gamelle inox antidérapante', 'chiens', 'Base en caoutchouc – 0,9 L', '9.90', null, 50, 'images/packshots/gamelle.jpg'],
        ['balle-tennis', 'Balle de jeu pour chien', 'chiens', 'Caoutchouc résistant, rebond optimal', '3.90', null, 80, 'images/packshots/balle.jpg'],
        ['souris-jouet', 'Souris en peluche pour chat', 'chats', 'Avec herbe à chat', '4.50', null, 65, 'images/packshots/souris.jpg'],
    ];

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach (self::UNIVERS as $slug => [$nom, $image]) {
            $categorie = (new Categorie())->setNom($nom)->setSlug($slug)->setImage($image);
            $manager->persist($categorie);
            $categories[$slug] = $categorie;
        }

        foreach (self::ANIMAUX as [$slug, $nom, $parent, $image]) {
            $categorie = (new Categorie())->setNom($nom)->setSlug($slug)->setImage($image)->setParent($categories[$parent]);
            $manager->persist($categorie);
            $categories[$slug] = $categorie;
        }

        foreach (self::PRODUITS as [$slug, $nom, $categorie, $description, $prix, $promo, $stock, $image]) {
            $manager->persist(
                (new Produit())
                    ->setSlug($slug)
                    ->setNom($nom)
                    ->setDescription($description)
                    ->setPrix($prix)
                    ->setPrixPromo($promo)
                    ->setStock($stock)
                    ->setImage($image)
                    ->setCategorie($categories[$categorie])
            );
        }

        $manager->flush();
    }
}
