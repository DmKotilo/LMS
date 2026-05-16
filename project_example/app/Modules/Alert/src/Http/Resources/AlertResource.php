<?php

namespace App\Modules\Alert\src\Http\Resources;

use Alert\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Alert::class;


    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'bonus_count' => $this->bonus_count,
            'bonus_date' => $this->bonus_date,
            'bonus_message' => $this->bonus_message,
            'message' => $this->message,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),

        ];
    }



}
