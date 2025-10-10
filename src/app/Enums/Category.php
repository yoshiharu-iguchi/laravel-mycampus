<?php

namespace App\Enums;

enum Category: string
{
    case Required = 'required';
    case Elective = 'elective';

    public function label(): string
    {
        return match ($this) {
            self::Required => '必修',
            self::Elective => '選択',
        };
    }

    public static function fromLabel(string|int|bool $label):self
    {
        $v = is_string($label) ? mb_strtolower(trim($label)) : $label;

        return match ($v) {
            '必修','required','compulsory','core',1,'1',true => self::Required,
            '選択','elective','optional',0,'0',false => self::Elective,
            default => throw new \ValueError("Unknown category label:{$label}"),
        };
    }

    
}