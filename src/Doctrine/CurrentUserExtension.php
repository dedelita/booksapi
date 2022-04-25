<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Entity\UserBook;
use App\Entity\Comment;

class CurrentUserExtension implements QueryCollectionExtensionInterface
{
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();
        
        if (
            UserBook::class !== $resourceClass
            && Comment::class !== $resourceClass
            || $this->security->isGranted("ROLE_ADMIN")
            || null === $user
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        if ($resourceClass === Comment::class) {
            $queryBuilder->join(sprintf('%s.userBook', $rootAlias), 'ub')
                ->addSelect('ub')
                ->andWhere('ub.user = :current_user');
        } else {
            $queryBuilder->andWhere(sprintf('%s.user = :current_user', $rootAlias));
        }
        /** @var User $user */
        $queryBuilder->setParameter('current_user', $user);
    }
}