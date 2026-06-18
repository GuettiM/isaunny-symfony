<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function findLatest(?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'c')->addSelect('c')
            ->orderBy('a.id', 'DESC')
            ->setFirstResult($offset);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Article[]
     */
    public function findByCategorie(Categorie $categorie): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
