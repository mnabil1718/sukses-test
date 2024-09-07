<?php

namespace App\Repositories\Book;

use App\DTO\BookDTO;
use App\DTO\FilterDTO;

interface BookRepositoryInterface
{
    public function getAll(FilterDTO $filter): array;
    public function getById($id): mixed;
    public function getByIdWithAuthor($id): mixed;
    public function insert(BookDTO $book): mixed;
    public function save(BookDTO $book): mixed;
    public function delete($id): int;
}
