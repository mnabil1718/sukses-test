<?php


namespace App\Services\Author;

use App\DTO\AuthorDTO;
use App\Exceptions\HttpException;
use App\Repositories\Author\AuthorRepositoryInterface;
use Carbon\Carbon;

class AuthorService
{

    protected AuthorRepositoryInterface $repository;

    public function __construct(AuthorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        $authors = $this->repository->getAll();
        return array_map(function ($author) {
            return new AuthorDTO(
                id: $author->id,
                name: $author->name,
                bio: $author->bio,
                birth_date: Carbon::parse($author->birth_date)
            );
        }, $authors);
    }

    public function getById($id)
    {
        $author = $this->repository->getById($id);

        if (!$author) {
            throw new HttpException('author not found', 404);
        }

        $authorDTO = new AuthorDTO(id: $author->id, name: $author->name, bio: $author->bio, birth_date: Carbon::parse($author->birth_date));
        return $authorDTO;
    }

    public function create(AuthorDTO $author)
    {
        $authorResponse = $this->repository->insert($author);

        if (!$authorResponse) {
            throw new HttpException("author cannot be created", 400);
        }

        $author->id = $authorResponse->id;
        return $author;
    }


    public function update(AuthorDTO $author)
    {
        $authorResponse = $this->repository->getById($author->id);

        if (!$authorResponse) {
            throw new HttpException("author not found", 404);
        }

        $author->name = $author->name ?? $authorResponse->name;
        $author->bio = $author->bio ?? $authorResponse->bio;
        $author->birth_date = $author->birth_date ?? Carbon::parse($authorResponse->birth_date);

        $affected = $this->repository->save($author);

        if (!$affected || $affected < 1) {
            throw new HttpException("author update failed", 400);
        }

        return $author;
    }

    public function delete(int $id)
    {
        $authorResponse = $this->repository->getById($id);

        if (!$authorResponse) {
            throw new HttpException("author not found", 404);
        }

        $affected = $this->repository->delete($id);

        if (!$affected || $affected < 1) {
            throw new HttpException("author delete failed", 400);
        }
    }
}
