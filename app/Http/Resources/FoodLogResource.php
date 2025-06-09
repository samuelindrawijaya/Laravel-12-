<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FoodLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'meal_time'=> $this->meal_time,
            'time'     => $this->time,
            'foods'    => $this->foods,
            'symptoms' => $this->symptoms,
            'concerns' => $this->concerns,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
