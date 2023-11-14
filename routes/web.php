<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('react.chat');
    } else {
        return view('auth.login');
    }
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::get('/react-chat', [App\Http\Controllers\SpaController::class, 'index'])->name('react.chat')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::get('/react-prompts', [App\Http\Controllers\SpaController::class, 'index'])->name('react.prompts')->middleware('auth');
Route::get('/test-chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat')->middleware('auth');
Route::get('/force-error', [App\Http\Controllers\ChatController::class, 'error'])->name('chat.error')->middleware('auth');

Route::get('/env', function () {
    return response()->json([
        'MIX_STRIPE_PUBLIC_KEY' => env('MIX_STRIPE_PUBLIC_KEY'),
        'MIX_STRIPE_RETURN_URL' => env('MIX_STRIPE_RETURN_URL'),
    ]);
});;

// Admin/Employee
Route::resource('employees', App\Http\Controllers\Admin\EmployeesController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::patch('/admin/employees/{employee}/deactivate', [App\Http\Controllers\Admin\EmployeesController::class, 'deactivate'])->name('admin.employees.deactivate')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::patch('/admin/employees/{employee}/activate', [App\Http\Controllers\Admin\EmployeesController::class, 'activate'])->name('admin.employees.activate')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::get('/admin/employees/{employee}/conversations/{year?}/{month?}', [App\Http\Controllers\Admin\EmployeesController::class, 'conversations'])->name('admin.employees.conversations')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::get('/admin/employees/import', [App\Http\Controllers\Admin\EmployeesController::class, 'importForm'])->name('admin.employees.importForm')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::post('/admin/employees/preview', [App\Http\Controllers\Admin\EmployeesController::class, 'importPreview'])->name('admin.employees.importPreview')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::post('/admin/employees/import', [App\Http\Controllers\Admin\EmployeesController::class, 'import'])->name('admin.employees.import')->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::get('/admin/employees/export', [App\Http\Controllers\Admin\EmployeesController::class, 'export'])->name('admin.employees.export')->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/Companies
Route::resource('companies', App\Http\Controllers\Admin\CompaniesController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/Prompts
Route::resource('prompts', App\Http\Controllers\Admin\PromptsController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::post('/admin/prompts/{prompt}/mass_store', 'App\Http\Controllers\Admin\PromptsController@mass_store')
    ->name('admin.prompts.mass_store');
// Admin/Plans
Route::resource('plans', App\Http\Controllers\Admin\PlansController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/Accounts
Route::resource('accounts', App\Http\Controllers\Admin\AccountsController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/ProhibitedWords
Route::resource('prohibited_words', App\Http\Controllers\Admin\ProhibitedWordsController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);
Route::post('/admin/promprohibited_wordspts/{prohibited_word}/mass_store', 'App\Http\Controllers\Admin\ProhibitedWordsController@mass_store')
    ->name('admin.prohibited_words.mass_store');
// Admin/Resources
Route::resource('resources', App\Http\Controllers\Admin\ResourcesController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/Payments
Route::get('/payments', [App\Http\Controllers\SpaController::class, 'index'])->name('react.payments')->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Admin/Notifications
Route::resource('notifications', App\Http\Controllers\Admin\NotificationsController::class, ['as' => 'admin'])->middleware(['auth', 'verified', 'twofactor', 'checkip']);

// Mypage
Route::middleware(['auth', 'verified', 'twofactor', 'checkip'])->group(function () {
    Route::get('/mypage', 'App\Http\Controllers\UserController@mypage')->name('mypage');
    Route::post('/mypage', 'App\Http\Controllers\UserController@mypageUpdate')->name('mypage.update');
});

// Mycompany
Route::middleware(['auth', 'verified', 'twofactor', 'checkip'])->group(function () {
    Route::get('/mycompany', 'App\Http\Controllers\CompanyController@mycompany')->name('mycompany');
    Route::post('/mycompany', 'App\Http\Controllers\CompanyController@mycompanyUpdate')->name('mycompany.update');
});

Route::get('/switch/child/{childCompanyId}', 'App\Http\Controllers\Admin\CompaniesController@switchToChildAccount')->name('switch.child');
// IP restriction
Route::middleware(['auth', 'verified', 'twofactor', 'checkip'])->group(function () {
    Route::get('/companies/{company}/security', 'App\Http\Controllers\CompanyController@ipEdit')->name('admin.companies.security');
    Route::post('/companies/{company}/security/delete', 'App\Http\Controllers\CompanyController@ipDelete')->name('admin.companies.security.delete');
    Route::post('/companies/{company}/security/update', 'App\Http\Controllers\CompanyController@ipUpdate')->name('admin.companies.security.update');
});

// Contact
Route::middleware(['auth', 'verified', 'twofactor', 'checkip'])->group(function () {
    Route::get('/contact', 'App\Http\Controllers\ContactController@showForm')->name('contact');
    Route::post('/contact', 'App\Http\Controllers\ContactController@submitForm')->name('contact.submit');
});

// Email Verification
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 2FA
Route::get('verify/resend', 'App\Http\Controllers\Auth\TwoFactorController@resend')->name('verify.resend');
Route::resource('verify', 'App\Http\Controllers\Auth\TwoFactorController')->only(['index', 'store'])->middleware('checkip');

// Language
Route::get('/switch-language/{language}', 'App\Http\Controllers\LanguageController@switchLanguage')->name('switch-language');;

// API used from Chat
Route::get('/api/prompts/{type}/{category?}', 'App\Http\Controllers\Api\PromptsController@index')->middleware('auth');
Route::get('/api/categories', 'App\Http\Controllers\Api\PromptsController@categories')->middleware('auth');
Route::post('/api/callgpt', 'App\Http\Controllers\Api\GptController@index')->middleware('auth');
Route::post('/api/checkMessage', 'App\Http\Controllers\Api\GptController@checkMessage')->middleware('auth');
Route::post('/api/generateToken', 'App\Http\Controllers\Api\GptController@index')->middleware('auth');
Route::post('/api/calldocs', 'App\Http\Controllers\Api\DocsbotController@index')->middleware('auth');
Route::get('/api/history/{mode}', 'App\Http\Controllers\Api\GptController@history')->middleware('auth');
Route::post('/api/hideHistory', 'App\Http\Controllers\Api\GptController@hideHistory')->middleware('auth');
Route::post('/api/showHistory', 'App\Http\Controllers\Api\GptController@showHistory')->middleware('auth');
Route::get('/api/speed', 'App\Http\Controllers\Api\GptController@speed')->middleware('auth');
Route::post('/api/callgptstream', 'App\Http\Controllers\Api\GptStreamController@index')->middleware('auth');
Route::post('/api/calldocsstream', 'App\Http\Controllers\Api\DocsbotStreamController@index')->middleware('auth');
Route::get('/api/statusdoc', 'App\Http\Controllers\Api\DocsbotController@statusdoc')->middleware('auth');
Route::get('/api/isResourceRegistered', 'App\Http\Controllers\Api\DocsbotController@isResourceRegistered')->middleware('auth');
// API used for Payments
Route::get('/api/payments', 'App\Http\Controllers\Api\PaymentsController@index')->middleware('auth');
Route::post('/api/payments/createSubscription', 'App\Http\Controllers\Api\PaymentsController@createSubscription')->middleware('auth');
Route::post('/api/payments/cancelSubscription', 'App\Http\Controllers\Api\PaymentsController@cancelSubscription')->middleware('auth');
Route::post('/api/payments/createSetupIntent', 'App\Http\Controllers\Api\PaymentsController@createSetupIntent')->middleware('auth');
Route::post('/api/payments/resetPaymentMethod', 'App\Http\Controllers\Api\PaymentsController@resetPaymentMethod')->middleware('auth');
Route::post('/api/payments/unsubscribe', 'App\Http\Controllers\Api\PaymentsController@unsubscribe')->middleware('auth');

Route::post('/api/webhook-stripe', 'App\Http\Controllers\Api\PaymentsController@handleStripeWebhook');

Route::get('/api/user/plan', 'App\Http\Controllers\UserController@getPlanId')->middleware(['auth', 'verified', 'twofactor']);

/*
Route::get('_phpmyinfo', function () {
    phpinfo();
})->name('_phpmyinfo');
*/

// Route for the cron to hit every minute:
Route::get('/scheduler', function (){
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
});

// Catch all route any non defined route will be handled by react single page app
Route::get('/{any}', [App\Http\Controllers\SpaController::class, 'index'])->where('any', '.*')->middleware(['auth', 'verified', 'twofactor']);
