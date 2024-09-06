<?php


namespace App\Repositories\Author;

use App\DTO\AuthorDTO;
use Illuminate\Support\Facades\DB;

class AuthorRepository implements AuthorRepositoryInterface
{

    public function getAll(): array
    {
        return DB::select("SELECT id, name, bio, birth_date FROM authors");
    }

    public function getById($id): mixed
    {
        return DB::selectOne("SELECT id, name, bio, birth_date FROM authors WHERE id=?", [$id]);
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
