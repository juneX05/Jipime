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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Http\Response
     */
    public function index()
    {
        return Course::with('tests')->paginate(20);
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
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function show($id)
    {
        return Course::with('tests')->findOrFail($id);
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
        $course = Course::with('tests')->findOrFail($id);
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
        $course = Course::with('tests')->findOrFail($id);

        //implementing safe delete.
        if ($this->testsPresent($course)){
            return response()->json(['message' => "Please remove all tests in $course->name before deleting"],422);
        }
        $this->deleteCourse($course);

        return ['message' => "Course Deleted"];
    }

    protected function testsPresent($course){
        $tests =  count($course->tests);
        return $tests > 0;
    }

    protected function deleteCourse($course){
        $course->delete();
        $coursePhoto = public_path('img/course/').$course->image;
        if (file_exists($coursePhoto)){
            @unlink($coursePhoto);
        }
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroyAll(Request $request)
    {
        $this->authorize('isAdmin');

        foreach ($request->batch_delete as $id)
        {
            $course = Course::with('tests')->findOrFail($id);

            //implementing safe delete.
            if ($this->testsPresent($course)){
                return response()->json(['message' => "Please remove all tests in $course->name before deleting"],422);
            }
            $this->deleteCourse($course);
        }

        return response()->json(['message' => 'All Courses deleted successfully']);
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
            $courses = Course::with('tests')->paginate(20);
        }

        return $courses;
    }
}
