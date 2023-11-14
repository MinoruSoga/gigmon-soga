<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Notification;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role == 1) {
            $notifications = Notification::orderBy('created_at', 'desc')->paginate(20);
        }else{
            $today = now()->toDateString();
            $notifications = Notification::where(function ($query) use ($today) {
                $query->whereNull('display_from')
                ->orWhere('display_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('display_to')
                ->orWhere('display_to', '>=', $today);
            })
            ->orderBy('priority_flag', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        }


        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.notifications.create');
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
            'title' => 'required',
            'content' => 'required',
        ]);
        Notification::create($request->all());
        return redirect()->route('admin.notifications.index');
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
        $notification = Notification::find($id);
        return view('admin.notifications.edit', ['notification' => $notification]);
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
            'title' => 'required',
            'content' => 'required',
        ]);
        $notification = Notification::find($id);
        $data = $request->all();
        // チェックボックスがチェックされていない場合、priority_flagをfalseに設定
        $data['priority_flag'] = $request->has('priority_flag');
        $notification->fill($data);
        $notification->save();
        return redirect()->route('admin.notifications.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->delete();
        }
        return redirect()->route('admin.notifications.index');
    }
}
