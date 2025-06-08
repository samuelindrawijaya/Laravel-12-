<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->whenLoaded('role', fn () => $this->role->name),
            'profile' => $this->whenLoaded('profile'),
            'is_active' => $this->is_active,
            'email_verified' => $this->hasVerifiedEmail(),
        ];
    }
}
// This resource transforms the User model into an array format suitable for API responses.
// It includes the user's ID, name, email, role, profile, active status, and email verification status.
// The `whenLoaded` method ensures that the role and profile are only included if they have been loaded, preventing unnecessary database queries.
// This is useful for optimizing performance and reducing the amount of data sent in the response.
