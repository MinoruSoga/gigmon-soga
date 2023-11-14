<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Company;

class ResourcesController extends Controller
{
    private $baseUrl = 'https://docsbot.ai/api/teams/:teamId/bots/:botId/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 社内ナレッジリンクを押下されたとき、ログイン者名を取得し、usersテーブルでログイン者のcompany_idを取得し、
        // companiesテーブルでログイン者のcompany_idを探してその会社のdocsbot_team_id・docsbot_bot_id・docsbot_api_keyを取得し、
        // 3つのカラムの中で1つでもNULLがあった場合警告ポップを出す

        // ログインしているユーザーの所属する会社のIDを取得
        $id = Auth::user()->company_id;

        // データベース内のCompanyモデルから該当の会社のレコードを取得
        $company = Company::where('id', $id)->first();

        if(is_null($company->docsbot_team_id) || is_null($company->docsbot_bot_id) || is_null($company->docsbot_api_key)){
            return redirect()->back()->with('warning',  __('resource.UNREGISTERED KNOWLEDGE SETTING'));
        }
      
        $resources = []; // リソースを格納する空の配列を作成

        // リソースを取得
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $company->docsbot_api_key,
        ];
        $resources = [];
        try {
            $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources");
            $response = Http::withHeaders($headers)->timeout(20)->get($url);
            // $resources = $response->json();
            $resources = $response->json('sources');
//Log::Info($resources);
            if (!$resources) {
                $resources = [];
            }
        } catch (ConnectionException $e) {
//          Log::error($e->getMessage());
        }

        $total_source_pages = \DB::table('companies')->where('id', $company->id)->value('source_pages');
        return view('admin.resources.index', compact('resources', 'total_source_pages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin.resources.create');
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
            'type' => ['required'],
            'url' => ['nullable', 'required_if:type,url', 'string'],
            'title' => ['required'],
            'file' => ['required_if:type,document', 'file'],
        ]);

        $uploadType = $request->input('upload');

        $company = Auth::user()->company;
        $companies = Company::where('id', $company->id)
             ->orWhere('parent_company_id', $company->id)
             ->get();
//    Log::info($uploadType);
        if ($uploadType == 'single') {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $company->docsbot_api_key,
            ];
            if ($request->hasFile('file') && ($request->type == 'document' || $request->type == 'csv')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl);
                $response = Http::withHeaders($headers)->get($url . "upload-url?fileName=" . rawurlencode($fileName));
                if($response->ok()){
                    $uploadUrl = $response->json()['url'];
                    $uploadFile = $response->json()['file'];
                    // 署名付きURLを使用してファイルをアップロードします

                    $client = new Client();
                    $response = $client->request('PUT', $uploadUrl, [
                        'headers' => [
                            'Content-Type' => 'application/octet-stream'
                        ],
                        'body' => fopen($file->path(), 'r')
                    ]);

                    // ファイルをリソースとして登録します
                    $resources = [];
                    try {
                        $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources");
                        $data = [
                            'type' => $request->type,
                            'file' => $uploadFile,
                            'title' => $request->title,
                            'url' => $request->url,
                        ];
                        $response = Http::withHeaders($headers)->post($url, $data);

                        if (isset($response['message']) && !empty($response['message'])) {
                            return redirect()->route('admin.resources.create')->withErrors([['error' => __('resource.Invalid File')]]);
                        }
                        $resources = $response->json();
                    } catch (ConnectionException $e) {
    //                    Log::error($e->getMessage());
                    }
                } else {
    //                Log::error($response);
                }
            } else {
                $data = [
                    'type' => $request->type,
                    'title' => $request->title,
                    'url' => $request->url,
                ];
                $resources = [];
                try {
                    $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources");
                    $response = Http::withHeaders($headers)->timeout(20)->post($url, $data);

                    $resources = $response->json();
                } catch (ConnectionException $e) {
    //                Log::error($e->getMessage());
                }
            }
        } else {
            foreach ($companies as $company) {
                $headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $company->docsbot_api_key,
                ];
                if ($request->hasFile('file') && ($request->type == 'document' || $request->type == 'csv')) {
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
                    $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl);
                    $response = Http::withHeaders($headers)->get($url . "upload-url?fileName=" . rawurlencode($fileName));
                    if($response->ok()){
                        $uploadUrl = $response->json()['url'];
                        $uploadFile = $response->json()['file'];
                        // 署名付きURLを使用してファイルをアップロードします

                        $client = new Client();
                        $response = $client->request('PUT', $uploadUrl, [
                            'headers' => [
                                'Content-Type' => 'application/octet-stream'
                            ],
                            'body' => fopen($file->path(), 'r')
                        ]);

                        // ファイルをリソースとして登録します
                        $resources = [];
                        try {
                            $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources");
                            $data = [
                                'type' => $request->type,
                                'file' => $uploadFile,
                                'title' => $request->title,
                                'url' => $request->url,
                            ];
                            $response = Http::withHeaders($headers)->post($url, $data);

                            if (isset($response['message']) && !empty($response['message'])) {
                                return redirect()->route('admin.resources.create')->withErrors([['error' => __('resource.Invalid File')]]);
                            }
                            $resources = $response->json();
                        } catch (ConnectionException $e) {
        //                    Log::error($e->getMessage());
                        }
                    } else {
        //                Log::error($response);
                    }
                } else {
                    $data = [
                        'type' => $request->type,
                        'title' => $request->title,
                        'url' => $request->url,
                    ];
                    $resources = [];
                    try {
                        $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources");
                        $response = Http::withHeaders($headers)->timeout(20)->post($url, $data);
                        $resources = $response->json();
                    } catch (ConnectionException $e) {
        //                Log::error($e->getMessage());
                    }
                }
            }
        }
        return redirect()->route('admin.resources.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Auth::user()->company;
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $company->docsbot_api_key,
        ];
        $resource = [];
        try {
            $url = str_replace([':teamId', ':botId', ':sourceId'], [$company->docsbot_team_id, $company->docsbot_bot_id, $id], $this->baseUrl . 'sources/:sourceId');
            $response = Http::withHeaders($headers)->timeout(20)->get($url);
            $resource = $response->json();
        } catch (ConnectionException $e) {
            //
        }

        return view('admin.resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Auth::user()->company;
        $headers = [
        #    'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $company->docsbot_api_key,
        ];
        try {
            $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $this->baseUrl . "sources/" . $id);
            $response = Http::withHeaders($headers)->timeout(20)->delete($url);

//Log::info($response);

        } catch (ConnectionException $e) {
//            Log::error($e->getMessage());
        }

        return redirect()->route('admin.resources.index');
    }
}
