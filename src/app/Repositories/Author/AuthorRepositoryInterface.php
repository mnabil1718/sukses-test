<?php

namespace App\Repositories\Author;

use App\DTO\AuthorDTO;
use App\DTO\FilterDTO;

interface AuthorRepositoryInterface
{
    public function getAll(FilterDTO $filterDTO): array;
    public function getById($id): mixed;
    public function getBooks($id): array;
    public function insert(AuthorDTO $author): mixed;
    public function save(AuthorDTO $author): mixed;
    public function delete($id): int;
}
