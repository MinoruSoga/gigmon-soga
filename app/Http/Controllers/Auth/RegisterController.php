<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

use App\Models\User;
use App\Models\Company;
use App\Models\Employee;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
            // User validation rules
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Company validation rules
            'company_name' => ['required', 'string', 'max:255'],
            'company_postal_code' => ['required', 'string', 'max:7'],
            'company_prefecture' => ['required', 'string', 'max:255'],
            'company_city' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:255'],
            'company_building' => ['nullable', 'string', 'max:255'],
            'company_phone_number' => ['required', 'string', 'max:255'],

            // Billing validation rules
            'accounting_email' => ['nullable', 'string', 'email', 'max:255'],

            // Agent validation rules
            'agency_code' => ['nullable', 'string', 'max:255'],
            'staff_code' => ['nullable', 'string', 'max:255'],

            // Terms validation rules
            'terms' => ['required', 'accepted'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();

        try {
            // Create new company
            $company = Company::create([
                'name' => $data['company_name'],
                'postal_code' => $data['company_postal_code'],
                'prefecture' => $data['company_prefecture'],
                'city' => $data['company_city'],
                'address' => $data['company_address'],
                'building' => $data['company_building'],
                'phone_number' => $data['company_phone_number'],
                'agency_code' => $data['agency_code'],
                'staff_code' => $data['staff_code'],
                'accounting_email' => $data['accounting_email'],
                'stripe_subscription_status' => 'trialing',
                'plan_id' => 1,    // Free Trial plan
            ]);

            // Create new user linked to the company
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 2, // Company's admin
                'company_id' => $company->id, // assuming users table has a company_id foreign key
            ]);

            // Create new employee
            $employee = new Employee;
            $employee->user_id = $user->id;
            $employee->company_id = $company->id;
            $employee->active = 1;
            $employee->save();

            DB::commit();

            try {
                if (env('SLACK_WEBHOOK_NEW_COMPANY_URL')) {
                    $client = new Client();
                    $response = $client->post(env('SLACK_WEBHOOK_NEW_COMPANY_URL'), [
                        'json' => [
                            'text' =>
                                '新たなアカウントが追加されました' . "\n" .
                                '法人名：' . $data['company_name'] . "\n" .
                                '管理者名：' . $data['name'] . "\n" .
                                '代理店コード：' . $data['agency_code'] . "\n" .
                                '担当者コード：' . $data['staff_code']
                        ]
                    ]);

                    if ($response->getStatusCode() == 200) {
                        //
                    } else {
                        //Log::info($response->getBody());
                    }
                }
            } catch (\Exception $e) {
                //Log::error($e->getMessage());
            }

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
   }
}
