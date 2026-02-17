<?php

namespace App\Models;

enum ImageStatus: string
{
    case PROCESSING = 'PROCESSING';
    case AVAILABLE = 'AVAILABLE';
    case DISABLED = 'DISABLED';
}
