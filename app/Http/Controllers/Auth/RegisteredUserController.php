<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InviteToken;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ 招待リンク経由なら company_id を自動付与
        $inviteCompanyId = $request->session()->get('invite_company_id');
        $inviteTokenId   = $request->session()->get('invite_token_id');

        if ($inviteCompanyId && empty($user->company_id)) {
            $user->company_id = $inviteCompanyId;
            $user->save();

            // ✅ 使用回数を増やす（無制限でもログとして有用）
            if ($inviteTokenId) {
                InviteToken::where('id', $inviteTokenId)->increment('used_count');
            }

            // ✅ セッションの招待情報を消す
            $request->session()->forget(['invite_company_id', 'invite_token_id']);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
