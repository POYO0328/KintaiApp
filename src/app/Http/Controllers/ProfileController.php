<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;


class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');

            // storage/app/public/profile_images にランダム名で保存
            $path = $file->store('profile_images', 'public');

            // DB には公開 URL 用パスを保存
            $user->profile_image_path = 'storage/' . $path;
        }

        $user->name         = $validated['name'];
        $user->postal_code  = $validated['postal_code'];
        $user->address      = $validated['address'];
        $user->building     = $validated['building'];
        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'プロフィールを更新しました');
    }


}
