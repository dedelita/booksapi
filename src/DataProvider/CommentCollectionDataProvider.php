<?php
// api/src/DataProvider/CommentDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\Security\Core\Security;

class CommentCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $commentRepository;
    private $security;

    public function __construct(CommentRepository $commentRepository, Security $security)
    {
        $this->commentRepository = $commentRepository;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Comment::class === $resourceClass && $operationName == "get";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $collection = $this->commentRepository->findByUser($this->security->getUser());
        return $collection;
    }
}
