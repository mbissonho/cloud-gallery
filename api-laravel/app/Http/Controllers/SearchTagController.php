<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\TagSearchCollection;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchTagController extends Controller
{
    use GetsPerPageFromRequest;

    public function __invoke(SearchRequest $request): ResourceCollection
    {
        $builder = Tag::search($request->validated('query'));

        return new TagSearchCollection($builder->paginate($this->getPerPage($request))->withQueryString());
    }
}
