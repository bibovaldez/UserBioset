<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageCaptureController extends Controller
{
    protected $imageRecognitionController;
    protected $encryptionController;

    public function __construct(ImageRecognitionController $imageRecognitionController, EncryptionController $encryptionController)
    {
        $this->imageRecognitionController = $imageRecognitionController;
        $this->encryptionController = $encryptionController;
    }

    public function upload(Request $request)
    {
        //validate image
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $uploadedFile = $request->file('image');
            $imageData = file_get_contents($uploadedFile->getRealPath());

            $encryptedImage = $this->encryptionController->encryptData($imageData);
            $compressedEncryptedImage = $this->compressAndEncode($encryptedImage);

            $recognitionResult = $this->imageRecognitionController->processImage($uploadedFile);
            $encryptedRecognitionResult = $this->encryptionController->encryptData(json_encode($recognitionResult));

            $this->storeImage($compressedEncryptedImage, $encryptedRecognitionResult);

            return response()->json(['message' => 'Image uploaded and processed successfully'], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Image upload failed'], 500);
        }
    }
    protected function storeImage(string $compressedEncryptedImage, string $encryptedRecognitionResult)
    {
        $image = new Image();
        $image->encrypted_image = $compressedEncryptedImage;
        $image->user_id = Auth::id();
        $image->building_id = Auth::user()->building_id;
        $image->recognition_result_encrypted = $encryptedRecognitionResult;
        $image->save();
    }

    private function compressAndEncode(string $data): string
    {
        return base64_encode(gzcompress($data, 9));
    }
}
