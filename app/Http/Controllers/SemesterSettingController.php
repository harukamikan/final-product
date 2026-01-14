<?php

namespace App\Http\Controllers;

use App\Models\SemesterSetting;
use App\Models\User;
use App\Services\SlackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SemesterSettingController extends Controller
{
    /**
     * 半期設定画面を表示
     */
    public function index()
    {
        $setting = SemesterSetting::current();
        $semesters = SemesterSetting::orderBy('start_date', 'desc')->get();
        
        return view('semester.index', compact('setting', 'semesters'));
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

    /**
     * 今すぐリセット
     */
    public function reset()
    {
        // Artisanコマンドを実行
        Artisan::call('semester:reset');
        
        return redirect()->route('admin.semester.index')
            ->with('success', '半期リセットを実行しました。新しい半期が開始されました。');
    }

    /**
     * 半期を削除
     */
    public function destroy(SemesterSetting $semester)
    {
        $currentSemester = SemesterSetting::current();
        
        // 現在の半期は削除できない
        if ($semester->id === $currentSemester->id) {
            return redirect()->route('admin.semester.index')
                ->with('error', '現在の半期は削除できません');
        }
        
        // 関連するマイル履歴も削除
        \App\Models\MileHistory::where('semester_id', $semester->id)->delete();
        
        // 半期を削除
        $semester->delete();
        
        return redirect()->route('admin.semester.index')
            ->with('success', '半期を削除しました');
    }
}
