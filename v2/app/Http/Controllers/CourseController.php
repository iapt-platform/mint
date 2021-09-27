<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\Course as CourseResource;
use Validator;


class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
		$courses = Course::all();
		return $this->sendResponse(CourseResource::collection($courses),"ok");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
		$input = $request->all();

		$validator = Validator::make($input,[
			'title' => 'required'
		]);
		if($validator->fails()){
			return $this->sendError('validation Error',$validator->errors());
		}
		$course = Course::create($input);
		return $this->sendResponse(new CourseResource($course),"ok");
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
		$result = Course::find($id);
		if(is_null($result)){
			return $this->sendError("couse not found");
		}
		return $this->sendResponse(new CourseResource($result),"ok");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
	 * PUT 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        //
		$input = $request->all();

		$validator = Validator::make($input,[
			'title' => 'required'
		]);
		if($validator->fails()){
			return $this->sendError('validation Error',$validator->errors());
		}
		$course->title = $input['title'];
		$course->save();
		return $this->sendResponse(new CourseResource($course),"ok");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
		$course->delete();
		return $this->sendResponse([],"delete ok");

    }
}
