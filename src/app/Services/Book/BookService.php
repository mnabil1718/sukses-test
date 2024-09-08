<?php


namespace App\Services\Book;

use App\DTO\AuthorDTO;
use App\DTO\BookDTO;
use App\DTO\FilterDTO;
use App\Helpers\Pagination;
use App\Repositories\Author\AuthorRepositoryInterface;
use App\Repositories\Book\BookRepositoryInterface;
use App\Traits\CacheUtility;
use Carbon\Carbon;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookService
{
    use CacheUtility;

    protected BookRepositoryInterface $repository;
    protected AuthorRepositoryInterface $authorRepository;
    protected string $cacheTag = 'books';

    public function __construct(BookRepositoryInterface $repository, AuthorRepositoryInterface $authorRepository)
    {
        $this->repository = $repository;
        $this->authorRepository = $authorRepository;
    }

    public function getAll(FilterDTO $filterDTO)
    {
        $cacheKey = 'books_' . $filterDTO->page . '_' . $filterDTO->pageSize;

        return $this->cacheWithCallback($this->cacheTag, $cacheKey, 60 * 60, function () use ($filterDTO) {
            $books = $this->repository->getAll($filterDTO);
            $metadata = count($books) > 0 ? Pagination::calculateMetadata($books[0]->total_records, $filterDTO->page, $filterDTO->pageSize) : new stdClass();
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
        });
    }

    public function getByIdWithAuthor($id)
    {

        $cacheKey = 'books_' . $id;

        return $this->cacheWithCallback($this->cacheTag, $cacheKey, 60 * 60, function () use ($id) {
            $book = $this->repository->getByIdWithAuthor($id);

            if (!$book) {
                throw new NotFoundHttpException('book not found');
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
        });
    }

    public function create(BookDTO $book)
    {
        $author = $this->authorRepository->getById($book->author->id);

        if (!$author) {
            throw new NotFoundHttpException("author ID not found");
        }

        $bookResponse = $this->repository->insert($book);

        if (!$bookResponse) {
            throw new BadRequestHttpException("book cannot be created");
        }

        $book->id = $bookResponse->id;
        $this->flushCache($this->cacheTag);
        return $book;
    }


    public function update(BookDTO $book)
    {

        $bookResponse = $this->repository->getById($book->id);

        if (!$bookResponse) {
            throw new NotFoundHttpException("book not found");
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
                throw new NotFoundHttpException("author ID not found");
            }
        }


        $affected = $this->repository->save($book);

        if (!$affected || $affected < 1) {
            throw new BadRequestHttpException("book update failed");
        }

        $this->flushCache($this->cacheTag);

        return $book;
    }

    public function delete(int $id)
    {
        $bookResponse = $this->repository->getById($id);

        if (!$bookResponse) {
            throw new NotFoundHttpException("book not found");
        }

        $affected = $this->repository->delete($id);

        if (!$affected || $affected < 1) {
            throw new BadRequestHttpException("book delete failed");
        }

        $this->flushCache($this->cacheTag);
    }
}
