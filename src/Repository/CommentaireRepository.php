<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /** @return Commentaire[] */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('co')
            ->leftJoin('co.membre', 'm')->addSelect('m')
            ->leftJoin('co.article', 'a')->addSelect('a')
            ->orderBy('co.dateCommentaire', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Commentaires validés d'un article, du plus récent au plus ancien.
     *
     * @return Commentaire[]
     */
    public function findValidatedByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('co')
            ->leftJoin('co.membre', 'm')->addSelect('m')
            ->where('co.article = :article')
            ->andWhere('co.statut = :statut')
            ->setParameter('article', $articleId)
            ->setParameter('statut', Commentaire::STATUT_VALIDE)
            ->orderBy('co.dateCommentaire', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('co')
            ->select('COUNT(co.id)')
            ->where('co.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
