<?php
namespace App\Http\Controllers;

use App\Models\UserReward;
use App\Models\Notification;
use App\Models\AdminSetting;
use App\Services\SlackService;
use Illuminate\Support\Facades\Auth;

class UserRewardController extends Controller
{
    protected $slackService;

    public function __construct(SlackService $slackService)
    {
        $this->slackService = $slackService;
    }

    public function index()
    {
        $userId = Auth::id();
        // 有効な所持報酬のみ
        $userRewards = UserReward::with('reward')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('expires_at')
            ->get();

        return view('rewards.my', compact('userRewards'));
    }

    public function use($id)
    {
        $userReward = UserReward::with('reward')->findOrFail($id);
        
        // 自分の報酬かチェック
        if ($userReward->user_id !== Auth::id()) {
            abort(403);
        }
        
        // 使用可能かチェック
        if (!$userReward->isUsable()) {
            return back()->with('error', 'この報酬は使用できません');
        }
        
        // 使用済みにする
        $userReward->used_at = now();
        $userReward->save();
        
        $user = Auth::user();
        $rewardName = $userReward->reward->name;
        
        // 管理者設定を取得
        $adminSetting = AdminSetting::where('company_id', $user->company_id)->first();
        
        if ($adminSetting) {
            // Slack通知
            if ($adminSetting->notification_type === 'slack' && $adminSetting->slack_id) {
                $message = "🎁 *報酬使用通知*\n\n";
                $message .= "ユーザー: {$user->name}\n";
                $message .= "使用した報酬: {$rewardName}\n";
                $message .= "使用日時: " . now()->format('Y/m/d H:i');
                
                $this->slackService->sendDM($adminSetting->slack_id, $message);
            }
            
            // Web通知（管理者設定のslack_idを持つユーザーへ）
            if ($adminSetting->slack_id) {
                $adminUser = \App\Models\User::where('slack_id', $adminSetting->slack_id)->first();
                if ($adminUser) {
                    Notification::create([
                        'user_id' => $adminUser->id,
                        'title' => '🎁 報酬使用通知',
                        'message' => "{$user->name}さんが「{$rewardName}」を使用しました",
                        'type' => 'reward_used',
                    ]);
                }
            }
        }
        
        return back()->with('success', '報酬を使用しました！管理者に通知されました。');
    }
}