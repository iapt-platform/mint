<?php
namespace App\Http\Api;

use App\Models\CourseMember;
use Illuminate\Support\Facades\Log;

class CourseApi{
    public static function getStudentChannels($courseId){
        $channels = [];
        $studentsChannel = CourseMember::where('course_id',$courseId)
                ->whereNotNull('channel_id')
                ->where('role','student')
                ->whereIn('status',['joined','accepted','agreed'])
                ->select('channel_id')
                ->orderBy('created_at')
                ->get();
        foreach ($studentsChannel as $key => $channel) {
            $channels[] = $channel->channel_id;
        }
        return $channels;
    }

    public static function role($courseId,$userUid){
        $role = CourseMember::where('course_id',$courseId)
                            ->where('user_id')
                            ->value('role');
        return $role;
    }
}
