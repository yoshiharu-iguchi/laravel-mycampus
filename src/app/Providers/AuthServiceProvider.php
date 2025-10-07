<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Subject;
use App\Policies\SubjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Subject::class => SubjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::policy(Subject::class,SubjectPolicy::class);
    }
}
