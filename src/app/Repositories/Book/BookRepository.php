<?php

namespace App\Repositories\Book;

use App\Repositories\Book\BookRepositoryInterface;
use App\DTO\BookDTO;
use App\DTO\FilterDTO;
use Illuminate\Support\Facades\DB;

class BookRepository implements BookRepositoryInterface
{

    public function getAll(FilterDTO $filter): array
    {
        return DB::select("SELECT COUNT(*) OVER() AS total_records, id, title, description, publish_date, author_id FROM books ORDER BY id DESC LIMIT ? OFFSET ?", [$filter->limit(), $filter->offset()]);
    }

    public function getByIdWithAuthor($id): mixed
    {
        return DB::selectOne("SELECT b.id, b.title, b.description, b.publish_date, a.id AS author_id, a.name AS author_name, a.bio AS author_bio, a.birth_date AS author_birth_date FROM books b LEFT JOIN authors a ON b.author_id=a.id WHERE b.id=?", [$id]);
    }

    public function getById($id): mixed
    {
        return DB::selectOne("SELECT id, title, description, publish_date, author_id FROM books WHERE id=?", [$id]);
    }

    public function insert(BookDTO $book): mixed
    {
        return DB::selectOne('INSERT INTO books (title, description, publish_date, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) RETURNING id', [$book->title, $book->description, $book->publish_date, $book->author->id, now(), now()]);
    }


    public function save(BookDTO $book): mixed
    {
        return DB::update('UPDATE books SET title=?, description=?, publish_date=?, author_id=?, updated_at=? WHERE id=?', [$book->title, $book->description, $book->publish_date, $book->author == null ? null : $book->author->id, now(), $book->id]);
    }

    public function delete($id): int
    {
        return DB::delete("DELETE FROM books WHERE id=?", [$id]);
    }
}
