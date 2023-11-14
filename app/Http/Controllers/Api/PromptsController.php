<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Prompt;
use App\Models\Category;

class PromptsController extends Controller
{
    public function index($type, $category = null)
    {
        $prompts = null;
        if ($type === 'general') {
            $prompts = Prompt::where('company_id', 1)->get();
            if (!is_null($category)) {
                $prompts = $prompts->where('category_id', $category);
            }
        } else {
            $prompts = Auth::user()->company->prompts;
        }

        return response()->json($prompts);
    }

    public function categories()
    {
        $categories = Category::select('id', 'name')->orderBy('id')->get();
        for ($category = 0; $category < count($categories); $category++) {
            $categories[$category]['name'] = __('common.Category.' . $categories[$category]['name']);
        }
        return response()->json($categories);
    }
}