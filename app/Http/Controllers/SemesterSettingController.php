<?php

namespace App\Http\Controllers;

use App\Models\SemesterSetting;
use Illuminate\Http\Request;

class SemesterSettingController extends Controller
{
    /**
     * 半期設定画面を表示
     */
    public function index()
    {
        $setting = SemesterSetting::current();
        
        return view('semester.index', compact('setting'));
    }

    /**
     * 半期設定を保存
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_reset_enabled' => 'boolean',
        ]);

        $setting = SemesterSetting::current();
        $setting->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'auto_reset_enabled' => $request->boolean('auto_reset_enabled'),
        ]);

        return redirect()->route('admin.semester.index')
            ->with('success', '半期設定を保存しました');
    }
}
