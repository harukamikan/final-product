<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanySelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // No user or no company → redirect to company creation
        if (!$user || !$user->company_id) {
            return redirect()->route('company.create');
        }
        
        // Has company but not onboarded → redirect to survey
        // (except if already on onboarding routes)
        if (!$user->onboarded_at && !$request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.survey');
        }
        
        return $next($request);
    }
}
