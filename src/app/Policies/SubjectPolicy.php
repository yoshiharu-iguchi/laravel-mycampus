<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\Teacher;

class SubjectPolicy
{
    public function view(Teacher $user, Subject $subject): bool
    {
        return $subject->teacher_id === $user->id;
    }
}