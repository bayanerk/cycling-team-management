<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /**
     * Get all addresses for authenticated user
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            $addresses = Address::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'addresses' => $addresses->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'city' => $address->city,
                            'district' => $address->district,
                            'street' => $address->street,
                            'created_at' => $address->created_at,
                            'updated_at' => $address->updated_at,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting addresses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب العناوين',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Store a new address
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = $request->validated();
            $data['user_id'] = $user->id;

            $address = Address::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العنوان بنجاح',
                'data' => [
                    'address' => [
                        'id' => $address->id,
                        'city' => $address->city,
                        'district' => $address->district,
                        'street' => $address->street,
                        'created_at' => $address->created_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة العنوان',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update an address
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this address
            if ($address->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث هذا العنوان',
                ], 403);
            }

            $data = $request->validated();
            $address->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث العنوان بنجاح',
                'data' => [
                    'address' => [
                        'id' => $address->id,
                        'city' => $address->city,
                        'district' => $address->district,
                        'street' => $address->street,
                        'updated_at' => $address->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث العنوان',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete an address
     */
    public function destroy(Address $address): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this address
            if ($address->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف هذا العنوان',
                ], 403);
            }

            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العنوان بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف العنوان',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
