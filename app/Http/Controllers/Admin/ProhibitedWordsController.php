<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProhibitedWord;

class ProhibitedWordsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prohibitedWords = ProhibitedWord::where('company_id', auth()->user()->company_id)->get();
        return view('admin.prohibited_words.index', ['prohibited_words' => $prohibitedWords]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.prohibited_words.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $prohibitedWord = new ProhibitedWord;
        $prohibitedWord->fill($request->all());
        $prohibitedWord->company_id = auth()->user()->company_id;
        $prohibitedWord->save();

        return redirect()->route('admin.prohibited_words.index');
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
        $prohibitedWord = ProhibitedWord::find($id);
        return view('admin.prohibited_words.edit', ['prohibited_word' => $prohibitedWord]);
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
        $prohibitedWord = ProhibitedWord::find($id);
        $prohibitedWord->fill($request->all());
        $prohibitedWord->company_id = auth()->user()->company_id;
        $prohibitedWord->save();

        return redirect()->route('admin.prohibited_words.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prohibitedWord = ProhibitedWord::find($id);
        if ($prohibitedWord) {
            $prohibitedWord->delete();
        }
        return redirect()->route('admin.prohibited_words.index');
    }
    public function mass_store(ProhibitedWord $prohibited_word)
    {
        $companyId = Auth::user()->company_id;

        $childCompanies = DB::table('companies')
            ->where('parent_company_id', $companyId)
            ->get();
        foreach($childCompanies as $childCompany) {
            $newProhibited_words = $prohibited_word->replicate();
            $newProhibited_words->company_id = $childCompany->id;
            $newProhibited_words->save();
        }
    
        return redirect()->route('admin.prohibited_words.index');
    }
}
