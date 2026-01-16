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
        $setting = AdminSetting::where('company_id', auth()->user()->company_id)->first();
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
        $setting = AdminSetting::where('company_id', auth()->user()->company_id)->first();
        if ($setting) {
            $setting->update($request->all());
        } else {
            AdminSetting::create(array_merge($request->all(), ['company_id' => auth()->user()->company_id]));
        }
        return redirect()->route('admin.settings.index')
            ->with('success', '設定を保存しました');
    }
}