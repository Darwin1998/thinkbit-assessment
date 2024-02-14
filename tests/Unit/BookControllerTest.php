<?php
namespace Tests\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use Illuminate\Http\UploadedFile;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        Book::factory()->count(5)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);

        $response->assertViewIs('books.index');

        $response->assertViewHas('books');

        $this->assertCount(5, $response->original->getData()['books']);
    }

    public function testCreateBook()
    {
       $faker = \Faker\Factory::create();

       $fakeImage = UploadedFile::fake()->image('cover.jpg');

       $bookData = [
           'name' => $faker->name,
           'author' => $faker->name,
           'cover' => $fakeImage,
       ];

       $response = $this->post(route('books.store'), $bookData);

       $this->assertDatabaseHas('books', [
           'name' => $bookData['name'],
           'author' => $bookData['author'],
       ]);

       $response->assertStatus(302);
    }

    public function testUpdate()
    {
        $book = Book::factory()->create();
        $faker = \Faker\Factory::create();

        $fakeImage = UploadedFile::fake()->image('cover.jpg');
 
        $response = $this->patch("/books/update/{$book->id}", [
            'name' => 'Updated Book Name',
            'author' => 'Updated Author',
            'cover' => $fakeImage,
        ]);

        $response->assertStatus(302); 
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'name' => 'Updated Book Name',
            'author' => 'Updated Author',
        ]);
    }

    public function testDelete()
    {
        $book = Book::factory()->create();
        $response = $this->post("/books/delete/{$book->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function testExport()
    {
        $response = $this->get('/books/export');
        $response->assertStatus(200);
    }
}
