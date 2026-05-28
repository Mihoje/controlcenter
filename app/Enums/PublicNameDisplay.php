<?php

namespace App\Enums;

enum PublicNameDisplay: string
{
    case FullName = 'full_name';
    case FirstName = 'first_name';
    case CID = 'cid_only';
}
