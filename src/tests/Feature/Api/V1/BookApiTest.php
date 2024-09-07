<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    protected $uri = '/api/v1/books';
    protected $errorResponseMessage = "Error response is not returning the right message.";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_index()
    {
        $author = Author::factory()->create();
        $books = Book::factory(20)->create([
            'author_id' => $author->id,
        ]);

        $bookIds = $books->map(fn($book) => $book->id);

        $response = $this->json('get', $this->uri);

        $response->assertStatus(200);

        $data = $response->json('data');
        collect($data)->each(fn($book) => $this->assertTrue(in_array($book['id'], $bookIds->toArray())));
    }


    public function test_index_no_author()
    {

        $books = Book::factory(20)->create([
            'author_id' => null,
        ]);

        $bookIds = $books->map(fn($book) => $book->id);

        $response = $this->json('get', $this->uri);

        $response->assertStatus(200);

        $data = $response->json('data');
        collect($data)->each(fn($book) => $this->assertTrue(in_array($book['id'], $bookIds->toArray())));
    }


    public function test_index_bad_pagination()
    {
        $response = $this->json('get', $this->uri . '?page=&page_size=');

        $response->assertStatus(422);

        $message = $response->json('message');
        $this->assertStringContainsStringIgnoringCase("The page size field must be a number", $message, $this->errorResponseMessage);
    }


    public function test_index_empty()
    {
        $response = $this->json('get', $this->uri . '?page=10000&page_size=1');

        $response->assertStatus(200);

        $data = $response->json('message');
        $this->assertEmpty($data, "Response data is not empty.");
    }


    public function test_show()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create([
            'author_id' => $author->id,
        ]);

        $response = $this->json('get', $this->uri . '/' . $book->id);

        $result = $response->assertStatus(200)->json('data');

        // Assert book fields
        $this->assertEquals($book->id, data_get($result, 'id'), 'Response ID not the same as model ID.');
        $this->assertEquals($book->title, data_get($result, 'title'), 'Response title not the same as model title.');
        $this->assertEquals($book->description, data_get($result, 'description'), 'Response description not the same as model description.');
        $this->assertEquals($book->publish_date, data_get($result, 'publish_date'), 'Response publish date not the same as model publish date.');

        // Assert author fields
        $this->assertEquals($book->author_id, data_get($result, 'author.id'), 'Response author ID not the same as model author ID.');
        $this->assertEquals($author->name, data_get($result, 'author.name'), 'Response author name not the same as model author name.');
        $this->assertEquals($author->bio, data_get($result, 'author.bio'), 'Response author bio not the same as model author bio.');
    }

    public function test_show_no_author()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create([
            'author_id' => null,
        ]);

        $response = $this->json('get', $this->uri . '/' . $book->id);

        $result = $response->assertStatus(200)->json('data');

        // Assert book fields
        $this->assertEquals($book->id, data_get($result, 'id'), 'Response ID not the same as model ID.');
        $this->assertEquals($book->title, data_get($result, 'title'), 'Response title not the same as model title.');
        $this->assertEquals($book->description, data_get($result, 'description'), 'Response description not the same as model description.');
        $this->assertEquals($book->publish_date, data_get($result, 'publish_date'), 'Response publish date not the same as model publish date.');

        // Assert author fields
        $this->assertEquals($book->author_id, data_get($result, 'author.id'), 'Response author ID not the same as model author ID.');
        $this->assertEquals(null, data_get($result, 'author.name'), 'Response author name not the same as model author name.');
        $this->assertEquals(null, data_get($result, 'author.bio'), 'Response author bio not the same as model author bio.');
    }

    public function test_show_not_found()
    {
        $response = $this->json('get', $this->uri . '/10000');

        $message = $response->assertStatus(404)->json('message');

        $this->assertStringContainsStringIgnoringCase('book not found', $message, $this->errorResponseMessage);
    }


    public function test_show_bad_id()
    {
        $response = $this->json('get', $this->uri . '/null');

        $message = $response->assertStatus(422)->json('message');

        $this->assertStringContainsStringIgnoringCase('The id field must be a number', $message, $this->errorResponseMessage);
    }


    public function test_create()
    {

        $author = Author::factory()->create();
        $book = Book::factory()->make([
            'author_id' => $author->id
        ]);

        $response = $this->json('post', $this->uri, $book->toArray());

        $result = $response->assertStatus(201)->json('data');

        $result = collect($result)->only(array_keys($book->getAttributes()));

        $result->each(function ($value, $field) use ($book) {
            $this->assertSame(data_get($book, $field), $value, 'Fillable is not the same.');
        });
    }


    public function test_create_no_author()
    {
        $book = Book::factory()->make([
            'author_id' => null
        ]);

        $response = $this->json('post', $this->uri, $book->toArray());

        $message = $response->assertStatus(422)->json('message');

        $this->assertStringContainsStringIgnoringCase('The author id field is required', $message, $this->errorResponseMessage);
    }


    public function test_update()
    {
        $author = Author::factory()->create();
        $dummy = Book::factory()->create([
            'author_id' => $author->id
        ]);
        $dummy2 = Book::factory()->make([
            'author_id' => $author->id
        ]);

        $fillables = collect((new Book())->getFillable());

        $fillables->each(function ($toUpdate) use ($dummy, $dummy2) {
            $response = $this->json('patch', $this->uri . '/' . $dummy->id, [
                $toUpdate => data_get($dummy2, $toUpdate),
            ]);

            $result = $response->assertStatus(200)->json('data');
            $this->assertSame(data_get($dummy2, $toUpdate), data_get($result, $toUpdate), 'Failed to update model.');
        });
    }

    public function test_update_no_author()
    {
        $author = Author::factory()->create();
        $dummy = Book::factory()->create([
            'author_id' => null
        ]);
        $dummy2 = Book::factory()->make([
            'author_id' => null
        ]);

        $fillables = collect((new Book())->getFillable());

        $fillables->each(function ($toUpdate) use ($dummy, $dummy2) {
            if ($toUpdate != 'author_id') {
                $response = $this->json('patch', $this->uri . '/' . $dummy->id, [
                    $toUpdate => data_get($dummy2, $toUpdate),
                ]);

                $result = $response->assertStatus(200)->json('data');
                $this->assertSame(data_get($dummy2, $toUpdate), data_get($result, $toUpdate), 'Failed to update model.');
            }
        });
    }

    public function test_update_not_found()
    {
        $response = $this->json('patch', $this->uri . '/' . 2008, [
            'title' => 'Chewbacca',
        ]);

        $message = $response->assertStatus(404)->json('message');
        $this->assertStringContainsStringIgnoringCase('book not found', $message, $this->errorResponseMessage);
    }

    public function test_delete()
    {
        $author = Author::factory()->create();
        $dummy = Book::factory()->create([
            'author_id' => $author->id,
        ]);

        $response = $this->json('delete', $this->uri . '/' . $dummy->id);

        $result = $response->assertStatus(200)->json('message');

        $this->expectException(ModelNotFoundException::class);

        Book::query()->findOrFail($dummy->id);
    }


    public function test_delete_not_found()
    {
        $response = $this->json('delete', $this->uri . '/' . 56);

        $response->assertStatus(404);

        $this->expectException(ModelNotFoundException::class);

        Book::query()->findOrFail(56);
    }
}
