<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionCreater extends Controller
{
    public function index()
    {
        $missions = Mission::orderBy('id')->paginate(20);

        return view('admin.missions.index', compact('missions'));
    }

    public function create()
    {
        return view('admin.missions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Mission::create($data);

        return redirect()
            ->route('admin.missions.index')
            ->with('success', 'ミッションを作成しました。');
    }

    public function edit(Mission $mission)
    {
        return view('admin.missions.edit', compact('mission'));
    }

    public function update(Request $request, Mission $mission)
    {
        $data = $this->validateData($request, $mission->id);

        $mission->update($data);

        return redirect()
            ->route('admin.missions.index')
            ->with('success', 'ミッションを更新しました。');
    }

    public function destroy(Mission $mission)
    {
        $mission->delete();

        return redirect()
            ->route('admin.missions.index')
            ->with('success', 'ミッションを削除しました。');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key'            => ['required', 'string', 'max:255', 'unique:missions,key,' . $id],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'trigger_type'   => ['required', 'string', 'max:255'],
            'required_count' => ['required', 'integer', 'min:1'],
            'reward_miles'   => ['required', 'integer', 'min:0'],
            'repeatable'     => ['required', 'boolean'],
        ]);
    }
}
