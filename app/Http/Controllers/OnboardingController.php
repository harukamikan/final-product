<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    protected OnboardingService $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Display the onboarding survey form
     */
    public function showSurvey(Request $request)
    {
        $user = $request->user();

        // Redirect if already onboarded
        if ($user->onboarded_at) {
            return redirect()->route('dashboard');
        }

        // Redirect if no company_id
        if (!$user->company_id) {
            return redirect()->route('company.create');
        }

        return view('onboarding.survey');
    }

    /**
     * Process survey submission and generate initial missions
     */
    public function submitSurvey(Request $request)
    {
        $user = $request->user();

        // Validate that user hasn't already completed onboarding
        if ($user->onboarded_at) {
            return redirect()->route('dashboard')
                ->with('info', '既にオンボーディングは完了しています。');
        }

        // Validate survey data
        $validated = $request->validate([
            'role' => ['required', 'in:IC,TechLead,EM,PdM'],
            'preferred_output' => ['required', 'in:blog,event,speaker,cert'],
            'current_situation' => ['required', 'in:new,normal,busy'],
            'experience_level' => ['required', 'in:junior,mid,senior'],
        ], [
            'role.required' => 'ロールを選択してください',
            'role.in' => '有効なロールを選択してください',
            'preferred_output.required' => '最もやりたいアウトプットを選択してください',
            'preferred_output.in' => '有効な選択肢を選んでください',
            'current_situation.required' => '現在の状況を選択してください',
            'current_situation.in' => '有効な選択肢を選んでください',
            'experience_level.required' => 'スキルレベルを選択してください',
            'experience_level.in' => '有効な選択肢を選んでください',
        ]);

        try {
            // Save survey response
            $survey = $this->onboardingService->saveSurveyResponse($user, $validated);

            // Generate initial missions
            $missions = $this->onboardingService->generateInitialMissions(
                $user,
                $survey->segment,
                $validated
            );

            // Mark user as onboarded
            $user->onboarded_at = now();
            $user->save();

            return redirect()
                ->route('missions.index')
                ->with('success', sprintf(
                    'ようこそ！あなた専用のミッション %d 件を用意しました。まずはチャレンジしてみましょう！',
                    count($missions)
                ));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'エラーが発生しました。もう一度お試しください。');
        }
    }
}
