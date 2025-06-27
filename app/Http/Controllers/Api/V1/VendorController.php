<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Master\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Vendors",
 *     description="API Endpoints for managing vendors"
 * )
 */
class VendorController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/vendors",
     *     summary="Get all vendors",
     *     tags={"Vendors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for vendor name, phone, or address",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendors retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Vendor Name"),
     *                     @OA\Property(property="phone", type="string", example="081234567890"),
     *                     @OA\Property(property="address", type="string", example="Vendor Address")
     *                 )),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        // Search functionality
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        $vendors = $query->paginate($request->per_page ?? 15);

        return $this->successResponse($vendors, 'Vendors retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendors",
     *     summary="Create a new vendor",
     *     tags={"Vendors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Vendor Name"),
     *             @OA\Property(property="phone", type="string", maxLength=14, example="081234567890"),
     *             @OA\Property(property="address", type="string", maxLength=255, example="Vendor Address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Vendor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendor created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Vendor Name"),
     *                 @OA\Property(property="phone", type="string", example="081234567890"),
     *                 @OA\Property(property="address", type="string", example="Vendor Address"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create vendor")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:25|string',
            'phone' => 'required|max:14',
            'address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $vendor = Vendor::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address
            ]);
            
            return $this->successResponse($vendor, 'Vendor created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create vendor: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/{vendor}",
     *     summary="Get vendor details",
     *     tags={"Vendors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendor retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Vendor Name"),
     *                 @OA\Property(property="phone", type="string", example="081234567890"),
     *                 @OA\Property(property="address", type="string", example="Vendor Address"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found"
     *     )
     * )
     */
    public function show(Vendor $vendor)
    {
        return $this->successResponse($vendor, 'Vendor retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendors/{vendor}",
     *     summary="Update vendor details",
     *     tags={"Vendors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=25, example="Updated Vendor Name"),
     *             @OA\Property(property="phone", type="string", maxLength=14, example="081234567890"),
     *             @OA\Property(property="address", type="string", maxLength=255, example="Updated Vendor Address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendor updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Vendor Name"),
     *                 @OA\Property(property="phone", type="string", example="081234567890"),
     *                 @OA\Property(property="address", type="string", example="Updated Vendor Address"),
     *                 @OA\Property(property="drugs", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to update vendor")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:25|string',
            'phone' => 'required|max:14',
            'address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $vendor->update($request->all());

            return $this->successResponse($vendor, 'Vendor updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update vendor: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendors/{vendor}",
     *     summary="Delete a vendor",
     *     tags={"Vendors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vendor",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendor deleted successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Vendor Name"),
     *                 @OA\Property(property="phone", type="string", example="081234567890"),
     *                 @OA\Property(property="address", type="string", example="Vendor Address")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete vendor with existing drugs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cannot delete vendor with existing drugs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to delete vendor")
     *         )
     *     )
     * )
     */
    public function destroy(Vendor $vendor)
    {
        try {
            // Check if vendor has any drugs using the relationship directly
            if ($vendor->drugs && $vendor->drugs->count() > 0) {
                return $this->errorResponse('Cannot delete vendor with existing drugs', [], 422);
            }

            // Simpan data yang akan dihapus
            $deletedData = $vendor->toArray();

            $vendor->delete();

            return $this->successResponse($deletedData, 'Vendor deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete vendor: ' . $e->getMessage(), [], 500);
        }
    }
} 