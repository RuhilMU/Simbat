<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Master\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Variants",
 *     description="API Endpoints for managing drug variants"
 * )
 */
class VariantController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/variants",
     *     tags={"Variants"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of variants",
     *     description="Returns a paginated list of drug variants with optional search functionality",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for variant name",
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
     *             @OA\Property(property="message", type="string", example="Variants retrieved successfully"),
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
        $query = Variant::query();

        // Search functionality
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $variants = $query->paginate($request->per_page ?? 15);

        // Load relationships manually
        $variants->getCollection()->transform(function ($variant) {
            $variant->drugs = $variant->drugs();
            return $variant;
        });

        return $this->successResponse($variants, 'Variants retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/variants",
     *     tags={"Variants"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new variant",
     *     description="Creates a new drug variant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Tablet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Variant created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant created successfully"),
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
            $variant = Variant::create([
                'name' => $request->name
            ]);
            
            // Load relationships manually
            $variant->drugs = $variant->drugs();

            return $this->successResponse($variant, 'Variant created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create variant: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/variants/{id}",
     *     tags={"Variants"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get variant details",
     *     description="Returns detailed information about a specific variant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Variant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant retrieved successfully"),
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
     *         description="Variant not found"
     *     )
     * )
     */
    public function show(Variant $variant)
    {
        // Load relationships manually
        $variant->drugs = $variant->drugs();

        return $this->successResponse($variant, 'Variant retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/variants/{id}",
     *     tags={"Variants"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update variant details",
     *     description="Updates the details of an existing variant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Variant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Tablet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variant updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant updated successfully"),
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
     *         description="Variant not found"
     *     )
     * )
     */
    public function update(Request $request, Variant $variant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:25|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $variant->update($request->all());
            
            // Load relationships manually
            $variant->drugs = $variant->drugs();

            return $this->successResponse($variant, 'Variant updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update variant: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/variants/{id}",
     *     tags={"Variants"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a variant",
     *     description="Deletes a variant if it has no associated drugs",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Variant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variant deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Variant deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete variant with existing drugs"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Variant not found"
     *     )
     * )
     */
    public function destroy(Variant $variant)
    {
        try {
            // Check if variant has any drugs
            if (count($variant->drugs()) > 0) {
                return $this->errorResponse('Cannot delete variant with existing drugs', [], 422);
            }

            // Simpan data yang akan dihapus
            $deletedData = $variant->toArray();

            $variant->delete();

            return $this->successResponse($deletedData, 'Variant deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete variant: ' . $e->getMessage(), [], 500);
        }
    }
} 