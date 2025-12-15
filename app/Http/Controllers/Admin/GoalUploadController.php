<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Goal;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class GoalUploadController extends Controller
{
    // アップロード画面を表示
    public function index()
    {
        return view('admin.goals.upload');
    }

    // Excelファイルを処理
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0]; // 最初のシートを取得

        $successCount = 0;
        $errorUsers = [];

        // ヘッダー行をスキップ（1行目）
        foreach (array_slice($data, 1) as $row) {
            $name = $row[0] ?? null;
            $category = $row[1] ?? null;
            $title = $row[2] ?? null;
            $deadlineValue = $row[3] ?? null;

            if (!$name || !$title) continue;

            // 日付の変換処理を追加
            $deadline = null;
            if ($deadlineValue) {
                try {
                    // Excelのシリアル値を日付に変換
                    if (is_numeric($deadlineValue)) {
                        $deadline = Date::excelToDateTimeObject($deadlineValue)->format('Y-m-d');
                    } else {
                        $deadline = date('Y-m-d', strtotime($deadlineValue));
                    }
                } catch (\Exception $e) {
                    $deadline = null;
                }
            }

            // ユーザーを名前で検索
            $user = User::where('name', $name)->first();

            if ($user) {
                Goal::create([
                    'user_id' => $user->id,
                    'category' => $category,
                    'title' => $title,
                    'deadline' => $deadline,
                ]);
                $successCount++;
            } else {
                $errorUsers[] = $name;
            }
        }

        $errorUsers = array_unique($errorUsers);

        return redirect()->back()->with([
            'success' => "{$successCount}件の目標を登録しました",
            'errors' => $errorUsers
        ]);
    }
}