<?php

namespace App\Http\Controllers;

use App\DTO\BookDTO;
use App\DTO\AuthorDTO;
use App\DTO\FilterDTO;
use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\GetBookRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\ShowBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Requests\ValidateIdRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookWithAuthorResource;
use App\Services\Book\BookService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookController extends Controller
{
    protected BookService $service;

    public function __construct(BookService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $filterDTO = FilterDTO::fromRequest($request);
        $result = $this->service->getAll($filterDTO);
        return BookResource::collection($result['books'])
            ->additional(['metadata' => $result['metadata']])
            ->response()
            ->setStatusCode(200);
    }


    /**
     * Store a newly created resource in storage.
     * 
     * @param CreateBookRequest $request
     * @return JsonResponse
     */
    public function store(CreateBookRequest $request): JsonResponse
    {

        $bookDTO = new BookDTO(
            title: $request->title,
            description: $request->description,
            publish_date: Carbon::parse($request->publish_date),
            author: new AuthorDTO(id: $request->author_id)
        );

        return (new BookResource($this->service->create($bookDTO)))
            ->response()
            ->setStatusCode(201)
            ->header('Location', sprintf('/api/v1/books/%d', $bookDTO->id));
    }

    /**
     * Get the specified resource by id.
     * 
     * @param ShowBookRequest $id
     * @return JsonResource
     */
    public function find(ValidateIdRequest $request): JsonResource
    {
        return new BookWithAuthorResource($this->service->getByIdWithAuthor($request->id));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request)
    {
        $bookDTO = new BookDTO(
            id: $request->id,
            title: $request->title,
            description: $request->description,
            author: $request->author_id == null ? null : new AuthorDTO(id: $request->author_id),
            publish_date: $request->publish_date == null ? null : Carbon::parse($request->publish_date)
        );
        return new BookResource($this->service->update($bookDTO));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ValidateIdRequest $request)
    {
        $this->service->delete($request->id);
        return response()->json([
            'message' => 'book successfully deleted'
        ], 200);
    }
}
