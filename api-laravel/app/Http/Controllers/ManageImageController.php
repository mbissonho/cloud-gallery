<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditImageRequest;
use App\Models\Image;

class ManageImageController extends Controller
{
    public function delete(Image $image)
    {
        abort_if(auth()->user()->id !== $image->user_id, 404, trans('http.404'));

        $image->delete();

        return response(status: 204);
    }

    public function edit(Image $image, EditImageRequest $request)
    {
        abort_if(auth()->user()->id !== $image->user_id, 404, trans('http.404'));

        $image->tags()->detach();
        $image->tags()->attach($request->validated('tag_ids') ?? []);
        $image->update($request->validated());


        return response(status: 200);
    }

}
