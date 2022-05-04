<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsAgent extends Constraint
{
    public $message = 'The string "{{ string }}" is not a valid user agent.';
}
