<?php

namespace App\Enums;

enum Term: int
{
    case First = 1;
    case Second = 2;
    case FullYear = 3;

public function transKey():string
{
    return 'enums.term.' . strtolower($this->name);
}

public function label():string
{
    return __($this->transKey());
}

public static function fromLabel(string $label):self
{
    $map = trans('enrollment.term');
    $value = array_search($label,$map,true);
    if ($value === false) {
        throw new \ValueError("Unknown term label: {$label}");
    }
    return self::from((int)$value);
}

}