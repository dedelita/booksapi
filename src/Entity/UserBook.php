<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\UserBookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      normalizationContext={"groups"={"userbook:read"}},
 *      denormalizationContext={"groups"={"userbook:write"}},
 *      collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER')"},
 *         "post"={"security"="is_granted('ROLE_USER')"}
*       },
*       itemOperations={
*         "get"={"security"="is_granted('ROLE_USER') and object.getUser() == user"},
*         "put"={"security"="is_granted('ROLE_USER') and object.getUser() == user"},
*         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUser() == user"}
*       }
 * )
 * @ORM\Entity(repositoryClass=UserBookRepository::class)
 */
class UserBook
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="books")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Book::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"userbook:write", "user:read"})
     */
    private $book;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("userbook:read")
     */
    private $createdDate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("userbook:read")
     */
    private $isDeleted;

    /**
     * @ApiSubresource
     * @ORM\OneToOne(targetEntity=Comment::class, inversedBy="userBook", cascade={"persist", "remove"})
     */
    private $comment;

    public function __construct()
    {
        $this->isDeleted = false;
        $this->createdDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
