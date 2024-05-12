<?php
namespace App\Http\Api;

use App\Models\CourseMember;
use Illuminate\Support\Facades\Log;

class CourseApi{
    public static function getStudentChannels($courseId){
        $channels = [];
        $studentsChannel = CourseMember::where('course_id',$courseId)
                ->whereNotNull('channel_id')
                ->select('channel_id')
                ->orderBy('created_at')
                ->get();
        foreach ($studentsChannel as $key => $channel) {
            $channels[] = $channel->channel_id;
        }
        return $channels;
    }
}
