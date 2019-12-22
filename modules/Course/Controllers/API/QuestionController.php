<?php

namespace Modules\Course\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\Course\Models\Answer;
use Modules\Course\Models\MatchingItem;
use Modules\Course\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param $test_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Http\Response
     */
    public function index($test_id)
    {
        return Question::with(['test','answers','matchingItems'])
            ->where('test_id','=',$test_id)
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
            'question'=> 'required|string|max:191|unique:questions',
            'type'=> 'required',
            'answers' => 'sometimes|array',
            'matching_items' => 'sometimes|array',
            'answers.*.answer' => 'sometimes|required',
            'matching_items.*.partB' => 'sometimes|required',
            'matching_items.*.partA' => 'sometimes|nullable',
            'test_id'=> 'required',
        ]);


        $question =  Question::create($request->all());

        if ($question->type == 'matching_items'){
            foreach ($request->matching_items as $item){
                $listitem = MatchingItem::create([
                    'question_id' => $question->id,
                    'partA' => $item['partA'],
                    'partB' => $item['partB'],
                ]);
            }
        }
        elseif ($question->type == 'multiple_choice'){
            foreach ($request->answers as $item){
                $answer = Answer::create([
                    'question_id' => $question->id,
                    'answer' => $item['answer'],
                    'isAnswer' => $item['isAnswer'],
                ]);
            }
        }
        elseif ($question->type == 'true_or_false'){
            $item = $request->answers[0];
            $answer = Answer::create([
                'question_id' => $question->id,
                'answer' => $item['answer'],
                'isAnswer' => $item['isAnswer'],
            ]);
        }


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
        return Question::with(['test','answers','matchingItems'])->findOrFail($id);
    }

    public function getTestQuestions($test_id)
    {
        return Question::with(['test','answers','matchingItems'])
            ->where('test_id','=',$test_id)
            ->inRandomOrder()
            ->get();
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
        $question = Question::with('test')->findOrFail($id);

        $this->validate($request,[
            'question'=> 'required|string|max:191|unique:questions,name,'.$question->id,
            'type'=> 'required',
            'answers' => 'sometimes|array',
            'matching_items' => 'sometimes|array',
            'answers.*.answer' => 'sometimes|required',
            'matching_items.*.partB' => 'sometimes|required',
            'matching_items.*.partA' => 'sometimes|nullable',
            'test_id'=> 'required',
        ]);

        if ($question->type == 'matching_items'){
            foreach ($request->matching_items as $item){
                $matching_item = $question->answers()->where('id',$item['id'])->update([
                    'question_id' => $question->id,
                    'partA' => $item['partA'],
                    'partB' => $item['partB'],
                ]);
            }
        }
        elseif ($question->type == 'multiple_choice'){
            foreach ($request->answers as $item){
                $answer = $question->answers()->where('id',$item['id'])->update([
                    'question_id' => $question->id,
                    'answer' => $item['answer'],
                    'isAnswer' => $item['isAnswer'],
                ]);
            }
        }
        elseif ($question->type == 'true_or_false'){
            $item = $request->answers[0];
            $answer = $question->answers()->where('id',$item['id'])->update([
                'question_id' => $question->id,
                'answer' => $item['answer'],
                'isAnswer' => $item['isAnswer'],
            ]);
        }

        $question->update($request->all());


        return ['message' => 'Question updated successfully'];
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
        $question = Question::findOrFail($id);
        $question->delete();

        return ['message' => "User Deleted"];
    }

    public function search(Request $request){
        if($search = $request->get('q')){
            $questions = Question::with('test')
            ->where(function ($query) use ($search){
                $query->where('name','LIKE',"%$search%")
                    ->orWhere('email','LIKE',"%$search%");
            })->paginate(20);
        }
        else
        {
            $questions = Question::with('test')->paginate(20);
        }

        return $questions;
    }
}
