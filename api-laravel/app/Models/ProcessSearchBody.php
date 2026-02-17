<?php

namespace App\Models;

use Laravel\Scout\Builder;

trait ProcessSearchBody
{
    abstract function processBody(Builder $builder, array $body, array $options = []): array;
}
