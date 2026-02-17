<?php

namespace Tests\Util;

use Illuminate\Support\Arr;

class Http
{

    public static function buildQuery(array $params, array $asFlatArray): string
    {
        if(empty($params)) return '';

        $queryString = http_build_query(Arr::except($params, $asFlatArray));

        foreach ($asFlatArray as $key) {
            if(!isset($params[$key])) continue;

            foreach ($params[$key] as $tagId) {
                $queryString .= '&'.$key.'[]=' . urlencode($tagId);
            }
        }

        return $queryString;
    }

}
