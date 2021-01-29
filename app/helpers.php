<?php

if (! function_exists('exit_if')) {
    function exit_if(bool $condition, int $code = 1)
    {
        if ($condition) {
            exit($code);
        }
    }
}
