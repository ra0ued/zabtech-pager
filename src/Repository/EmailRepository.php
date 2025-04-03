<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Email>
 */
class EmailRepository extends ServiceEntityRepository
{
    public const MESSAGES_PER_PAGE = 50;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    /**
     * @param int $page
     * @return mixed
     */
    public function getAllMessages(int $page): mixed
    {
        $offset = ($page - 1) * self::MESSAGES_PER_PAGE;

        return $this->createQueryBuilder('m')
            ->orderBy('m.receivedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults(self::MESSAGES_PER_PAGE)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $senderId
     * @return mixed
     */
    public function getFromSender(int $senderId): mixed
    {
        return $this->createQueryBuilder('m')
            ->where('m.senderId = :senderId')
            ->setParameter('senderId', $senderId)
            ->orderBy('m.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $keyword
     * @return array
     */
    public function findBySubject(string $keyword = ''): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->where($qb->expr()->like('m.subject', ':keyword'))
            ->orWhere($qb->expr()->like('m.body', ':keyword'))
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('m.receivedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
