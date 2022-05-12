<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ConfirmPassword  extends Constraint
{
    public $message = 'Password and confirmation must match.';
}
