<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MileHistory;
use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    /**
     * 🧪 テスト用マイル付与
     */
    public function addMiles()
    {
        abort_unless(app()->environment('local'), 403);

        MileHistory::create([
            'user_id'    => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'miles'      => 1000,
            'type'       => 'debug',
            'memo'       => 'テスト用付与',
        ]);

        return back()->with('success', 'テスト用に +1000 マイル付与しました');
    }

    /**
     * 🧪 テスト用スクラッチポイント付与
     */
    public function addScratchPoints()
    {
        abort_unless(app()->environment('local'), 403);

        /** @var \App\Models\User $user */
        $user = User::findOrFail(Auth::id());

        $user->personal_mission_points += 100;
        $user->save();

        return back()->with('success', 'テスト用に +100 スクラッチポイント付与しました');
    }
}