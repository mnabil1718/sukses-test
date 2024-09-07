<?php


namespace App\Services\Book;

use App\DTO\AuthorDTO;
use App\DTO\BookDTO;
use App\DTO\FilterDTO;
use App\Exceptions\HttpException;
use App\Helpers\Pagination;
use App\Repositories\Author\AuthorRepositoryInterface;
use App\Repositories\Book\BookRepositoryInterface;
use Carbon\Carbon;

class BookService
{

    protected BookRepositoryInterface $repository;
    protected AuthorRepositoryInterface $authorRepository;

    public function __construct(BookRepositoryInterface $repository, AuthorRepositoryInterface $authorRepository)
    {
        $this->repository = $repository;
        $this->authorRepository = $authorRepository;
    }

    public function getAll(FilterDTO $filterDTO)
    {
        $books = $this->repository->getAll($filterDTO);
        $metadata = count($books) > 0 ? Pagination::calculateMetadata($books[0]->total_records, $filterDTO->page, $filterDTO->pageSize) : [];
        $books = array_map(function ($book) {
            return new BookDTO(
                id: $book->id,
                title: $book->title,
                description: $book->description,
                publish_date: Carbon::parse($book->publish_date),
                author: $book->author_id == null ? null : new AuthorDTO(id: $book->author_id)
            );
        }, $books);

        return [
            'books' => $books,
            'metadata' => $metadata
        ];
    }

    public function getByIdWithAuthor($id)
    {
        $book = $this->repository->getByIdWithAuthor($id);

        if (!$book) {
            throw new HttpException('book not found', 404);
        }

        $bookDTO = new BookDTO(
            id: $book->id,
            title: $book->title,
            description: $book->description,
            publish_date: Carbon::parse($book->publish_date),
            author: $book->author_id == null ? null : new AuthorDTO(
                id: $book->author_id,
                name: $book->author_name,
                bio: $book->author_bio,
                birth_date: Carbon::parse($book->author_birth_date)
            )
        );
        return $bookDTO;
    }

    public function create(BookDTO $book)
    {
        $author = $this->authorRepository->getById($book->author->id);

        if (!$author) {
            throw new HttpException("author ID not found", 404);
        }

        $bookResponse = $this->repository->insert($book);

        if (!$bookResponse) {
            throw new HttpException("book cannot be created", 400);
        }

        $book->id = $bookResponse->id;
        return $book;
    }


    public function update(BookDTO $book)
    {

        $bookResponse = $this->repository->getById($book->id);

        if (!$bookResponse) {
            throw new HttpException("book not found", 404);
        }

        $book->title = $book->title ?? $bookResponse->title;
        $book->description = $book->description ?? $bookResponse->description;
        $book->publish_date = $book->publish_date ?? Carbon::parse($bookResponse->publish_date);

        if ($book->author == null && $bookResponse->author_id != null) {
            $book->author =  new AuthorDTO(id: $bookResponse->author_id);
        }

        if ($book->author != null) {
            $author = $this->authorRepository->getById($book->author->id);

            if (!$author) {
                throw new HttpException("author ID not found", 404);
            }
        }


        $affected = $this->repository->save($book);

        if (!$affected || $affected < 1) {
            throw new HttpException("book update failed", 400);
        }

        return $book;
    }

    public function delete(int $id)
    {
        $bookResponse = $this->repository->getById($id);

        if (!$bookResponse) {
            throw new HttpException("book not found", 404);
        }

        $affected = $this->repository->delete($id);

        if (!$affected || $affected < 1) {
            throw new HttpException("book delete failed", 400);
        }
    }
}
