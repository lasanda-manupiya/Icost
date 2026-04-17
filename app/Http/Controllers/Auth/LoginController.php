<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';


    /**
     * Get the post-login redirect path.
     *
     * @return string
     */
    public function redirectTo()
    {
        if (!auth()->check()) {
            return '/dashboard';
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

        return $isPurchaseOrderOnlyUser ? '/purchase-orders' : '/dashboard';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $title = 'Login';
        return view('auth.login', compact('title'));
    }

    /**
     * customize the guard.
     *
     * @return object
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function login(Request $request)
    {
        
        $this->validate($request, [
            'email'           => 'required|max:255|email',
            'password'           => 'required',
        ]);
        
        if ($this->attemptLogin($request)) {
            
            if(!auth()->user()->isRole('Super Admin')){
                $check_status = \DB::table('users')->where('email', $request->email)->first();
                if ($check_status != null && $check_status->start_date != 0 && $check_status->end_date != 0) {
                    
                    $paymentDate = date('Y-m-d');
                    $paymentDate=date('Y-m-d', strtotime($paymentDate));
                    
                    $contractDateBegin =$check_status->start_date;
                    $contractDateEnd = $check_status->end_date;
                
                    if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                        
                        $user = $this->guard('admin')->user();
                        if ($request->remember) {
                            \Cookie::queue('user_login_email', $request->email);
                            \Cookie::queue('user_login_password', $request->password);
                            \Cookie::queue('user_login_remember', true);
                        } else {
                            \Cookie::queue(\Cookie::forget('user_login_email'));
                            \Cookie::queue(\Cookie::forget('user_login_password'));
                            \Cookie::queue('user_login_remember', false);
                        }
                        return $this->sendLoginResponse($request);
                    }else{
                        Auth::logout();
                        return redirect('/login');
                    }
                }
            }else{
                $user = $this->guard('admin')->user();
                if ($request->remember) {
                    \Cookie::queue('user_login_email', $request->email);
                    \Cookie::queue('user_login_password', $request->password);
                    \Cookie::queue('user_login_remember', true);
                } else {
    
                    \Cookie::queue(\Cookie::forget('user_login_email'));
                    \Cookie::queue(\Cookie::forget('user_login_password'));
                    \Cookie::queue('user_login_remember', false);
                }
                return $this->sendLoginResponse($request);
                
            }
        }
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);
    }
}
