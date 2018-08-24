<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/authenticate', 'AuthController@authenticate');
Route::get('/user', 'AuthController@getAuthenticatedUser');
Route::put('/user', 'UserController@update');
Route::post('/user/register', 'UserController@register');
Route::post('/user/send-password-reset-email', 'UserController@sendPasswordResetEmail');
Route::post('/user/reset-password/{token}', 'UserController@resetPassword');
Route::post('/reset-password/find-by-email-and-token', 'UserResetPasswordController@findByEmailAndToken');

Route::get('/user-betting-history/search-by-date', 'UserBettingHistoryController@searchByDate');
Route::get('/user-betting-history-detail/search-by-date', 'UserBettingHistoryDetailController@searchByDate');

Route::post('/auth', 'SeamlessAPIController@auth');
Route::post('/debit', 'SeamlessAPIController@debit');
Route::post('/credit', 'SeamlessAPIController@credit');
Route::post('/cancel', 'SeamlessAPIController@cancel');
Route::post('/tips', 'SeamlessAPIController@tips');
Route::post('/get-current-balance', 'SeamlessAPIController@getCurrentBalance');

Route::post('/balance/deposit', 'BalanceController@deposit');
Route::post('/balance/withdraw', 'BalanceController@withdraw');

Route::get('/countries', 'CountryController@index');
Route::get('/currencies', 'CurrencyController@index');
Route::get('/languages', 'LanguageController@index');