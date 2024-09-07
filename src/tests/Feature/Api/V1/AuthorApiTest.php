<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    protected $uri = '/api/v1/authors';
    protected $errorResponseMessage = "Error response is not returning the right message.";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_index()
    {
        $authors = Author::factory(10)->create();
        $authorIds = $authors->map(fn($author) => $author->id);

        $response = $this->json('get', $this->uri);

        $response->assertStatus(200);

        $data = $response->json('data');
        collect($data)->each(fn($author) => $this->assertTrue(in_array($author['id'], $authorIds->toArray())));
    }


    public function test_index_bad_pagination()
    {
        $authors = Author::factory(10)->create();
        $authorIds = $authors->map(fn($author) => $author->id);

        $response = $this->json('get', $this->uri . '?page=10000&page_size=0');

        $response->assertStatus(422);

        $message = $response->json('message');
        $this->assertStringContainsStringIgnoringCase("The page size field must be at least 1", $message, $this->errorResponseMessage);
    }


    public function test_index_empty()
    {
        $authors = Author::factory(10)->create();
        $authorIds = $authors->map(fn($author) => $author->id);

        $response = $this->json('get', $this->uri . '?page=10000&page_size=1');

        $response->assertStatus(200);

        $data = $response->json('message');
        $this->assertEmpty($data, "Response data is not empty.");
    }



    public function test_show()
    {
        $dummy = Author::factory()->create();
        $response = $this->json('get', $this->uri . '/' . $dummy->id);

        $result = $response->assertStatus(200)->json('data');

        $this->assertEquals(data_get($result, 'id'), $dummy->id, 'Response ID not the same as model id.');
    }


    public function test_show_not_found()
    {
        $response = $this->json('get', $this->uri . '/10000');

        $message = $response->assertStatus(404)->json('message');

        $this->assertStringContainsStringIgnoringCase('author not found', $message, $this->errorResponseMessage);
    }


    public function test_show_bad_id()
    {
        $response = $this->json('get', $this->uri . '/null');

        $message = $response->assertStatus(422)->json('message');

        $this->assertStringContainsStringIgnoringCase('The id field must be a number', $message, $this->errorResponseMessage);
    }


    public function test_create()
    {

        $dummy = Author::factory()->make();

        $response = $this->json('post', $this->uri, $dummy->toArray());

        $result = $response->assertStatus(201)->json('data');

        $result = collect($result)->only(array_keys($dummy->getAttributes()));

        $result->each(function ($value, $field) use ($dummy) {
            $this->assertSame(data_get($dummy, $field), $value, 'Fillable is not the same.');
        });
    }


    public function test_create_empty_request()
    {
        $response = $this->json('post', $this->uri, []);

        $message = $response->assertStatus(422)->json('message');

        $this->assertStringContainsStringIgnoringCase('The name field is required. (and 2 more errors)', $message, $this->errorResponseMessage);
    }


    public function test_update()
    {
        $dummy = Author::factory()->create();
        $dummy2 = Author::factory()->make();

        $fillables = collect((new Author())->getFillable());

        $fillables->each(function ($toUpdate) use ($dummy, $dummy2) {
            $response = $this->json('patch', $this->uri . '/' . $dummy->id, [
                $toUpdate => data_get($dummy2, $toUpdate),
            ]);

            $result = $response->assertStatus(200)->json('data');
            $this->assertSame(data_get($dummy2, $toUpdate), data_get($result, $toUpdate), 'Failed to update model.');
        });
    }

    public function test_update_not_found()
    {
        $response = $this->json('patch', $this->uri . '/' . 2008, [
            'name' => 'Chewbacca',
        ]);

        $message = $response->assertStatus(404)->json('message');
        $this->assertStringContainsStringIgnoringCase('author not found', $message, $this->errorResponseMessage);
    }

    public function test_delete()
    {
        $dummy = Author::factory()->create();

        $response = $this->json('delete', $this->uri . '/' . $dummy->id);

        $result = $response->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);

        Author::query()->findOrFail($dummy->id);
    }


    public function test_delete_not_found()
    {
        $response = $this->json('delete', $this->uri . '/' . 56);

        $response->assertStatus(404);

        $this->expectException(ModelNotFoundException::class);

        Author::query()->findOrFail(56);
    }


    public function test_books()
    {
        $author = Author::factory()->create();
        $books = Book::factory(10)->create([
            'author_id' => $author->id
        ]);

        $bookIds = $books->map(fn($book) => $book->id);

        $response = $this->json('get', $this->uri . '/' . $author->id . '/books');

        $response->assertStatus(200);

        $data = $response->json('data');
        collect($data)->each(function ($book) use ($bookIds, $author) {
            $this->assertTrue(in_array($book['id'], $bookIds->toArray()), "Book ID not found in the created books.");
            $this->assertEquals($author->id, $book['author_id'], "Author ID does not match the user ID.");
        });
    }


    public function test_books_fake_author_id()
    {
        $author = Author::factory()->create();
        Book::factory(10)->create([
            'author_id' => $author->id
        ]);

        $response = $this->json('get', $this->uri . '/' . 34 . '/books');

        $response->assertStatus(404);
    }


    public function test_books_empty_param()
    {
        $author = Author::factory()->create();
        Book::factory(10)->create([
            'author_id' => $author->id
        ]);

        $response = $this->json('get', $this->uri . '//books');

        $response->assertStatus(404);
    }
}
