<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      normalizationContext={"groups"={"comment:read"}},
 *      denormalizationContext={"groups"={"comment:write"}},
 *      collectionOperations={
 *         "get"={"security"="is_granted('ROLE_USER')", "openapi_context"={"summary"="Récupérer les comments de l'user"}},
 *         "post"={"security"="is_granted('ROLE_USER')"}
*       },
*       itemOperations={
*         "get"={"security"="is_granted('ROLE_USER') and object.getUserBook().getUser() == user"},
*         "put"={"security"="is_granted('ROLE_USER') and object.getUserBook().getUser() == user"},
*         "delete"={"security"="is_granted('ROLE_ADMIN') or object.getUserBook().getUser() == user"}
*       }
 * )
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"comment:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"comment:write", "comment:read"})
     */
    private $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"comment:write", "comment:read"})
     */
    private $stars;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"comment:read"})
     */
    private $createdDate;

    /**
     * @ORM\OneToOne(targetEntity=UserBook::class, mappedBy="comment", cascade={"persist", "remove"})
     * @Groups({"comment:write"}, "comment:read")
     */
    private $userBook;

    public function __construct()
    {
        $this->createdDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(?int $stars): self
    {
        $this->stars = $stars;

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

    public function getUserBook(): ?UserBook
    {
        return $this->userBook;
    }

    public function setUserBook(?UserBook $userBook): self
    {
        // unset the owning side of the relation if necessary
        if ($userBook === null && $this->userBook !== null) {
            $this->userBook->setComment(null);
        }

        // set the owning side of the relation if necessary
        if ($userBook !== null && $userBook->getComment() !== $this) {
            $userBook->setComment($this);
        }

        $this->userBook = $userBook;

        return $this;
    }
}
