<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant aléatoire stocké dans le cookie du visiteur pour retrouver son panier. */
    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $creeLe;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $modifieLe;

    /** @var Collection<int, LignePanier> */
    #[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: 'panier', cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignes;

    public function __construct()
    {
        $this->token = bin2hex(random_bytes(32));
        $this->creeLe = new \DateTimeImmutable();
        $this->modifieLe = new \DateTimeImmutable();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function getModifieLe(): \DateTimeImmutable
    {
        return $this->modifieLe;
    }

    public function touch(): static
    {
        $this->modifieLe = new \DateTimeImmutable();

        return $this;
    }

    /** @return Collection<int, LignePanier> */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LignePanier $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setPanier($this);
        }

        return $this;
    }

    public function removeLigne(LignePanier $ligne): static
    {
        $this->lignes->removeElement($ligne);

        return $this;
    }

    public function getNbArticles(): int
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getQuantite();
        }

        return $total;
    }

    public function getTotal(): string
    {
        $total = '0.00';
        foreach ($this->lignes as $ligne) {
            $total = bcadd($total, $ligne->getSousTotal(), 2);
        }

        return $total;
    }
}
