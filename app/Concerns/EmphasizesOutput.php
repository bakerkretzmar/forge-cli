<?php

namespace App\Concerns;

trait EmphasizesOutput
{
    protected function emphasize(string $text): string
    {
        return "<options=bold;fg=blue>{$text}</options=bold;fg=blue>";
    }
}
