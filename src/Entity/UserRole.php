<?php

namespace App\Enum;

enum UserRole: string
{
    case PATIENT = 'PATIENT';
    case PSYCHIATRIST = 'PSYCHIATRIST';
    case ADMIN = 'ADMIN';
}