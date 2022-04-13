<?php
namespace App\DataPersister;

use App\Entity\UserBook;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class UserBookDataPersister implements ContextAwareDataPersisterInterface
{
    private $entityManager;
    private $request;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $request,
        Security $security
    )
    {
        $this->entityManager = $entityManager;
        $this->request = $request->getCurrentRequest();
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }
    
    /**
     * @param User $data
     */
    public function persist($data, array $context = [])
    {
        if ($this->request->getMethod() === 'POST') {
            $data->setUser($this->security->getUser());
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();
        return $data;
    }
    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}