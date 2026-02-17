<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreSignedUrlRequest;
use App\Models\Image;
use App\Models\ImageStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class S3ImageUploadController extends Controller
{
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function __invoke(PreSignedUrlRequest $request)
    {
        $filename = $request->validated('filename');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        abort_if(!in_array($extension, self::ALLOWED_EXTENSIONS), 400, 'Filename sent is invalid');

        //TODO: Apply some sort of upload limit here

        $storageKeyUuid = Uuid::getFactory()->uuid4()->toString();

        $options = [
            'Metadata' => [
                'user-id'     => (string) auth()->user()->id,
                'title'       => $request->validated('file_title'),
                'storage-key-uuid' => $storageKeyUuid,
                'comma-separated-tags-ids' => Arr::join($request->validated('file_tag_ids') ?? [], ','),
                'description' => $request->validated('file_description')
            ]
        ];

        //Check if already exist the image that user is trying perform upload
        $userId = auth()->user()->id;

        $alreadySavedImage = Image::query()
            ->where('user_id', $userId)
            ->where('title',  $request->validated('file_title'))
            ->where('status', ImageStatus::PROCESSING)
            ->get()
            ->first();

        if($alreadySavedImage) {
            $storageResult = Storage::disk('main-image')->temporaryUploadUrl(
                $alreadySavedImage->storage_key,
                now()->addHour(),
                $options
            );

            return response()->json([
                'url' => $storageResult['url']
            ]);
        }

        $storageKey = sprintf("%s.%s", $storageKeyUuid, $extension);

        $storageResult = Storage::disk('main-image')->temporaryUploadUrl(
            $storageKey,
            now()->addHour(),
            $options
        );

        /* @var Image $image */
        $image = Image::create([
            'user_id' => auth()->user()->id,
            'storage_key' => $storageKey,
            'title' => $request->validated('file_title'),
            'description' => $request->validated('file_description')
        ]);

        $image->tags()->attach($request->validated('file_tag_ids') ?? []);
        $image->searchable();

        return response()->json([
            'url' => $storageResult['url']
        ]);
    }
}
