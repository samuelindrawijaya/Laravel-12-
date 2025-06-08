<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class UserProfileController extends Controller
{
    /**
     * Lihat profil milik sendiri.
     */
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) {
            if($user){
                // Jika profil belum ada, buat profil baru dengan nilai default
                Log::info('Creating new profile for user', ['user_id' => $user->id]);
                // Buat profil baru dengan nilai default
                $profile = $user->profile()->create([
                    'user_id' => $user->id,
                    'phone' => null,
                    'address' => null,
                    'bio' => null,
                ]);
            }
        }

        $fields = ['phone', 'address', 'bio', 'profile_image', 'gender', 'birthdate', 'instagram', 'linkedin', 'github', 'website'];
        $filledCount = collect($fields)->filter(fn($f) => !empty($profile?->$f))->count();
        $progress = round(($filledCount / count($fields)) * 100);

        return response()->json([
            'profile' => new UserProfileResource($profile),
            'completion' => $progress
        ]);
    }


    /**
     * Update profil milik sendiri.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validatedData
        );

        Log::info('User profile updated', [
            'user_id' => $user->id,
            'changes' => $validatedData,
        ]);

        return response()->success(new UserProfileResource($profile), 'Profile updated');
    }

    /**
     * Tampilkan profil publik pengguna berdasarkan ID.
     */
    public function showPublic(User $user)
    {
        $profile = $user->profile;

        if (!$profile) {
            return response()->error('Profile not found', 404);
        }

        return response()->success(new UserProfileResource($profile), 'Profile retrieved');
    }

    /**
     * Upload avatar untuk pengguna.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        if (!$request->hasFile('avatar')) {
            return response()->error('No file uploaded', 400);
        }

        if (!$user->profile) {
            return response()->error('Profile not found', 404);
        }
        // Uncomment the following lines if you want to check if the profile image is already set
        // if (!$user->profile->profile_image) {
        //     return response()->error('Profile image not set', 404);
        // }


        $user = auth()->user();
        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '.' . $file->getClientOriginalExtension();

        if ($user->profile->profile_image) {
            Storage::disk('public')->delete($user->profile->profile_image);
        }

        $path = $file->storeAs('avatars', $filename, 'public');

        $user->profile->update(['profile_image' => $path]);

        return response()->success([
            'avatar_url' => $user->profile->profile_image
            ? asset('storage/' . $user->profile->profile_image)
            : asset('images/default-avatar.png'),
        ],  'Avatar uploaded successfully');
    }
}
