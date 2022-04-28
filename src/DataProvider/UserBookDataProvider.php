<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use App\Doctrine\CurrentUserExtension;
use App\Entity\UserBook;
use App\Repository\UserBookRepository;

final class UserBookDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $userBookRepository;
    private $currentUserExtension;
    private $collectionExtensions;

    public function __construct(UserBookRepository $userBookRepository, CurrentUserExtension $currentUserExtension, iterable $collectionExtensions)
    {
        $this->userBookRepository = $userBookRepository;
        $this->currentUserExtension = $currentUserExtension;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBook::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $queryBuilder = $this->userBookRepository->createQueryBuilder('ub')
            ->andWhere('ub.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false);
        $queryNameGenerator = new QueryNameGenerator();

        $this->currentUserExtension
            ->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
        
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $result = $extension->getResult($queryBuilder, $resourceClass, $operationName);
                return $result;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}