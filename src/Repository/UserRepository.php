<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use AbstractRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findUserByUsernameOrEmail(string $username)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :val OR u.email = :val')
            ->setParameter('val', $username)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByParams(
        int $page,
        int $limit,
        string $sort,
        string $sortBy,
        ?string $search
    ) {
        $query = $this->createQueryBuilder('e');
        $query = $this->search($query, $search);
        return $this->resultCount(
            $query,
            $page,
            $limit,
            false,
            $sort,
            $sortBy,
            null,
            null
        );
    }

    private function search(QueryBuilder $query, ?string $search)
    {
        if ($search != null) {
            $query = $query->andWhere('(LOWER(e.username) LIKE :search OR LOWER(e.email) LIKE :search)')
                ->setParameter('search', '%' . addcslashes(strtolower($search), '%_') . '%');
        }
        return $query;
    }
}
