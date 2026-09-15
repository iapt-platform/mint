<?php

namespace App\Http\Controllers;

use App\Http\Api\StudioApi;
use App\Models\Course;
use App\Models\DhammaTerm;
use App\Models\Sentence;
use App\Models\UserInfo;
use App\Models\Wbw;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserMilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($studioName)
    {
        //
        $user_uid = StudioApi::getIdByName($studioName);

        $milestone = [];
        $milestone[] = ['date' => UserInfo::where('userid', $user_uid)->value('created_at'), 'event' => 'sign-in'];
        if (Wbw::where('creator_uid', $user_uid)->exists()) {
            $milestone[] = [
                'date' => Wbw::where('creator_uid', $user_uid)
                    ->orderBy('created_at')
                    ->value('created_at'),
                'event' => 'first-wbw',
            ];
        }
        if (Sentence::where('editor_uid', $user_uid)->exists()) {
            $milestone[] = [
                'date' => Sentence::where('editor_uid', $user_uid)
                    ->orderBy('created_at')
                    ->value('created_at'),
                'event' => 'first-translation',
            ];
        }
        if (DhammaTerm::where('owner', $user_uid)->exists()) {
            $milestone[] = [
                'date' => DhammaTerm::where('owner', $user_uid)
                    ->orderBy('created_at')
                    ->value('created_at'),
                'event' => 'first-term',
            ];
        }
        if (Course::where('studio_id', $user_uid)->exists()) {
            $milestone[] = [
                'date' => Course::where('studio_id', $user_uid)
                    ->orderBy('created_at')
                    ->value('created_at'),
                'event' => 'first-course',
            ];
        }

        return $this->ok($milestone);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
