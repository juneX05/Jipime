<?php

Route::apiResources([
    'courses' => 'CourseController',
]);
Route::get('findCourse','CourseController@search');

Route::get('/tests/course/{course_id}','TestController@index');
Route::post('/tests','TestController@store');
Route::put('/tests/{id}','TestController@update');
Route::get('/tests/{id}','TestController@show');
Route::delete('/tests/{id}','TestController@destroy');
Route::get('findTest','TestController@search');

Route::get('/questions/test/{test_id}','QuestionController@index');
Route::get('/questions/preview/test/{test_id}','QuestionController@getTestQuestions');
Route::post('/questions','QuestionController@store');
Route::put('/questions/{id}','QuestionController@update');
Route::get('/questions/{id}','QuestionController@show');
Route::delete('/questions/{id}','QuestionController@destroy');
Route::get('findQuestions','QuestionController@search');

Route::get('/answers/test/{test_id}','AnswerController@index');
Route::post('/answers','AnswerController@store');
Route::put('/answers/{id}','AnswerController@update');
Route::get('/answers/{id}','AnswerController@show');
Route::delete('/answers/{id}','AnswerController@destroy');
Route::get('findAnswers','AnswerController@search');