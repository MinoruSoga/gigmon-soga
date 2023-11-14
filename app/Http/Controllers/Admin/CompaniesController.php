<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Models\Plan;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role == 1) {
            $companies = Company::paginate(20);
       } else if(auth()->user()->role == 2){
            $currentUserCompanyId = auth()->user()->company_id;
            $companies = Company::where('parent_company_id', $currentUserCompanyId)->paginate(20);
       }
   
       return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $plans = Plan::all();
        return view('admin.companies.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $company = Company::create($request->all());

        return redirect()->route('admin.companies.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        $plans = Plan::all();
        $companies = Company::all(); // 全ての会社情報を取得
/*
        // Ensure the user is authorized to edit this employee
        if (Auth::user()->cannot('edit', $company)) {
            abort(403);
        }
*/

        return view('admin.companies.edit', compact('company', 'companies', 'plans'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $company->update($request->all());

        return redirect()->route('admin.companies.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::find($id);
        $company->delete();

        return redirect()->route('admin.companies.index');
    }

    public function deleteAllCompaniesMarkedAsDeleted()
    {
        $companies = Company::whereNotNull('deleted_at')->get();
        foreach ($companies as $company) {
            $company->delete();
        }
        return;
    }
    public function switchToChildAccount(Request $request, $childCompanyId)
    {

        // 子会社の最初のユーザを取得します
        $childUser = User::where('company_id', $childCompanyId)->where('role', 2)->first();

        if (!$childUser) {
            // 子会社にユーザが存在しないときの処理
            abort(404, 'Child company has no users');
        }
    
        // 現在ログインしている親会社のユーザIDをセッションに保存
        session(['parent_user_id' => auth()->id()]);
    
        // 子会社のユーザでログイン
        Auth::login($childUser);
    
        return redirect('/home');  // 子会社のダッシュボードなどへリダイレクト
    }
}
