<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Master\Drug;
use App\Models\Master\Repack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Repacks",
 *     description="API Endpoints for managing drug repacks"
 * )
 */
class RepackController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/repacks",
     *     tags={"Repacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of repacks",
     *     description="Returns a paginated list of drug repacks with optional search and filtering",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for repack name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="drug_id",
     *         in="query",
     *         description="Filter by drug ID",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *             @OA\Property(property="message", type="string", example="Repacks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="number"),
     *                     @OA\Property(property="margin", type="number"),
     *                     @OA\Property(property="price", type="number"),
     *                     @OA\Property(property="drug", type="object"),
     *                     @OA\Property(property="stock", type="object")
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
        $query = Repack::query();

        // Search functionality
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by drug_id if provided
        if ($request->has('drug_id')) {
            $query->where('drug_id', $request->drug_id);
        }

        $repacks = $query->paginate($request->per_page ?? 15);

        // Load relationships manually
        $repacks->getCollection()->transform(function ($repack) {
            $repack->drug = $repack->drug();
            $repack->stock = $repack->stock();
            return $repack;
        });

        return $this->successResponse($repacks, 'Repacks retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/repacks",
     *     tags={"Repacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new repack",
     *     description="Creates a new drug repack with calculated price based on drug's base price and margin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"drug_id", "quantity", "piece_unit", "margin"},
     *             @OA\Property(property="drug_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="number", format="float", example=10),
     *             @OA\Property(property="piece_unit", type="string", enum={"pack", "pcs"}, example="pack"),
     *             @OA\Property(property="margin", type="number", format="float", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Repack created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repack created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="quantity", type="number"),
     *                 @OA\Property(property="margin", type="number"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="drug", type="object"),
     *                 @OA\Property(property="stock", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or drug has no price set"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|numeric|min:1',
            'piece_unit' => 'required|in:pack,pcs',
            'margin' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $drug = Drug::findOrFail($request->drug_id);
            
            // Check if drug has last_price and last_discount
            if ($drug->last_price == 0) {
                return $this->errorResponse('Cannot create repack: Drug has no price set', [], 422);
            }
            
            // Calculate quantity based on piece_unit
            $quantity = $request->quantity;
            if ($request->piece_unit === 'pcs') {
                $quantity = $request->quantity * $drug->piece_netto;
            }

            $repack = Repack::create([
                'drug_id' => $drug->id,
                'name' => $drug->name . ' ' . $request->quantity . ' ' . $request->piece_unit,
                'quantity' => $quantity,
                'margin' => $request->margin,
                'price' => $drug->calculate_price($quantity, $request->margin)
            ]);

            // Load relationships manually
            $repack->drug = $repack->drug();
            $repack->stock = $repack->stock();

            return $this->successResponse($repack, 'Repack created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create repack: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/repacks/{id}",
     *     tags={"Repacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get repack details",
     *     description="Returns detailed information about a specific repack",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Repack ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repack retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="quantity", type="number"),
     *                 @OA\Property(property="margin", type="number"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="drug", type="object"),
     *                 @OA\Property(property="stock", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repack not found"
     *     )
     * )
     */
    public function show(Repack $repack)
    {
        // Load relationships manually
        $repack->drug = $repack->drug();
        $repack->stock = $repack->stock();

        return $this->successResponse($repack, 'Repack retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/repacks/{id}",
     *     tags={"Repacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update repack details",
     *     description="Updates the margin and recalculates the price of an existing repack",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Repack ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"margin"},
     *             @OA\Property(property="margin", type="number", format="float", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Repack updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repack updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="quantity", type="number"),
     *                 @OA\Property(property="margin", type="number"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="drug", type="object"),
     *                 @OA\Property(property="stock", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or drug has no price set"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repack not found"
     *     )
     * )
     */
    public function update(Request $request, Repack $repack)
    {
        $validator = Validator::make($request->all(), [
            'margin' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $drug = $repack->drug();
            
            // Check if drug has last_price and last_discount
            if ($drug->last_price == 0) {
                return $this->errorResponse('Cannot update repack: Drug has no price set', [], 422);
            }

            $repack->update([
                'margin' => $request->margin,
                'price' => $drug->calculate_price($repack->quantity, $request->margin)
            ]);

            // Load relationships manually
            $repack->drug = $repack->drug();
            $repack->stock = $repack->stock();

            return $this->successResponse($repack, 'Repack updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update repack: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/repacks/{id}",
     *     tags={"Repacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a repack",
     *     description="Deletes a repack if it is not a default repack and has no existing stock",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Repack ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Repack deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repack deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="quantity", type="number"),
     *                 @OA\Property(property="margin", type="number"),
     *                 @OA\Property(property="price", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete default repack or repack with existing stock"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Repack not found"
     *     )
     * )
     */
    public function destroy(Repack $repack)
    {
        try {
            // Check if repack is default (1 pack or 1 pcs)
            $drug = $repack->drug();
            if ($repack->quantity === $drug->piece_quantity * $drug->piece_netto || 
                $repack->quantity === $drug->piece_netto) {
                return $this->errorResponse('Cannot delete default repack', [], 422);
            }

            // Check if repack has stock
            if ($repack->stock() > 0) {
                return $this->errorResponse('Cannot delete repack with existing stock', [], 422);
            }

            // Simpan data yang akan dihapus
            $deletedData = $repack->toArray();

            $repack->delete();

            return $this->successResponse($deletedData, 'Repack deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete repack: ' . $e->getMessage(), [], 500);
        }
    }
} 