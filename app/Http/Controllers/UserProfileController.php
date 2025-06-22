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
        DB::beginTransaction();
        try {
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

            $fields = ['phone', 'address', 'bio', 'profile_image', 'gender', 'birthdate', 'instagram', 'linkedin', 'github', 'website',
                'has_gerd', 'has_anxiety', 'is_on_diet', 'diet_type', 'personality_note', 'daily_goal_note'];
            $filledCount = collect($fields)->filter(fn($f) => !empty($profile?->$f))->count();
            $progress = round(($filledCount / count($fields)) * 100);

            DB::commit();
            return response()->json([
                'profile' => new UserProfileResource($profile),
                'completion' => $progress
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error retrieving user profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->error('Failed to retrieve profile', 500);
        }
    }


    /**
     * Update profil milik sendiri.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
        DB::beginTransaction();
        try{
            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $validatedData
            );

            DB::commit();
            Log::info('User profile updated', [
                'user_id' => $user->id,
                'changes' => $validatedData,
            ]);
            return response()->success(new UserProfileResource($profile), 'Profile updated');
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->error('Failed to update profile', 500);
        }
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

        $user = auth()->user();

        if (!$user->profile) {
            return response()->error('Profile not found', 404);
        }
        DB::beginTransaction();
        try {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . now()->timestamp . '.' . $file->getClientOriginalExtension();

            // Hapus file lama jika ada
            if ($user->profile->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }

            // Simpan file baru
            $path = $file->storeAs('avatars', $filename, 'public');

            $user->profile->update(['profile_image' => $path]);


            DB::commit();

            return response()->success([
                'avatar_url' => asset('storage/' . $path)
            ], 'Avatar uploaded successfully');


        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Avatar upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->error('Failed to upload avatar', 500);
        }

    }
}
