<?php
namespace App\Http\Api;

use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationApi{
    public static function send($data){
        $insertData = [];
        foreach ($data as $key => $row) {
            $insertData[] = [
                'id' => Str::uuid(),
                'from' => $row['from'],
                'to' => $row['to'],
                'url' => $row['url'],
                'content' => $row['content'],
                'res_type' => $row['res_type'],
                'res_id' => $row['res_id'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        $insert = Notification::insert($insertData);
        return $insert;
    }
}
