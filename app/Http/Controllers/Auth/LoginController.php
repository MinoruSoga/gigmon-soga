<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Notifications\TwoFactorCode;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
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
    protected $redirectTo = '/home';

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('react.chat');
        }
        return view('auth.login');
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

    protected function authenticated(Request $request, $user)
    {
        // 子アカウントから切り替えて親アカウントにログインした場合
        if (session()->has('parent_user_id')) {

            Auth::loginUsingId(session('parent_user_id'));
            session()->forget('parent_user_id');
            return redirect('/companies'); // 親アカウントのダッシュボードへリダイレクト
        }

        $user->generateTwoFactorCode();
        $user->notify(new TwoFactorCode());
    }

    protected function attemptLogin(Request $request)
    {
        // リクエストから認証に必要な情報を取得
        $credentials = $request->only('email', 'password');

        // ユーザ認証を試行
        if (Auth::attempt($credentials)) {
            // ユーザがログインに成功した場合
            $user = Auth::user();

            if (!$user->employee) {
                Auth::logout(); // employeeが存在しないのでログアウトする
                throw ValidationException::withMessages(['email' => trans('auth.failed')]);
            }
            
            // employeeテーブルからユーザの有効性を確認
            if (!$user->employee->active || $user->employee->deleted_at) {
                Auth::logout(); // アカウント無効なのでログアウトする
                throw ValidationException::withMessages(['email' => trans('auth.inactive')]);
            }

            // 最終ログイン日時を更新
            $user->last_login_at = now();
            $user->save();

            return true; // ログイン成功
        }

        // ログインに失敗した場合
        // userテーブルにemailが存在するかチェック
        $userExists = User::where('email', $request->email)->exists();
        if (!$userExists) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        // パスワードが間違っている場合
        throw ValidationException::withMessages(['password' => trans('auth.password')]);

    }
    public function logout(Request $request)
    {
        $parentId = session('parent_user_id'); // 保持

        $this->guard()->logout();
        
        $request->session()->invalidate();
        
        $request->session()->regenerateToken();
    
        if($parentId) {
            session(['parent_user_id' => $parentId]); // 再セット
        }
    
        return $this->loggedOut($request) ?: redirect('/');
    }
}
