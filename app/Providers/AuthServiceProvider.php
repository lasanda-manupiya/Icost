<?php

namespace App\Providers;

use App\Role;
use App\Feature;
use App\RoleUser;
use App\CompanyFeature;
use App\PermissionRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\PurchaseManager\Entities\Purchase;
use Modules\PurchaseManager\Entities\Quotation;
use Modules\PurchaseManager\Policies\PurchasePolicy;
use Modules\PurchaseManager\Policies\QuotationPolicy;
use Illuminate\Foundation\Support\Providers\AuthService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Purchase::class => PurchasePolicy::class,
        Quotation::class => QuotationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        Gate::define('access', function ($user, $permission) {
            if (Str::startsWith($permission, 'purchase orders ')) {
                return true;
            }

            $role = RoleUser::whereHas('users', function($q) use ($user) {
                                $q->where('user_id', $user->id);
                            })->get()->first();

            if (isset($role->role_id)) {

                $company_id = ($role->role_id > 3) ? $user->company_id : (($role->role_id == 2) ? $user->id : 1);
                if ($company_id != 1) {
                    //for company & company users
                    $company_extra_features_id = CompanyFeature::where('company_id', $company_id)->pluck('feature_id', 'feature_id')->toArray();

                    $query = Feature::where('status', 1);
                    $query->where(function ($query)use($company_extra_features_id) {
                                $query->where('is_default', 1);
                                if (!empty($company_extra_features_id))
                                    $query->orWhereIn('id', $company_extra_features_id);
                            });
                    $company_features = $query->pluck('module', 'id')->toArray();
                    
                    $company_permission_count = PermissionRole::where('role_id', '=', $role->role_id)->where('company_id', '=', $company_id)->count();
                    $company_id = ($company_permission_count > 0) ? $company_id : 1;
                    
                    $hasPermissionOnUserRole = PermissionRole::whereHas('companies', function($q) use ($user, $permission, $company_id,$company_features) {
                                $q->where('id', $company_id);
                            })->whereHas('permissions', function($q) use ($permission,$company_features) {
                                $q->where('name', $permission);
                                if (!empty($company_features))
                                $q->whereIn('module', $company_features);
                            })->whereHas('roles', function($q) use ($role) {
                        $q->where('id', $role->role_id);
                    });
                }else{
                    //supplier
                    $hasPermissionOnUserRole = PermissionRole::whereHas('companies', function($q) use ($user, $permission, $company_id) {
                                $q->where('id', $company_id);
                            })->whereHas('permissions', function($q) use ($permission) {
                                $q->where('name', $permission);
                            })->whereHas('roles', function($q) use ($role) {
                        $q->where('id', $role->role_id);
                    });

                }     
                if ($hasPermissionOnUserRole->exists() === true) {
                    return true;
                }
            }
            return false;
        });
    }

}
