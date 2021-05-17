<?php

namespace App\Exceptions\Todo;

use Exception;
use App\Models\Utility\Todo;

class CouldNotUpdate extends Exception
{
    public static function isCompleted(Todo $todo): self
    {
        return new static("The todo with title `{$todo->title}` can't be updated, because it is already completed.");
    }
}
