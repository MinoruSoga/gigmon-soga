<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function mycompany()
    {
        // 法人情報
        $company = Auth::user()->company;

        // 利用状況を月毎にまとめる
        $usages = [];
        $wdate = strtotime($company->created_at);
        do {
            $str = date('Y/m', $wdate);
            $usages[$str] = [
                'chatgpt' => 0,
                'docsbot' => 0
            ];

            $sql =
                " SELECT c.conversation_system_id, SUM(CHAR_LENGTH(message) + CHAR_LENGTH(response)) AS all_length " .
                "   FROM conversations c " .
                "        LEFT JOIN users u ON c.user_id = u.id " .
                "  WHERE u.company_id = " . Auth::user()->company_id .
                "    AND DATE_FORMAT(c.created_at, '%Y/%m') = '" . $str . "' " .
                "  GROUP BY c.conversation_system_id " .
                "  ORDER BY c.conversation_system_id ";
            $res = DB::select($sql);
            foreach ($res as $r) {
                if ($r->conversation_system_id === 1) {
                    $usages[$str]['chatgpt'] = $r->all_length;
                } elseif ($r->conversation_system_id === 2) {
                    $usages[$str]['docsbot'] = $r->all_length;
                }
            }

            $wdate = strtotime('+1 month', $wdate);
        } while ($str < date('Y/m'));

        return view('mycompany', [
            'company' => $company,
            'usages'  => $usages
        ]);
    }

    public function mycompanyUpdate(Request $request)
    {
/*
        $user = Auth::user();
        $request->validate([
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|same:password',
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

    #    if($user->email !== $request->email){
    #        $user->email_verified_at = null;
    #        $user->email = $request->email;
    #        $user->save();
    #        $user->sendEmailVerificationNotification();
    #    } else{
            $user->save();
    #    }

        return redirect()->route('mypage')->with('success', __('mypage.Profile updated successfully'));
*/

        $company = Auth::user()->company;
        $request->validate([
            'postal_code' => 'required',
            'prefecture' => 'required',
            'city' => 'required',
            'address' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $company->postal_code = $request->postal_code;
            $company->prefecture = $request->prefecture;
            $company->city = $request->city;
            $company->address = $request->address;
            $company->building = $request->building;
            $company->accounting_email = $request->accounting_email;
            $company->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withErrors(['error' => 'Failed to update company: ' . $e->getMessage()]);
        }
        return redirect()->route('mycompany')->with('success', __('company.Company updated successfully'));
    }

    public function ipEdit(Company $company)
    {
        $ipAddresses = $company->ipAddresses()->pluck('ip_address')->all();
        return view('ipEdit', compact('company', 'ipAddresses'));
    }

    public function ipUpdate(Request $request,$companyId)
    {

        $company = Company::find($companyId);

        // バリデーションや保存のロジックを書く
        $data = $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);

        // 古いIPアドレスを削除
        $company->ipAddresses()->delete();

        // 新しいIPアドレスを保存
        foreach ($data['ip_addresses'] as $ip) {
            $company->ipAddresses()->create(['ip_address' => $ip]);
        }

        return redirect()->route('admin.companies.security', $company)->with('success', 'IPアドレスを更新しました。');
    }
    public function ipDelete(Request $request,$company)
    {

        $deletedIps = $request->get('ip_address');
        
        DB::table('company_ip_addresses')
        ->where('company_id', $company)
        ->where('ip_address', $deletedIps)
        ->delete();
    
        return response()->json(['message' => 'IPアドレスが正常に削除されました。'], 200);
    }
}