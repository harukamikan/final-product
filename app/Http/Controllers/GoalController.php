<?php

namespace App\Http\Controllers;

use App\Models\Goal;

class GoalController extends Controller
{
    /**
     * 目標一覧画面
     */
    public function index()
    {
        // 期限が近い順に並べる例（カラム名に合わせて変更してください）
        $goals = Goal::orderBy('due_date', 'asc')->get();

        return view('goals.index', compact('goals'));
    }
}
