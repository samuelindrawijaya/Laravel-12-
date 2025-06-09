<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'phone'     => $this->phone,
            'address'   => $this->address,
            'bio'       => $this->bio,
            'gender'    => $this->gender,
            'birthdate' => $this->birthdate,
            'instagram' => $this->instagram,
            'linkedin'  => $this->linkedin,
            'github'    => $this->github,
            'website'   => $this->website,
            'profile_image' => $this->profile_image,
            'avatar_url' => $this->profile_image
                ? asset('storage/' . $this->profile_image)
                : asset('default/avatar.png'),
            'has_gerd' => $this->has_gerd,
            'has_anxiety' => $this->has_anxiety,
            'is_on_diet' => $this->is_on_diet,
            'diet_type' => $this->diet_type,
            'personality_note' => $this->personality_note,
            'daily_goal_note' => $this->daily_goal_note,

        ];
    }
}
