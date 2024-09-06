<?php

namespace App\DTO;

use Carbon\Carbon;

class AuthorDTO
{
    public int $id;
    public ?string $name;
    public ?string $bio;
    public ?Carbon $birth_date;

    public function __construct(int $id = 0, ?string $name = null, ?string $bio = null, ?Carbon $birth_date = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->bio = $bio;
        $this->birth_date = $birth_date;
    }
}
