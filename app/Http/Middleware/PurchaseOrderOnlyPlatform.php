<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class PurchaseOrderOnlyPlatform
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $allowedPatterns = [
            'dashboard',
            'purchase-orders*',
            'ajax/purchase-orders*',
            'logout',
        ];

        foreach ($allowedPatterns as $pattern) {
            if (Str::is($pattern, $request->path())) {
                return $next($request);
            }
        }

        return redirect()->route('purchase.orders.index');
    }
}
