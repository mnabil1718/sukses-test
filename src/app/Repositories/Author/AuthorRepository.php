<?php


namespace App\Repositories\Author;

use App\DTO\AuthorDTO;
use App\DTO\FilterDTO;
use Illuminate\Support\Facades\DB;

class AuthorRepository implements AuthorRepositoryInterface
{

    public function getAll(FilterDTO $filterDTO): array
    {
        return DB::select("SELECT COUNT(*) OVER() AS total_records, id, name, bio, birth_date FROM authors ORDER BY id DESC LIMIT ? OFFSET ?", [$filterDTO->limit(), $filterDTO->offset()]);
    }

    public function getById($id): mixed
    {
        return DB::selectOne("SELECT id, name, bio, birth_date FROM authors WHERE id=?", [$id]);
    }

    public function getBooks($id): array
    {
        return DB::select("SELECT b.id, b.title, b.description, b.publish_date, b.author_id FROM authors a JOIN books b ON b.author_id=a.id WHERE a.id=?", [$id]);
    }

    public function insert(AuthorDTO $author): mixed
    {
        return DB::selectOne('INSERT INTO authors (name, bio, birth_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?) RETURNING id', [$author->name, $author->bio, $author->birth_date, now(), now()]);
    }


    public function save(AuthorDTO $author): mixed
    {
        return DB::update('UPDATE authors SET name=?, bio=?, birth_date=?, updated_at=? WHERE id=?', [$author->name, $author->bio, $author->birth_date, now(), $author->id]);
    }

    public function delete($id): int
    {
        return DB::delete("DELETE FROM authors WHERE id=?", [$id]);
    }
}
