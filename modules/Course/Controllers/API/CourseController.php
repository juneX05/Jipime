<?php

namespace Modules\Course\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\Course\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Course::latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=> 'required|string|max:191|unique:courses',
            'description'=> 'required|string|max:191',
            'image'=> 'sometimes',
        ]);

        $photo = $request['image'];
        if ($photo != '') {
            //upload photo to server.
            $extension = $this->getFileExtension($photo);
            $name = time().'.'.$extension;
            \Image::make($photo)->save(public_path('img/course/').$name);

            $request->merge(['image' => $name]);
        }

        return Course::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'image' => $request['image'],
        ]);
    }

    protected function getFileExtension($photo){
        $extension = explode('/',
            explode(':',
                substr($photo,0,
                    strpos($photo,';')))[1])[1];

        return $extension;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Course::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $this->validate($request,[
            'name'=> 'required|string|max:191|unique:courses,name,'.$course->id,
            'description'=> 'required|string|max:191',
            'image'=> 'sometimes',
        ]);

        $photo = $request['image'];
        if ($course->image != $photo && $photo != ''){
            //upload photo to server.
            $extension = $this->getFileExtension($photo);
            $name = time().'.'.$extension;
            \Image::make($photo)->save(public_path('img/course/').$name);

            $request->merge(['image' => $name]);

            $coursePhoto = public_path('img/course/').$course->image;
            if (file_exists($coursePhoto)){
                @unlink($coursePhoto);
            }
        }

        $course->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'image' => $request['image'],
        ]);

        return ['message' => 'Course updated successfully'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');
        $course = Course::findOrFail($id);
        $course->delete();
        //delete the user
        $coursePhoto = public_path('img/course/').$course->image;
        if (file_exists($coursePhoto)){
            @unlink($coursePhoto);
        }
        return ['message' => "User Deleted"];
    }

    public function search(Request $request){
        if($search = $request->get('q')){
            $courses = Course::where(function ($query) use ($search){
                $query->where('name','LIKE',"%$search%")
                    ->orWhere('email','LIKE',"%$search%");
            })->paginate(20);
        }
        else
        {
            $courses = Course::latest()->paginate(20);
        }

        return $courses;
    }
}
