<?php 

namespace App\Http\Controllers\MPerks;

use Illuminate\Http\Request;

class KeywordsController extends \App\Http\Controllers\Controller
{
    public function showForm()
    {
        $keywords = \App\Models\MPerksKeywords::all();
        return view('mperks.keywords', compact('keywords'));
    }
    
    public function saveKeywords(Request $request)
    {
        $keywords = $request->get('keywords', "");
        
        $keywordsList = explode("\n", $keywords);
        
        \App\Models\MPerksKeywords::truncate();
        
        foreach($keywordsList as $keywords) {
            $obj = new \App\Models\MPerksKeywords();
            $obj->keywords = trim($keywords);
            $obj->save();
        }
        
        return redirect()->route('mperks.keywords')->withSuccess("Saved Keywords");
    }
}