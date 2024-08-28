<?php

namespace App\Enums;

enum CabinClass: string
{
    case Y = 'Economy';
    case S = 'Premium Economy';
    case C = 'Business';
    case J = 'Premium Business';
    case F = 'First';
    case P = 'Premium First';
}