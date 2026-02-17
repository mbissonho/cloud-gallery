<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchImagesRequest;
use App\Http\Resources\ImageSearchCollection;
use App\Models\Image;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchController extends Controller
{
    use GetsPerPageFromRequest;

    public function __invoke(SearchImagesRequest $request): ResourceCollection
    {
        $builder = Image::search($request->validated('query'))
            ->where('status', 'AVAILABLE');

        if($request->has('tag_id'))
            $builder->whereIn('tag_ids', [$request->validated('tag_id')]);

        return new ImageSearchCollection($builder->paginate($this->getPerPage($request))->withQueryString());
    }
}
