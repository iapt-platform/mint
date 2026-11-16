<?php

namespace App\Http\Resources\V3;

use App\Models\ReadingDaily;
use Illuminate\Http\Request;

/**
 * 某一天（UTC）累计计入的读经时长。
 *
 * @mixin ReadingDaily
 */
class ReadingTimeResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{day: string, seconds: int, updated_at: string}
     */
    public function toArray($request): array
    {
        return [
            'day' => $this->day->toDateString(),
            'seconds' => (int) $this->seconds,
            'updated_at' => $this->updated_at,
        ];
    }
}
