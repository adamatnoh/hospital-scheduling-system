<?php

namespace App\Enums;

enum RunStatus: string
{
    case Preview = 'preview';
    case Published = 'published';
}
