<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    /**
     * Show the image upload form.
     */
    public function showUploadForm()
    {
        return view('upload');
    }

    /**
     * Handle image upload and classification.
     */
    public function uploadImage(Request $request)
    {
        // Validate the incoming request for the image
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',  // Max file size 2MB
        ]);

        // Check if the uploaded file is valid
        if ($request->file('file')->isValid()) {
            // Store the uploaded image in the 'images' directory of public storage
            $path = $request->file('file')->store('images', 'public');

            // Call classifyImage method to send the image for classification
            $classification = $this->classifyImage($path);

            // Return a JSON response with the result
            return response()->json([
                'success' => 'Image uploaded and classified successfully!',
                'path' => $path,
                'classification' => $classification
            ]);
        }

        // If the upload fails, return an error response
        return response()->json(['error' => 'Failed to upload image'], 400);
    }

    /**
     * Classify the uploaded image by sending it to the Node.js server.
     *
     * @param string $imagePath
     * @return array
     */
    public function classifyImage($imagePath)
    {
        // Node.js API endpoint where the image will be classified
        $nodeApiUrl = 'http://localhost:3000/classify';  // Change to your Node.js server URL

        try {
            // Get the image content and convert it to base64 format
            $imageContent = file_get_contents(storage_path('app/public/' . $imagePath));
            $imageBase64 = base64_encode($imageContent);

            // Send the base64-encoded image to the Node.js server for classification
            $response = Http::post($nodeApiUrl, [
                'imageBase64' => $imageBase64,
            ]);

            // Check if the response is successful and return the classification result
            if ($response->successful()) {
                return $response->json();
            }

            // Log an error if the classification fails
            Log::error('Classification failed: ' . $response->body());
            return ['error' => 'Classification failed'];
        } catch (\Exception $e) {
            // Log the exception message if something goes wrong
            Log::error('Error during classification: ' . $e->getMessage());
            return ['error' => 'Classification encountered an error'];
        }
    }
}
