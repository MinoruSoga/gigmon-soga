<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Prompt;
use App\Models\Category;

class PromptsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // At system admin user, show all prompts
        //if (Auth::user()->role == 1) {
        //    $prompts = Prompt::all();
        //} else {
            $prompts = Auth::user()->company->prompts()->paginate(20);
        //}
        return view('admin.prompts.index', compact('prompts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.prompts.create', compact('categories'));
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

        if (Auth::user()->company->prompts()->count() >= Auth::user()->company->plan->max_prompts) {
            return redirect()->back()->withErrors(['prompt' => 'You have reached the maximum number of prompts for your plan.']);
        }

        $prompt = new Prompt($request->all());
        Auth::user()->company->prompts()->save($prompt);

        return redirect()->route('admin.prompts.index');
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
        $prompt = Prompt::find($id);
        $categories = Category::all();
        return view('admin.prompts.edit', ['prompt' => $prompt, 'categories' => $categories]);
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

        $prompt = Prompt::find($id);
        $prompt->fill($request->all());
        $prompt->save();

        return redirect()->route('admin.prompts.index');
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prompt = Prompt::find($id);
        if ($prompt) {
            $prompt->delete();
        }
        return redirect()->route('admin.prompts.index');
    }
    public function mass_store(Prompt $prompt)
    {
        $companyId = Auth::user()->company_id;

        $childCompanies = DB::table('companies')
            ->where('parent_company_id', $companyId)
            ->get();
        foreach($childCompanies as $childCompany) {
            $newPrompt = $prompt->replicate();
            $newPrompt->company_id = $childCompany->id;
            $newPrompt->save();
        }
    
        return redirect()->route('admin.prompts.index');
    }
}
