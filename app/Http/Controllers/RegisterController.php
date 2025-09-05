<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    private const CLOUDINARY_FOLDER = 'users_documents';
    private $cloudinary;

    public function __construct(private PaymentController $paymentController)
    {
        $this->paymentController = $paymentController;

        // Initialize Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->cloudinary = new Cloudinary();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /** 
     * Register.
     *  @requestMediaType multipart/form-data
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();
            // Handle file uploads if documents are provided using the private method
            if ($request->hasFile('documents')) {
                $documents = $request->file('documents');
                $filePaths = $this->uploadDocuments($documents);
                $validated['documents'] = $filePaths;
            }

            //generate user password
            $password = Str::random(10);
            $validated['password'] = $password;

            // Initialize payment instead of creating user directly
            $paymentRequest = new Request([
                'email' => $validated['email'],
                'user_data' => $validated
            ]);

            return $this->paymentController->initializePayment($paymentRequest);
        } catch (Exception $e) {
            logger($e);
            return response()->json(
                [
                    'status' => false,
                    'message' => 'An error has occured, please contact the admin',
                    'error' => $e->getMessage()
                ],
                500
            );
        }
    }

    private function cloundinaryDocumentsUpload(array $documents)
    {
        try {
            return array_map(function ($document) {
                $folderName = self::CLOUDINARY_FOLDER;

                $uploadResponse = $this->cloudinary->uploadApi()->upload(
                    $document->getRealPath(),
                    [
                        'folder' => $folderName,
                        'resource_type' => 'auto'
                    ]
                );

                return $uploadResponse['secure_url'];
            }, $documents);
        } catch (Exception $e) {
            logger('error from the upload function', [$e]);
            throw new Exception('Document upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple documents to storage
     */
    private function uploadDocuments($documents): array
    {
        $urls = [];

        foreach ($documents as $document) {
            $extension = $document->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = $document->storeAs('documents/users', $filename, 'public');

            $urls[] = Storage::disk('public')->url($path);
        }

        return $urls;
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
