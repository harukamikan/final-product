<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MileHistory;

class MileHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = MileHistory::with('mission')
            ->where('user_id', $user->id);

        if ($request->filled('keyword')) {
            $query->whereHas('mission', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }


        $mileHistories = $query
            ->latest('created_at')
            ->get();

        $totalMiles = $user->total_miles ?? $user->mileHistories()->sum('miles');

        return view('missions.completed', [
            'mileHistories' => $mileHistories,
            'totalMiles'    => $totalMiles,
        ]);
    }
}
