<?php

declare(strict_types=1);

namespace App\Http\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Like;
use App\Models\Notification;

class WatchApi
{
    public static function change(array $resId, string $from, string $message)
    {
        //发送站内信
        $watches = Like::where('type', 'watch')
            ->whereIn('target_id', $resId)
            ->get();
        $notifications = [];
        foreach ($watches as  $watch) {
            $notifications[] = [
                'id' => Str::uuid(),
                'from' => $from,
                'to' => $watch->user_id,
                'url' => '',
                'content' => $message,
                'res_type' => $watch->target_type,
                'res_id' => $watch->target_id,
                'channel' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Log::debug('notification insert', ['data' => $notifications]);
        $new = Notification::insert($notifications);
        return $new;
    }
}
