<?php

namespace App\Entity;

use App\Controller\GoogleBooksController;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      collectionOperations={
 *          "custom_gbapi_isbn"={
 *              "controller"=GoogleBooksController::class,
 *              "method"="GET",
 *              "path"="/gbooks",
 *              "defaults"={"_api_receive"=false},
 *              "swagger_context"={
 *                  "parameters"={}
 *              }
 *         }
 *      },
 *      itemOperations={
 *          "get",
 *          
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={"isbn":"exact", "author":"partial", "title":"partial", "language":"exact"}
 * )
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ApiProperty(identifier=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"book:read", "user:read", "userbook:read"})
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"book:read", "user:read", "userbook:read"})
     */
    private $title;

    /**
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="string", length=13, unique=true)
     * @Groups({"book:read", "user:read", "userbook:read"})
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"book:read", "userbook:read"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"book:read", "userbook:read"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Groups({"book:read", "userbook:read"})
     */
    private $language;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }
}
