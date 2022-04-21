<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class GoogleBooksController extends AbstractController
{
    private $googleClient;
    private $gbService;
    private $serializer;

    public function __construct()
    {
        $this->googleClient = new \Google\Client();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    private function createGBService()
    {
        $this->googleClient->setApplicationName($this->getParameter("app_name"));
        $this->googleClient->setDeveloperKey($this->getParameter("google_api_key"));
        $this->gbService = new \Google_Service_Books($this->googleClient);
    }

    public function __invoke(Request $request, BookRepository $bookRepository)
    {
        $isbn = $request->get('isbn');
        $title = $request->get('title');
        $author = $request->get('author');
        $language = $request->get('language');
        $this->createGBService();
        if (!empty($isbn)) {
            $book = $bookRepository->findByIsbn($isbn);
            if ($book) {
                $result = $book;
            } else {
                $results = $this->getResultsGB("isbn:$isbn", 40, 0);
                $rgb = $results->getItems();
                
                $result = $this->getGBooksInfo($rgb[0]);
            }
        } elseif (!empty($title)) { //&& !empty($author) || !empty($title)) {
            if (!empty($author)) {
                $book = $bookRepository->findBy(["author" => $author, "title" => $title]);
            } else {
                $book = $bookRepository->findByTitle($title);
            }
            if ($book) {
                $result = $book;
            } else {
                $result = $this->getGBooks($title, $author, $language);
            }
        } else {
            $result = $bookRepository->findAll();
        }
        
        return $result;
    }

    private function getGBooks($title, $author, $lang) 
    {
        $startIndex = 0;

        $q = "";
        if ($title)
            $q.= "intitle:\"" . str_replace(' ', '+', $title) . "\"";
        if (!empty($author))
            $q.= "+inauthor:\"" . str_replace(' ', '+', $author) . "\"";
        $results = $this->getResultsGB($q, 40, $startIndex, $lang);
        
        $rgb = $results->getItems();
        
        if ($results['totalItems'] > 40) {
            $iteratorNb = intdiv($results['totalItems'], 40);
            for ($i=0; $i < $iteratorNb; $i++) { 
                $startIndex += 39;
                $results = $this->getResultsGB($q, 40, $startIndex, $lang);
                $rgb = array_merge($rgb, $results->getItems());
            }
        }
        $books = [];
        foreach ($rgb as $item) {
            if($this->checkBook($item, $author, $title) && $item['volumeInfo']['language'] == $lang) {
                $books[] = $this->getGBooksInfo($item);
            }
        }
        
        if (!$books) {
            foreach ($rgb as $item) {
                if ($this->checkBook($item, $author, $title)) {
                    $item['volumeInfo']['language'] = $lang;
                    $books[] = $this->getGBooksInfo($item);
                   }
            }
        }
        
        if (!$books) {
            $q =  str_replace(' ', '+', $title) . "+inauthor:$author";
            $results = $this->getResultsGB($q, 40, $startIndex, $lang);
            $rgb = $results->getItems();
            foreach ($rgb as $item) {
                if($this->checkBook($item, $author, $title) && $item['volumeInfo']['language'] == $lang)
                    $books[] = $this->getGBooksInfo($item);
            }
        }
        return $books;
    }

    private function checkBook($item, $author, $title) {
        return $item['volumeInfo']['imageLinks'] && $item['volumeInfo']['authors'] && 
        ((
            !empty($author) &&
            in_array(strtolower($author), array_map("strtolower", $item['volumeInfo']['authors']))
        ) || 
        (
            !empty($author) &&
            preg_grep(
                '/[*]*?' . strtolower($author) . '[*]*?/', 
                array_map("strtolower", $item['volumeInfo']['authors'])
            )
        ) ||
        (empty($author))) &&
        (str_contains(mb_strtolower($item['volumeInfo']['title']), mb_strtolower($title)) ||
            str_contains(mb_strtolower($item['volumeInfo']['subtitle']), mb_strtolower($title)));
    }

    private function getResultsGB($q, $maxRes, $startIndex, $lang = null)
    {
        if ($lang != null) {
            return $this->gbService->volumes->listVolumes(['q' => $q], [
                'maxResults' => $maxRes, 
                'startIndex' => $startIndex,
                'langRestrict' => $lang,
                'printType' => "books"
            ]);
        }
        return $this->gbService->volumes->listVolumes(['q' => $q], [
            'maxResults' => $maxRes, 
            'startIndex' => $startIndex,
            'printType' => "books"
        ]);
    }

    private function getGBooksInfo($gbook) 
    {
        $authors = $gbook['volumeInfo']['authors'];
        foreach ($authors as $author) {
            $author = preg_replace("/([A-Z]{1})\-([A-Z]{1}) (.*)/", "$1.$2. $3", $author);
            $author = preg_replace("/([A-Z]{1})\. ([A-Z]{1}\.)(.*)/", "$1.$2 $3", $author);
            $authors_list[] = ucwords(mb_strtolower($author));
        }
        $book = new Book();
        if ($gbook['volumeInfo']['subtitle']) {
            $book->setTitle($gbook['volumeInfo']['title'] . " - " . $gbook['volumeInfo']['subtitle']);
        } else {
            $book->setTitle($gbook['volumeInfo']['title']);
        }
        $book->setAuthor(implode(", ", $authors_list));
        $book->setDescription($gbook['volumeInfo']['description']);
        $book->setImage($gbook['volumeInfo']['imageLinks']['thumbnail']);
        foreach ($gbook['volumeInfo']['industryIdentifiers'] as $identifier) {
            if (!empty($identifier['type']) && $identifier['type'] == "ISBN_13") {
                $book->setIsbn($identifier['identifier']);
            }
        }
        if ($gbook['volumeInfo']['language'] != null) {
            $book->setLanguage($gbook['volumeInfo']['language']);
        }

        return $book;
    }
}
