<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Company;
use App\Models\Employee;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.accounts.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::all();
        return view('admin.accounts.create', ['companies' => $companies]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:1,2,3',
            'company_id' => 'required|exists:companies,id',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new user with the request data and save it to the database
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = bcrypt($request->password);
            $user->role = $request->role;
            $user->company_id = $request->company_id;
            $user->save();

            // Create a new employee with the request data and save it to the database
            $employee = new Employee;
            $employee->user_id = $user->id; // Assign the user id to the employee
            $employee->company_id = Auth::user()->company_id; // Set the company_id to the current logged-in user's company
            $employee->save();

            // If everything is okay, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction
            DB::rollback();

            // Redirect the user back with an error message
            return redirect()->back()->withErrors(['error' => 'Failed to create new account: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.accounts.index');
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
        $user = User::find($id);
        $companies = Company::all();
        return view('admin.accounts.edit', ['user' => $user, 'companies' => $companies]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:1,2,3',
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->company_id = $request->company_id;
        if($request->password){
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->route('admin.accounts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return redirect()->route('admin.accounts.index');
    }
}
