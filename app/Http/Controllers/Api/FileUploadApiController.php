<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FileUploadApiController extends Controller
{
    /**
     * Upload file (image, PDF, etc.)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:meter_photo,customer_pdf,invoice_pdf,profile_photo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $type = $request->type;

        // Determine storage path based on type
        $storagePath = match ($type) {
            'meter_photo' => 'meter_readings',
            'customer_pdf' => 'customer_pdfs',
            'invoice_pdf' => 'invoice_pdfs',
            'profile_photo' => 'profile_photos',
            default => 'uploads',
        };

        // Handle image compression for photos
        if (in_array($type, ['meter_photo', 'profile_photo'])) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $storagePath . '/' . $filename;

            // Compress and save image
            $image = Image::make($file);

            // Resize if too large
            if ($image->width() > 1920 || $image->height() > 1920) {
                $image->resize(1920, 1920, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save with compression
            Storage::disk('public')->put($path, (string) $image->encode(null, 85));

            $fullPath = 'storage/' . $path;
        } else {
            // Store PDF and other files normally
            $path = $file->store($storagePath, 'public');
            $fullPath = 'storage/' . $path;
        }

        return response()->json([
            'message' => 'Fayl yuklandi',
            'file' => [
                'path' => $fullPath,
                'url' => url($fullPath),
                'type' => $type,
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ],
        ], 201);
    }
}
