<?php
namespace App\DataPersister;

use App\Entity\UserBook;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserBookDataPersister implements ContextAwareDataPersisterInterface
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBook;
    }
    
    /**
     * @param UserBook $data
     */
    public function persist($data, array $context = [])
    {
        if ($context['collection_operation_name'] == 'post') {
            $data->setUser($this->security->getUser());
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