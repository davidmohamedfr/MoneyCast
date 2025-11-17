<?php

namespace App\Domain\Import\Enums;

enum ImportSource: string
{
    case CSV = 'csv';
    case OFX = 'ofx';
    case QFX = 'qfx';
    case API = 'api';
}
