<?php

namespace App\Http\Controllers;

use App\Models\SemesterSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SemesterSettingController extends Controller
{
    /**
     * 半期設定画面を表示
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $setting = SemesterSetting::current($companyId);
        $semesters = SemesterSetting::where('company_id', $companyId)
            ->orderBy('start_date', 'desc')
            ->get();
        
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

        $companyId = auth()->user()->company_id;
        $setting = SemesterSetting::current($companyId);
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
        $companyId = auth()->user()->company_id;
        
        // Artisanコマンドを実行（会社IDを渡す）
        Artisan::call('semester:reset', ['company_id' => $companyId]);
        
        return redirect()->route('admin.semester.index')
            ->with('success', '半期リセットを実行しました。新しい半期が開始されました。');
    }

    /**
     * 半期を削除
     */
    public function destroy(SemesterSetting $semester)
    {
        $companyId = auth()->user()->company_id;
        
        // 自社の半期以外は削除できない
        if ($semester->company_id !== $companyId) {
            return redirect()->route('admin.semester.index')
                ->with('error', 'この半期は削除できません');
        }
        
        $currentSemester = SemesterSetting::current($companyId);
        
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
