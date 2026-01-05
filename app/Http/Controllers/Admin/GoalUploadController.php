<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\SemesterGoal;
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

        $companyId = \Illuminate\Support\Facades\Auth::user()->company_id;
        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0];

        $successCount = 0;
        $errorUsers = [];

        foreach (array_slice($data, 1) as $row) {
            $name = $row[0] ?? null;
            $category = $row[1] ?? null;
            $title = $row[2] ?? null;
            $deadlineValue = $row[3] ?? null;

            if (!$name || !$title) continue;

            $deadline = null;
            if ($deadlineValue) {
                try {
                    if (is_numeric($deadlineValue)) {
                        $deadline = Date::excelToDateTimeObject($deadlineValue)->format('Y-m-d');
                    } else {
                        $deadline = date('Y-m-d', strtotime($deadlineValue));
                    }
                } catch (\Exception $e) {
                    $deadline = null;
                }
            }

            $user = User::where('company_id', $companyId)
                ->where('name', $name)
                ->first();

            if ($user) {
                SemesterGoal::create([
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