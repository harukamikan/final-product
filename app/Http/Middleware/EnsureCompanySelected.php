<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanySelected
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->company_id) {
            return redirect()->route('company.create'); // 会社作成画面
        }
        return $next($request);
    }
}
