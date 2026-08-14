<?php

namespace App\Http\Resources;

use App\Http\Api\ChannelApi;
use App\Http\Api\UserApi;
use App\Models\DhammaTerm;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * The payload must contain only scalars and arrays: it is cached by
     * RelationController, and Laravel 13 refuses to unserialize objects from
     * cache unless they are listed in cache.serializable_classes. Hence the
     * associative json_decode() and the ISO-8601 date strings.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'case' => $this->case,
            'from' => json_decode((string) $this->from, true),
            'to' => json_decode((string) $this->to, true),
            'match' => json_decode((string) $this->match, true),
            'category' => $this->category,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if (! $request->has('vocabulary')) {

            $data['editor'] = UserApi::getByUuid($this->editor_id);

            $uiLang = strtolower($request->input('ui-lang', 'zh-Hans'));
            $term_channel = ChannelApi::getSysChannel("_System_Grammar_Term_{$uiLang}_");
            if ($term_channel) {
                $data['category_channel'] = $term_channel;
                if (! empty($this->category)) {
                    $term = DhammaTerm::where('word', $this->category)
                        ->where('channal', $term_channel)
                        ->first();
                    if ($term) {
                        $data['category_term']['channelId'] = $term_channel;
                        $data['category_term']['word'] = $term->word;
                        $data['category_term']['id'] = $term->guid;
                        $data['category_term']['meaning'] = $term->meaning;
                    }
                }
                $data['name_channel'] = $term_channel;
                $term_name = DhammaTerm::where('word', $this->name)
                    ->where('channal', $term_channel)
                    ->first();
                if ($term_name) {
                    $data['name_term']['channelId'] = $term_channel;
                    $data['name_term']['word'] = $term_name->word;
                    $data['name_term']['id'] = $term_name->guid;
                    $data['name_term']['meaning'] = $term_name->meaning;
                }
            }
        }
        /*

*/

        return $data;
    }
}
