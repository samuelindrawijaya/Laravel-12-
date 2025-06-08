<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Tampilkan semua user (dengan pagination).
     */
    public function index(Request $request)
    {
        $query = User::with('role', 'profile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $users = $query->paginate(10);
        return new UserCollection($users);
    }


    /**
     * Tampilkan detail user.
     */
    public function show($id)
    {
        $user = User::with(['role', 'profile'])->findOrFail($id);
        return response()->success(new UserResource($user), 'User found');
    }

    /**
     * Aktif/nonaktifkan user (oleh admin).
     */
    public function setActive(UpdateUserStatusRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_active = $request->is_active;
        $user->save();

        Log::info('User status updated', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'new_status' => $request->is_active,
        ]);

        return response()->success(null, 'User status updated');
    }
}
// This controller handles user management tasks such as listing users, showing user details, and updating user status (active/inactive).
// It uses the `UpdateUserStatusRequest` for validation and the `UserResource` for formatting responses.
// The `index` method retrieves a paginated list of users with their roles, while the `show` method retrieves a specific user's details.
// The `setActive` method updates the user's active status and logs the action for auditing purposes.
// The `UserCollection` is used to format the list of users for the response.
