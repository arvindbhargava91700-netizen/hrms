<?php



use Illuminate\Foundation\Application;

use Illuminate\Foundation\Configuration\Exceptions;

use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\SecureUpload;


return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(

        web: __DIR__.'/../routes/web.php',

        api: __DIR__.'/../routes/api.php',

        commands: __DIR__.'/../routes/console.php',

        health: '/up',

    )

    ->withMiddleware(function (Middleware $middleware): void {

        

        $middleware->redirectUsersTo(function () {



            if (auth()->check()) {

                return auth()->user()->dashboard_route;

            }



            return route('login');

        });



        $middleware->alias([

            'role'             => \App\Http\Middleware\RoleMiddleware::class,

            'requires_module'  => \App\Http\Middleware\RequiresModule::class,

            'force.json'       => \App\Http\Middleware\ForceJsonResponse::class,

            'api.auth'         => \App\Http\Middleware\EnsureApiAuthenticated::class,

            'partner.kyc.approved' => \App\Http\Middleware\EnsurePartnerKycApproved::class,

            'customer.kyc.approved' => \App\Http\Middleware\EnsureCustomerKycApproved::class,

            'requires_subscription' => \App\Http\Middleware\RequireActiveSubscription::class,

            'secure.upload' => SecureUpload::class,

        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {

        //

    })->create();

