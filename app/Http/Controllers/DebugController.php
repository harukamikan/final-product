<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\MileHistory;

class DebugController extends Controller
{
    public function addMiles()
    {
        abort_unless(app()->environment('local'), 403);
        
        MileHistory::create([
            'user_id' => Auth::id(),
            'company' => Auth::user()->company_id,
            'miles'  => 1000,
            'type'   => 'debug',
        ]);

        return back()->with('success', 'テスト用マイルを付与しました');
    }
}
