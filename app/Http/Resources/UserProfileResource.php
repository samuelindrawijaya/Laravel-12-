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
        ];
    }
}
