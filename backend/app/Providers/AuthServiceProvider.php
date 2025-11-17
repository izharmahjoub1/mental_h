<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Patient;
use App\Policies\PatientPolicy;
use App\Models\Alert;
use App\Policies\AlertPolicy;
use App\Models\Questionnaire;
use App\Policies\QuestionnairePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Patient::class => PatientPolicy::class,
        Alert::class => AlertPolicy::class,
        Questionnaire::class => QuestionnairePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

