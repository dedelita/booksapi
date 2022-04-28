<?php
namespace App\DataPersister;

use App\Entity\UserBook;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Repository\BookRepository;
use App\Repository\UserBookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserBookDataPersister implements ContextAwareDataPersisterInterface
{
    private $entityManager;
    private $security;
    private $userBookRepository;
    private $bookRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        Security $security, 
        UserBookRepository $userBookRepository,
        BookRepository $bookRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->userBookRepository = $userBookRepository;
        $this->bookRepository = $bookRepository;
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

            $isbn = $data->getBook()->getIsbn();
            $ub = $this->userBookRepository->findOneByUserIsbnBook($user, $isbn);
            if ($ub) {
                return $ub;
            }

            $book = $this->bookRepository->findOneByIsbn($isbn);
            if ($book) {
                $data->setBook($book);
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