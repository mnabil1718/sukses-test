<?php

namespace Tests\Unit;

use App\DTO\AuthorDTO;
use App\DTO\BookDTO;
use App\DTO\FilterDTO;
use App\Repositories\Author\AuthorRepository;
use App\Repositories\Book\BookRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorRepositoryTest extends TestCase
{

    use RefreshDatabase;


    public function test_create_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $payload = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($payload);

        $this->assertNotNull($result);
    }

    public function test_create_fail_null_arguments(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $payload = new AuthorDTO();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("23000");
        $repository->insert($payload);
    }


    public function test_update_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = $result->id;
        $input->name = "Kowalski";

        $affected = $repository->save($input);

        $updated = $repository->getById($input->id);

        $this->assertNotNull($affected);
        $this->assertTrue($affected > 0, "affected rows must be atleast 1");
        $this->assertSame($input->id, $updated->id, "id after update must be the same");
        $this->assertSame($input->name, $updated->name, "name after update must be the same");
    }


    public function test_update_fail_not_affected(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = 100;

        $affected = $repository->save($input);

        $this->assertNotNull($affected);
        $this->assertNotTrue($affected > 0, "affected rows must be atleast 1");
    }


    public function test_delete_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = $result->id;

        $affected = $repository->delete($input->id);

        $updated = $repository->getById($input->id);

        $this->assertNotNull($affected);
        $this->assertTrue($affected > 0, "affected rows must be atleast 1");
        $this->assertNull($updated);
    }


    public function test_delete_fail_not_affected(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $repository->insert($input);
        $input->id = 1000;

        $affected = $repository->delete($input->id);

        $this->assertNotTrue($affected > 0, "affected rows must be atleast 1");
    }



    public function test_get_one_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = $result->id;

        $result = $repository->getById($input->id);

        $this->assertSame($input->id, $result->id, "id have to be the same");
        $this->assertSame($input->name, $result->name, "name have to be the same");
        $this->assertSame($input->bio, $result->bio, "bio have to be the same");
        $this->assertSame($input->birth_date->format('Y-m-d'), Carbon::parse($result->birth_date)->format('Y-m-d'), "birth_date have to be the same");
    }


    public function test_get_one_fail_not_found(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $repository->insert($input);

        $result = $repository->getById(201);
        $this->assertNull($result);
    }


    public function test_get_all_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = $result->id;

        $filterDTO = new FilterDTO();

        $authors = $repository->getAll($filterDTO);

        $this->assertTrue(count($authors) == 1, "no data returned");
    }


    public function test_get_all_fail_no_data(): void
    {
        $repository = $this->app->make(AuthorRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $result = $repository->insert($input);
        $input->id = $result->id;

        $filterDTO = new FilterDTO(page: 100, pageSize: 0);

        $authors = $repository->getAll($filterDTO);

        $this->assertTrue(count($authors) == 0, "unidentified data returned");
    }


    public function test_get_books_success(): void
    {
        $repository = $this->app->make(AuthorRepository::class);
        $bookRepository = $this->app->make(BookRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $authorResult = $repository->insert($input);

        $book = new BookDTO(
            title: "Dilan 8133 B.C",
            description: "Prequel Dilan mengenai nenek moyangnya Seorang Gigantopithecus",
            publish_date: Carbon::now(),
            author: new AuthorDTO(id: $authorResult->id)
        );

        $bookResult = $bookRepository->insert($book);
        $book->id = $bookResult->id;

        $books = $repository->getBooks($authorResult->id);

        $this->assertTrue(count($books) == 1, "data must be returned");

        $this->assertSame($book->id, $books[0]->id, "id have to be the same");
        $this->assertSame($book->title, $books[0]->title, "title have to be the same");
        $this->assertSame($book->author->id, $books[0]->author_id, "author_id have to be the same");
    }


    public function test_get_books_fail_not_found(): void
    {
        $repository = $this->app->make(AuthorRepository::class);
        $bookRepository = $this->app->make(BookRepository::class);

        $input = new AuthorDTO(name: "Samsul", bio: "Novel writer", birth_date: Carbon::now());
        $authorResult = $repository->insert($input);

        $book = new BookDTO(
            title: "Dilan 8133 B.C",
            description: "Prequel Dilan mengenai nenek moyangnya Seorang Gigantopithecus",
            publish_date: Carbon::now(),
            author: new AuthorDTO(id: $authorResult->id)
        );

        $bookResult = $bookRepository->insert($book);

        $books = $repository->getBooks(11000);

        $this->assertTrue(count($books) == 0, "data must not be returned");
    }
}
