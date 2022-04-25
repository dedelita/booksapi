<?php
namespace App\DataPersister;

use App\Entity\UserBook;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Repository\UserBookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserBookDataPersister implements ContextAwareDataPersisterInterface
{
    private $entityManager;
    private $security;
    private $userBookRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, UserBookRepository $userBookRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->userBookRepository = $userBookRepository;
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
            $user = $this->security->getUser();
            $data->setUser($user);
            $ub = $this->userBookRepository->findByUserIsbnBook($user, $data->getBook()->getIsbn());
            if (!empty($ub)) {
                return $ub[0];
            }
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