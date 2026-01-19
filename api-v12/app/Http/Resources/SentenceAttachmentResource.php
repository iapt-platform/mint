<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class SentenceAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $url = config('app.url') . '/api/v2/attachment/' . $this->attachment_id;
        Log::info($url);
        //$response = Http::get($url);


        $data = [
            'uid' => $this->uid,
            'sentence_id' => $this->sentence_id,
            'attachment_id' => $this->attachment_id,
            'attachment' => [],
            'editor_id' => $this->editor_id,
        ];
        $res = Attachment::find($this->attachment_id);
        $filename = $res->bucket . '/' . $res->name;
        if (App::environment('local')) {
            $data['attachment']['url'] = Storage::url($filename);
        } else {
            $data['attachment']['url'] = Storage::temporaryUrl($filename, now()->addDays(2));
        }
        return $data;
    }
}
