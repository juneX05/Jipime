<?php

namespace Modules\Course\Models;


use Illuminate\Database\Eloquent\Model;
use Modules\Course\Models\Course;

class Answer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'answer', 'isAnswer', 'question_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //'email_verified_at' => 'datetime',
        'isAnswer' => 'boolean'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }

}
