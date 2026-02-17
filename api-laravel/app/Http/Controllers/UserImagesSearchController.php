<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchImagesRequest;
use App\Http\Resources\UserImageSearchCollection;
use App\Models\Image;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserImagesSearchController extends Controller
{
    use GetsPerPageFromRequest;

    public function __invoke(SearchImagesRequest $request): ResourceCollection
    {
        $builder = Image::search($request->validated('query'))
            ->where('user_id', auth()->user()->id)
            ->orderByDesc('created_at');

        if($request->has('tag_id'))
            $builder->whereIn('tag_ids', [$request->validated('tag_id')]);

        return new UserImageSearchCollection($builder->paginate($this->getPerPage($request))->withQueryString());
    }
}
