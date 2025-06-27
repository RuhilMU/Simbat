<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Master\Manufacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Manufactures",
 *     description="API Endpoints for managing drug manufacturers"
 * )
 */
class ManufactureController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/manufactures",
     *     tags={"Manufactures"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of manufacturers",
     *     description="Returns a paginated list of manufacturers with optional search functionality",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for manufacturer name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Manufactures retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *                 )),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Manufacture::query();

        // Search functionality
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $manufactures = $query->paginate($request->per_page ?? 15);

        // Load relationships manually
        $manufactures->getCollection()->transform(function ($manufacture) {
            $manufacture->drugs = $manufacture->drugs();
            return $manufacture;
        });

        return $this->successResponse($manufactures, 'Manufactures retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/manufactures",
     *     tags={"Manufactures"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new manufacturer",
     *     description="Creates a new manufacturer",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Pfizer Inc.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Manufacturer created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Manufacture created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:25|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $manufacture = Manufacture::create([
                'name' => $request->name
            ]);
            
            // Load relationships manually
            $manufacture->drugs = $manufacture->drugs();

            return $this->successResponse($manufacture, 'Manufacture created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create manufacture: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/manufactures/{id}",
     *     tags={"Manufactures"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get manufacturer details",
     *     description="Returns detailed information about a specific manufacturer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Manufacturer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Manufacture retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Manufacturer not found"
     *     )
     * )
     */
    public function show(Manufacture $manufacture)
    {
        // Load relationships manually
        $manufacture->drugs = $manufacture->drugs();

        return $this->successResponse($manufacture, 'Manufacture retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/manufactures/{id}",
     *     tags={"Manufactures"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update manufacturer details",
     *     description="Updates the details of an existing manufacturer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Manufacturer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Pfizer Inc.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Manufacturer updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Manufacture updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Manufacturer not found"
     *     )
     * )
     */
    public function update(Request $request, Manufacture $manufacture)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:25|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $manufacture->update($request->all());
            
            // Load relationships manually
            $manufacture->drugs = $manufacture->drugs();

            return $this->successResponse($manufacture, 'Manufacture updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update manufacture: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/manufactures/{id}",
     *     tags={"Manufactures"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a manufacturer",
     *     description="Deletes a manufacturer if it has no associated drugs",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Manufacturer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Manufacturer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Manufacture deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete manufacturer with existing drugs"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Manufacturer not found"
     *     )
     * )
     */
    public function destroy(Manufacture $manufacture)
    {
        try {
            // Check if manufacture has any drugs
            if (count($manufacture->drugs()) > 0) {
                return $this->errorResponse('Cannot delete manufacture with existing drugs', [], 422);
            }
    
            // Simpan data yang akan dihapus
            $deletedData = $manufacture->toArray();
    
            $manufacture->delete();
    
            return $this->successResponse($deletedData, 'Manufacture deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete manufacture: ' . $e->getMessage(), [], 500);
        }
    }
} 