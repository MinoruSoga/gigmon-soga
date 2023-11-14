<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Employee;
use App\Models\User;
use App\Models\Conversation;
use Mail;
use App\Mail\NotificationMail;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        if ($search) {
            // 検索条件をセッションに保存
            session(['search' => $search]);
        } else {
            session()->forget('search');
        }

        // リクエストにパラメータが存在しない場合は、セッションから'sort'の値を取得する。
        // セッションにも値が存在しない場合はデフォルト値を設定する
        $sort = $request->input('sort') ?? session()->get('sort', 'employees.id');
        $direction = $request->input('direction') ?? session()->get('direction', 'asc');

        session(['sort' => $sort, 'direction' => $direction]);

        // Fetch all employees belonging to the company of the logged-in user
        $employees =
            Employee::select(
                'employees.id as id',
                'users.id as user_id',
                'users.name as name',
                'users.email as email',
                'users.role as role',
                'employees.active as active',
                DB::raw('SUM(CHAR_LENGTH(conversations.message)) + SUM(CHAR_LENGTH(conversations.response)) AS words_count')
            )
            ->where('employees.company_id', Auth::user()->company_id)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->leftJoin('conversations', function($join) {
                $join
                    ->on('conversations.user_id', '=', 'users.id')
                    ->on(DB::raw('MONTH(conversations.created_at)'), '=', DB::raw(date('m')));
            })
            ->where(function($query) use ($search) {
                $query->orwhere('users.name', 'like', '%' . $search . '%')
                ->orwhere('users.email', 'like', '%' . $search . '%');
            })
            ->groupBy('employees.id', 'users.id', 'users.name', 'users.email', 'users.role', 'employees.active')
            ->orderBy($sort, $direction)
            ->paginate(20);

        // Return the index view with the list of employees
        return view('admin.employees.index', ['employees' => $employees, 'search' => $search, 'sort' => $sort, 'direction' => $direction]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new user with the request data and save it to the database
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = Hash::make($request->password); // Hash the password before saving
            $user->company_id = Auth::user()->company_id;
            $user->role = $request->role;
            $user->save();

            // Create a new employee with the request data and save it to the database
            $employee = new Employee;
            $employee->user_id = $user->id; // Assign the user id to the employee
            $employee->company_id = Auth::user()->company_id; // Set the company_id to the current logged-in user's company
            $employee->save();
            
            // $admin = Auth::user(); // 現在ログインしている管理者
            $admins = User::where('role', 2)->where('company_id', Auth::user()->company_id)->get();


            Mail::to($request->email)->send(new NotificationMail($user, $request->password, $admins));


            // If everything is okay, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction
            DB::rollback();

            // Redirect the user back with an error message
            return redirect()->back()->withErrors(['error' => 'Failed to create new employee: ' . $e->getMessage()]);
        }

        // Redirect the user back to the employee list
        return redirect()->route('admin.employees.index', ['search' => session('search')]);
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
        $employee = Employee::findOrFail($id);

