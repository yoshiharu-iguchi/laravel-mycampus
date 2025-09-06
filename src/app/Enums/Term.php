<?php

namespace App\Enums;

enum Term: int
{
    case First = 1;
    case Second = 2;
    case FullYear = 3;

public function transKey():string
{
    return 'enums.term' . strtolower($this->name);
}

public function label():string
{
    return __($this->transKey());
}

}