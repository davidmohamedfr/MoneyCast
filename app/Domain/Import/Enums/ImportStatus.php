<?php

namespace App\Domain\Import\Enums;

enum ImportStatus: string
{
    case PENDING = 'pending';
    case MAPPING = 'mapping';
    case VALIDATING = 'validating';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
