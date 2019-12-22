<?php

namespace Modules\Course\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\Course\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param $course_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Http\Response
     */
    public function index($course_id)
    {
        return Test::with('course')
            ->where('course_id','=',$course_id)
            ->paginate(20);
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
            'name'=> 'required|string|max:191|unique:tests',
            'description'=> 'required|string|max:191',
            'duration'=> 'required|string',
            'course_id'=> 'required',
        ]);

        return Test::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Builder|
     * \Illuminate\Database\Eloquent\Builder[]|
     * \Illuminate\Database\Eloquent\Collection|
     * \Illuminate\Database\Eloquent\Model|
     * \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Test::with('course')->findOrFail($id);
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
        $test = Test::with('course')->findOrFail($id);
        $this->validate($request,[
            'name'=> 'required|string|max:191|unique:tests,name,'.$test->id,
            'description'=> 'required|string|max:191',
            'duration'=> 'required|string',
            'course_id'=> 'required',
        ]);

        $test->update($request->all());

        return ['message' => 'Test updated successfully'];
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
        $test = Test::findOrFail($id);
        $test->delete();

        return ['message' => "User Deleted"];
    }

    public function search(Request $request){
        if($search = $request->get('q')){
            $tests = Test::with('course')
            ->where(function ($query) use ($search){
                $query->where('name','LIKE',"%$search%")
                    ->orWhere('email','LIKE',"%$search%");
            })->paginate(20);
        }
        else
        {
            $tests = Test::with('course')->paginate(20);
        }

        return $tests;
    }
}
