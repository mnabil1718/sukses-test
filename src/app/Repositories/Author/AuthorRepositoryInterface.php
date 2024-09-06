<?php

namespace App\Repositories\Author;

use App\DTO\AuthorDTO;

interface AuthorRepositoryInterface
{

    public function getAll(): array;
    public function getById($id): mixed;
    public function insert(AuthorDTO $author): mixed;
    public function save(AuthorDTO $author): mixed;
    public function delete($id): int;
}
