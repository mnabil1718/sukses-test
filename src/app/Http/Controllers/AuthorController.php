<?php

namespace App\Http\Controllers;

use App\DTO\AuthorDTO;
use App\Http\Requests\CreateAuthorRequest;
use App\Http\Requests\DeleteAuthorRequest;
use App\Http\Requests\ShowAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Services\Author\AuthorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    protected AuthorService $service;

    public function __construct(AuthorService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * 
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return AuthorResource::collection($this->service->getAll());
    }


    /**
     * Store a newly created resource in storage.
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
     * Get the specified resource by id.
     * 
     * @param ShowAuthorRequest $id
     * @return JsonResource
     */
    public function find(ShowAuthorRequest $request): JsonResource
    {
        return new AuthorResource($this->service->getById($request->id));
    }


    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteAuthorRequest $request)
    {
        $this->service->delete($request->id);
        return response()->json([
            'message' => 'author successfully deleted'
        ], 200);
    }
}
