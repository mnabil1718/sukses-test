<?php

namespace App\Http\Controllers;

use App\DTO\AuthorDTO;
use App\DTO\FilterDTO;
use App\Http\Requests\CreateAuthorRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\ShowAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Requests\ValidateIdRequest;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\BookResource;
use App\Services\Author\AuthorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorController extends Controller
{
    protected AuthorService $service;

    public function __construct(AuthorService $service)
    {
        $this->service = $service;
    }

    /**
     * List all authors with pagination.
     * 
     * @return JsonResponse
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $filterDTO = FilterDTO::fromRequest($request);
        $result = $this->service->getAll($filterDTO);
        return AuthorResource::collection($result['authors'])
            ->additional(['metadata' => $result['metadata']])
            ->response()
            ->setStatusCode(200);
    }


    /**
     * Create new author.
     * 
     * @param CreateAuthorRequest $request
     * @return JsonResponse
     */
    public function store(CreateAuthorRequest $request): JsonResponse
    {

        $authorDTO = new AuthorDTO(
            name: $request->name,
            bio: $request->bio,
            birth_date: Carbon::parse($request->birth_date)
        );
        return (new AuthorResource($this->service->create($authorDTO)))
            ->response()
            ->setStatusCode(201)
            ->header('Location', sprintf('/api/v1/authors/%d', $authorDTO->id));
    }

    /**
     * Get author by id.
     * 
     * @param ShowAuthorRequest $id
     * @return JsonResource
     */
    public function find(ValidateIdRequest $request): JsonResource
    {
        return new AuthorResource($this->service->getById($request->id));
    }


    /**
     * Partial update on author by id.
     * 
     * @param UpdateAuthorRequest $request
     */
    public function update(UpdateAuthorRequest $request)
    {
        $authorDTO = new AuthorDTO(
            id: $request->id,
            name: $request->name,
            bio: $request->bio,
            birth_date: $request->birth_date == null ? null : Carbon::parse($request->birth_date)
        );
        return new AuthorResource($this->service->update($authorDTO));
    }

    /**
     * Delete author by id.
     * 
     * @param ValidateIdRequest $request
     */
    public function destroy(ValidateIdRequest $request)
    {
        $this->service->delete($request->id);
        return response()->json([
            'message' => 'author successfully deleted'
        ], 200);
    }


    /**
     * Get books by author id
     *
     * @param ValidateIdRequest $request
     * @return void
     */
    public function books(ValidateIdRequest $request)
    {
        return BookResource::collection($this->service->getBooks($request->id));
    }
}
