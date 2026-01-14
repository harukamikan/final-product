<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
class AdminSettingController extends Controller
{
    // 設定画面を表示
    public function index()
    {
        $setting = AdminSetting::first();
       return view('admin.settings.index', compact('setting'));
    }
    // 設定を保存
    public function store(Request $request)
    {
        $request->validate([
            'notification_type' => 'required|in:slack,email',
            'slack_id' => 'nullable|string',
            'email' => 'nullable|email',
            'personal_mission_edit_start' => 'nullable|date',
            'personal_mission_edit_end' => 'nullable|date|after_or_equal:personal_mission_edit_start',
        ]);
        $setting = AdminSetting::first();
        if ($setting) {
            $setting->update($request->all());
        } else {
            AdminSetting::create($request->all());
        }
        return redirect()->route('admin.settings.index')
            ->with('success', '設定を保存しました');
    }
}