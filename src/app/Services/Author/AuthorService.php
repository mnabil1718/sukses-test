<?php


namespace App\Services\Author;

use App\DTO\AuthorDTO;
use App\DTO\BookDTO;
use App\DTO\FilterDTO;
use App\Helpers\Pagination;
use App\Repositories\Author\AuthorRepositoryInterface;
use App\Traits\CacheUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthorService
{

    use CacheUtility;

    protected AuthorRepositoryInterface $repository;
    protected string $cacheTag = 'authors';

    public function __construct(AuthorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(FilterDTO $filterDTO)
    {
        $cacheKey = 'authors_' . $filterDTO->page . '_' . $filterDTO->pageSize;

        return $this->cacheWithCallback($this->cacheTag, $cacheKey, 60 * 60, function () use ($filterDTO) {
            $authors = $this->repository->getAll($filterDTO);
            $metadata = count($authors) > 0 ? Pagination::calculateMetadata($authors[0]->total_records, $filterDTO->page, $filterDTO->pageSize) : new stdClass();
            $authors = array_map(function ($author) {
                return new AuthorDTO(
                    id: $author->id,
                    name: $author->name,
                    bio: $author->bio,
                    birth_date: Carbon::parse($author->birth_date)
                );
            }, $authors);

            return [
                'authors' => $authors,
                'metadata' => $metadata
            ];
        });
    }

    public function getById($id)
    {
        $cacheKey = 'authors_' . $id;

        return $this->cacheWithCallback($this->cacheTag, $cacheKey, 60 * 60, function () use ($id) {
            $author = $this->repository->getById($id);

            if (!$author) {
                throw new NotFoundHttpException('author not found');
            }

            $authorDTO = new AuthorDTO(id: $author->id, name: $author->name, bio: $author->bio, birth_date: Carbon::parse($author->birth_date));
            return $authorDTO;
        });
    }


    public function getBooks($id)
    {
        $author = $this->repository->getById($id);

        if (!$author) {
            throw new NotFoundHttpException('author not found');
        }

        $cacheKey = 'authors_' . $id . '_books';
        return $this->cacheWithCallback($this->cacheTag, $cacheKey, 60 * 60, function () use ($id) {

            $books = $this->repository->getBooks($id);

            return array_map(function ($book) {
                return new BookDTO(
                    id: $book->id,
                    title: $book->title,
                    description: $book->description,
                    publish_date: Carbon::parse($book->publish_date),
                    author: new AuthorDTO(id: $book->author_id)
                );
            }, $books);
        });
    }

    public function create(AuthorDTO $author)
    {
        $authorResponse = $this->repository->insert($author);

        if (!$authorResponse) {
            throw new BadRequestHttpException("author cannot be created");
        }

        $author->id = $authorResponse->id;
        $this->flushCache($this->cacheTag);

        return $author;
    }


    public function update(AuthorDTO $author)
    {
        $authorResponse = $this->repository->getById($author->id);

        if (!$authorResponse) {
            throw new NotFoundHttpException("author not found");
        }

        $author->name = $author->name ?? $authorResponse->name;
        $author->bio = $author->bio ?? $authorResponse->bio;
        $author->birth_date = $author->birth_date ?? Carbon::parse($authorResponse->birth_date);

        $affected = $this->repository->save($author);

        if (!$affected || $affected < 1) {
            throw new BadRequestHttpException("author update failed");
        }

        $this->flushCache($this->cacheTag);

        return $author;
    }

    public function delete(int $id)
    {
        $authorResponse = $this->repository->getById($id);

        if (!$authorResponse) {
            throw new NotFoundHttpException("author not found");
        }

        $affected = $this->repository->delete($id);

        if (!$affected || $affected < 1) {
            throw new BadRequestHttpException("author delete failed");
        }

        $this->flushCache($this->cacheTag);
    }
}
