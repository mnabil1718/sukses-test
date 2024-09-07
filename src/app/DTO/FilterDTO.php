<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class FilterDTO
{
    public ?int $page;
    public ?int $pageSize;

    public function __construct(?int $page = 1, ?int $pageSize = 10,)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
    }


    /**
     * Create DTO instance from FormRequest object
     *
     * @param FormRequest $request
     * @return self
     */
    public static function fromRequest(FormRequest $request): self
    {
        return new self($request->page ?? 1, $request->page_size ?? 10);
    }

    /**
     * Get limit for pagination
     *
     * @return integer
     */
    public function limit(): int
    {
        return $this->pageSize;
    }

    /**
     * Calculate offset for pagination
     *
     * @return integer
     */
    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
