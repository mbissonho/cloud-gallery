<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait GetsPerPageFromRequest
{
    /**
     * Get per page param from filtered request data
     *
     * @param Request $request
     * @return int
     */
    private function getPerPage(Request $request): int
    {
        $perPage = $request->has('per_page') ? $request->get('per_page') : 15;

        if($perPage > 15) {
            $perPage = 15;
        }

        return $perPage;
    }
}
