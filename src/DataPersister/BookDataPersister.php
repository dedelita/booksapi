<?php

namespace App\DataPersister;

use App\Entity\Book;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class BookDataPersister implements ContextAwareDataPersisterInterface
{
    private $entityManager;
    private $security;
    private $googleClient;
    private $gbService;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->googleClient = new \Google\Client();
        $this->googleClient->setApplicationName($this->getParameter("app_name"));
        $this->googleClient->setDeveloperKey($this->getParameter("api_key"));
        $this->gbService = new \Google_Service_Books($this->googleClient);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Book;
    }
    
    /**
     * @param Book $data
     */
    public function persist($data, array $context = [])
    {
        if ($context['collection_operation_name'] == 'post') {
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}