/*
        // Ensure the user is authorized to edit this employee
        if (Auth::user()->cannot('edit', $employee)) {
            abort(403);
        }
*/

        return view('admin.employees.edit', compact('employee'));
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
        $employee = Employee::findOrFail($id);

        // Ensure the user is authorized to update this employee
    #    if (Auth::user()->cannot('update', $employee)) {
    #        abort(403);
    #    }

        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|string|min:8',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the user's data
            $user = $employee->user;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password); // Hash the password before saving
            }
            $user->save();

            // If everything is okay, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction
            DB::rollback();

            // Redirect the user back with an error message
            return redirect()->back()->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }

        // Redirect the user back to the employee list
        return redirect()->route('admin.employees.index', ['search' => session('search')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $employee->delete();
        }
        return redirect()->route('admin.employees.index');
    }

    public function activate(Employee $employee)
    {
        // Activate the employee
        $employee->active = true;
        $employee->save();

        // Redirect back to the employee list
        return redirect()->route('admin.employees.index');
    }

    public function deactivate(Employee $employee)
    {
        // Deactivate the employee
        $employee->active = false;
        $employee->save();

        // Redirect back to the employee list
        return redirect()->route('admin.employees.index');
    }

    public function conversations(Employee $employee, $year = null, $month = null)
    {
        if ($employee->company_id != Auth::user()->company_id) {
            return redirect()->route('admin.employees.index');
        }

        $date = Carbon::now();
        if ($year && $month) {
            $date = Carbon::create($year, $month, 1);
        }

        // 該当年月の利用状況を取得
        $chatgpt = 0;
        $docsbot = 0;
        $sql =
            " SELECT c.conversation_system_id, SUM(CHAR_LENGTH(c.message) + CHAR_LENGTH(c.response)) AS all_length " .
            "   FROM conversations c " .
            "        LEFT JOIN users u ON c.user_id = u.id " .
            "  WHERE u.id = " . $employee->user_id .
            "    AND DATE_FORMAT(c.created_at, '%Y%m') = '" . $date->format('Ym') . "' " .
            "  GROUP BY c.conversation_system_id " .
            "  ORDER BY c.conversation_system_id ";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if ($r->conversation_system_id === 1) {
                $chatgpt = $r->all_length;
            } elseif ($r->conversation_system_id === 2) {
                $docsbot = $r->all_length;
            }
        }

        // 該当年月の会話履歴を取得
        $conversation_rows =
            Conversation::whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->where('conversations.user_id', $employee->user_id)
            ->orderBy('id', 'asc')
            ->get();
        $conversations = [];
        foreach ($conversation_rows as $row) {
            if ($row->role === 'user') {
                $conversations[] = [
                    'message'    => $row->message,
                    'response'   => $row->response,
                    'created_at' => $row->created_at,
                ];
            } else if ($row->role === 'function') {
                $conversations[count($conversations) - 1]['response'] = $row->response;
            }
        }

        return view('admin.employees.conversations', [
            'conversations' => $conversations,
            'chatgpt'       => $chatgpt,
            'docsbot'       => $docsbot,
            'date'          => $date,
            'employee'      => $employee,
        ]);
    }

    public function importForm()
    {
        return view('admin.employees.importForm');
    }

    public function importPreview(Request $request)
    {
        $file = $request->file('csv_file');

        // ファイルの基本バリデーション
        $validator = Validator::make(
            ['csv_file' => $file],
            ['csv_file' => 'required|mimes:csv,txt']
        );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $headers = array_shift($data);

        $errors = [];
        $validData = [];
        $errorCount = 0;
        foreach ($data as $row) {
            $email = $row[0];
            $password = $row[1];
            $name = $row[2];
            $user = User::where('email', $email)->first();
            if ($user) {
                if ($user->company_id != Auth::user()->company_id) {
                    $errors[] = "{$email} は異なる会社に所属しています。";
                    $errorCount++;
                } else {
                    $validData[] = [
                        'email' => $email,
                        'password' => $password,
                        'name' => $name
                    ];
                }
            } else {
                $validData[] = [
                    'email' => $email,
                    'password' => $password,
                    'name' => $name
                ];
            }
        }

        return view('admin.employees.importPreview', [
            'csv_errors' => $errors,
            'validData' => $validData,
            'errorCount' => $errorCount
        ]);
    }

    public function import(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $importedCount = 0;

        // Start a database transaction
        DB::beginTransaction();

        try {
            foreach ($data as $rowData) {
                $user = User::where('email', $rowData['email'])->first();
                if ($user) {
                    if (!empty($rowData['password'])) {
                        $user->password = Hash::make($rowData['password']);
                    }
                    $user->name = $rowData['name'];
                    $user->save();
                } else {
                    $user = User::create([
                        'email' => $rowData['email'],
                        'password' => Hash::make($rowData['password']),
                        'name' => $rowData['name'],
                        'email_verified_at' => now(),
                        'role' => 3,
                        'company_id' => Auth::user()->company_id
                    ]);

                    // Create a new employee with the request data and save it to the database
                    $employee = new Employee;
                    $employee->user_id = $user->id; // Assign the user id to the employee
                    $employee->company_id = Auth::user()->company_id; // Set the company_id to the current logged-in user's company
                    $employee->save();
                }
                $importedCount++;
            }
            // If everything is okay, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction
            DB::rollback();

            // Redirect the user back with an error message
            return redirect()->route('admin.employees.importForm')->withErrors(['error' => 'Failed to create new employee: ' . $e->getMessage()]);
        }
        return redirect()->route('admin.employees.importForm')->with('success', __('employee.Imported Success', ['count' => $importedCount]));

    }

    public function export() {
        $filename = sys_get_temp_dir() . "/users.csv";
        $handle = fopen($filename, 'w+');
        fputcsv($handle, ['メールアドレス', 'パスワード', '名前']);

        $users = User::where('company_id', Auth::user()->company_id)->get();

        foreach ($users as $user) {
            fputcsv($handle, [$user->email, '', $user->name]);
        }

        fclose($handle);

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $filenameToDownload = "users.csv";

        return Response::download($filename, $filenameToDownload, $headers)->deleteFileAfterSend(true);
    }

}
