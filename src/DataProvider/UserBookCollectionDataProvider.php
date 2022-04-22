<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\UserBook;
use App\Repository\UserBookRepository;
use Symfony\Component\Security\Core\Security;

class UserBookCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $userBookRepository;
    private $security;

    public function __construct(UserBookRepository $userBookRepository, Security $security)
    {
        $this->userBookRepository = $userBookRepository;
        $this->security = $security;
    }
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBook::class === $resourceClass && $operationName == "get";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $collection = $this->userBookRepository->findByUser($this->security->getUser());
        return $collection;
    }
}