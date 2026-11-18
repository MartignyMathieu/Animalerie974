<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * @return Produit[]
     */
    public function rechercher(?Categorie $categorie = null, ?string $recherche = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c')
            ->orderBy('p.nom', 'ASC');

        if (null !== $categorie) {
            // La catégorie elle-même et ses sous-catégories (ex. « NAC » + « Lapins »).
            $qb->andWhere('p.categorie = :categorie OR c.parent = :categorie')->setParameter('categorie', $categorie);
        }

        if (null !== $recherche && '' !== trim($recherche)) {
            $qb->andWhere('p.nom LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.trim($recherche).'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Produit[]
     */
    public function vedettes(int $limite = 4): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock > 0')
            ->orderBy('p.id', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}
