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

        $user = auth()->user();

        $isPurchaseOrderOnlyUser = $user->can('access', 'purchase orders visible')
            && !$user->can('access', 'suppliers visible')
            && !$user->can('access', 'quotations visible')
            && !$user->can('access', 'projects visible')
            && !$user->can('access', 'users visible')
            && !$user->can('access', 'admins visible')
            && !$user->can('access', 'reports visible')
            && !$user->can('access', 'timesheets visible')
            && !$user->can('access', 'workflow visible');

        if (!$isPurchaseOrderOnlyUser) {
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
