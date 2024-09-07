<?php

namespace App\DTO;

use App\DTO\AuthorDTO;
use Carbon\Carbon;

class BookDTO
{
    public int $id;
    public ?string $title;
    public ?string $description;
    public ?Carbon $publish_date;
    public ?AuthorDTO $author;

    public function __construct(int $id = 0, ?string $title = null, ?string $description = null, ?Carbon $publish_date = null, ?AuthorDTO $author = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->publish_date = $publish_date;
        $this->author = $author;
    }
}
