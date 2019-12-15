<?php

Route::apiResources([
    'users' => 'UserController',
]);

Route::get('profile','UserController@profile');
Route::get('findUser','UserController@search');
Route::put('profile','UserController@updateProfile');