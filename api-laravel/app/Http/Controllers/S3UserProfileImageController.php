<?php

namespace App\Http\Controllers;

use App\Http\Requests\BasePreSignedUrlRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class S3UserProfileImageController extends Controller
{
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function __invoke(BasePreSignedUrlRequest $request)
    {
        $filename = $request->validated('filename');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        abort_if(!in_array($extension, self::ALLOWED_EXTENSIONS), 400, 'Filename sent is invalid');

        $storageKeyUuid = Uuid::getFactory()->uuid4()->toString();

        $options = [
            'Metadata' => [
                'user-id'     => (string) auth()->user()->id,
                'title'       => $filename,
                'storage-key-uuid' => $storageKeyUuid,
            ]
        ];

        $storageKey = sprintf("%s.%s", $storageKeyUuid, $extension);

        $storageResult = Storage::disk('profile-image')->temporaryUploadUrl(
            $storageKey,
            now()->addHour(),
            $options
        );

        /* @var User $user */
        $user = $request->user();
        $user->update([
            'new_photo_storage_hash' => $storageKeyUuid
        ]);

        return response()->json([
            'url' => $storageResult['url']
        ]);
    }
}
