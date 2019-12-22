<?php

namespace Modules\Course\Models;


use Illuminate\Database\Eloquent\Model;
use Modules\Course\Models\Course;

class Question extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question', 'type', 'test_id'
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
    ];

    public function test(){
        return $this->belongsTo(Test::class);
    }

    public function answers(){
        return $this->hasMany(Answer::class);
    }

    public function matchingItems(){
        return $this->hasMany(MatchingItem::class);
    }
}
