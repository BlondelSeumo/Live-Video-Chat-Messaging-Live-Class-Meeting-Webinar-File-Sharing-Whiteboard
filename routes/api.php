<?php

use Illuminate\Support\Facades\Route;

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

// Guest Routes
Route::namespace('Auth')->prefix('auth')->group(function () {
    Route::post('login/otp', 'LoginWithOtp');
    Route::post('login', 'LoginController@login');

    Route::post('register', 'RegisterController@register');
    Route::post('verify', 'RegisterController@verify');

    Route::post('password', 'ResetPasswordController@password');
    Route::post('validate-reset-password', 'ResetPasswordController@validateCode');
    Route::post('reset', 'ResetPasswordController@reset');
});

// Global Routes
Route::namespace('Config')->group(function () {
    Route::get('config/pre-requisite', 'ConfigController@preRequisite');
    Route::group(['middleware' => ['under_maintenance']], function () {
        Route::get('config', 'ConfigController@index');
    });
});

Route::group(['middleware' => ['auth:sanctum']], function () {

    // Auth Routes
    Route::namespace('Auth')->prefix('auth')->group(function () {
        Route::post('logout', 'LoginController@logout');
        Route::post('lock', 'LockScreen');
        Route::get('user', 'UserController@me');
        Route::post('security', 'TwoFactorSecurity');
    });
});

Route::prefix('meetings')->group(function() {
    Route::get('pam/{identifier}', 'MeetingController@showPam');

    Route::prefix('{meeting}')->group(function() {
        Route::get('pam', 'MeetingController@pam');
        Route::post('join', 'InviteeController@join');
        Route::post('leave', 'InviteeController@leave');
    });
});

Route::group(['middleware' => ['auth:sanctum', '2fa', 'under_maintenance']], function () {

    // Auth Routes
    Route::namespace('Auth')->prefix('auth')->group(function () {
        Route::post('change-password', 'ChangePassword');
    });

    // User Routes
    Route::namespace('Auth')->prefix('user')->group(function () {
        Route::post('preference', 'UserController@preference');
    });

    Route::namespace('Auth')->group(function () {
        Route::get('users/pre-requisite', 'UserController@preRequisite');
        Route::post('users/{user}/status', 'UserController@updateStatus');
        Route::post('users/subscriptions', 'UserController@updateSubscription');
        Route::post('users/subscriptions/delete', 'UserController@deleteSubscription');
        Route::apiResource('users', 'UserController');

        Route::post('profile', 'ProfileController@update');
        Route::post('profile/avatar', 'ProfileController@uploadAvatar');
        Route::delete('profile/avatar', 'ProfileController@removeAvatar');
    });

    // Dashboard Routes
    Route::get('dashboard', 'DashboardController@index');
    Route::get('dashboard/stats', 'DashboardController@getStats');
    Route::get('dashboard/chart', 'DashboardController@getChart');

    // Any key search
    Route::get('search', 'Search');

    // Config Routes
    Route::namespace('Config')->prefix('config')->group(function () {
        Route::post('', 'ConfigController@store');
        Route::post('notification', 'ConfigController@notification');
        Route::get('notification', 'ConfigController@showDemoNotification');
        Route::post('assets', 'ConfigController@uploadAsset');
        Route::delete('assets', 'ConfigController@removeAsset');

        Route::delete('roles/{name}', 'RoleController@destroy');
        Route::apiResource('roles', 'RoleController')->except(['update', 'destroy']);

        Route::get('permissions/pre-requisite', 'PermissionController@preRequisite');
        Route::post('permissions/assign', 'PermissionController@assign');

        Route::get('locales/pre-requisite', 'LocaleController@preRequisite');
        Route::post('locales/{locale}/translate', 'LocaleController@translate');
        Route::apiResource('locales', 'LocaleController');
    });

    // Option Routes
    Route::get('options/pre-requisite', 'OptionController@preRequisite');
    Route::apiResource('options', 'OptionController');

    Route::group(['middleware' => ['can:access-contact']], function () {
        Route::apiResource('segments', 'SegmentController');

        Route::get('contacts/pre-requisite', 'ContactController@preRequisite');
        Route::apiResource('contacts', 'ContactController');

        Route::post('contacts/import/start', 'ContactImportController@startImport');
        Route::post('contacts/import/finish', 'ContactImportController@finishImport');
    });

    Route::get('meetings/pre-requisite', 'MeetingController@preRequisite');
    Route::get('meetings/m/{identifer}', 'MeetingController@showMeeting');
    Route::apiResource('meetings', 'MeetingController');

    Route::prefix('meetings/{meeting}')->group(function() {
        Route::post('config', 'MeetingController@config');
        Route::post('snooze', 'MeetingController@snooze');
        Route::post('cancel', 'MeetingController@cancel');

        Route::get('invitees/pre-requisite', 'InviteeController@preRequisite');
        Route::get('invitees', 'InviteeController@getInvitees');
        Route::post('invitees', 'InviteeController@addInvitees');

        Route::post('moderator', 'InviteeController@toggleModerator');
        Route::post('keep-alive', 'InviteeController@keepAlive');
        Route::post('invitation', 'InviteeController@sendInvitation');
        Route::post('invitees/{uuid}/block', 'InviteeController@blockInvitee');
        Route::post('invitees/{uuid}/unblock', 'InviteeController@unblockInvitee');
        Route::delete('invitees/{uuid}', 'InviteeController@deleteInvitee');

        // Route::post('join', 'InviteeController@join');
        // Route::post('leave', 'InviteeController@leave');
        Route::post('end', 'InviteeController@end');
        Route::post('alert', 'InviteeController@alert');

        Route::get('media/pre-requisite', 'MeetingMediaController@mediaPreRequisite');
        Route::post('media', 'MeetingMediaController@addMedia');
        Route::delete('media/{uuid}', 'MeetingMediaController@removeMedia');

        Route::get('comments/pre-requisite', 'MeetingCommentController@preRequisite');
        Route::get('comments', 'MeetingCommentController@index');
        Route::post('comments', 'MeetingCommentController@store');
        Route::post('comments/{comment}/media', 'MeetingCommentController@addMedia');
        Route::delete('comments/{comment}/media/{uuid}', 'MeetingCommentController@removeMedia');
    });

    Route::prefix('chat')->group(function() {
        Route::get('pre-requisite', 'ChatController@preRequisite');
        Route::get('rooms', 'ChatController@getRooms');
        Route::get('rooms/search', 'ChatController@searchRoom');
        Route::get('rooms/{uuid}', 'ChatController@getRoom');
        Route::patch('rooms/{uuid}', 'ChatController@editRoom');
        Route::post('rooms', 'ChatController@createRoom')->name('chat.create-room');
        Route::post('rooms/{uuid}/sync', 'ChatController@syncMember')->name('chat.sync-member')->middleware(['role:admin']);
        Route::get('rooms/{uuid}/members', 'ChatController@listMember')->name('chat.list-member');
        Route::post('rooms/{uuid}/members', 'ChatController@addMember')->name('chat.add-member');
        Route::delete('rooms/{uuid}/members', 'ChatController@removeMember')->name('chat.remove-member');
        Route::get('messages', 'ChatController@getMessage');
        Route::post('messages', 'ChatController@storeMessage');
        Route::get('messages/search', 'ChatController@searchMessage');
    });

    // Utility Routes
    Route::namespace('Utility')->prefix('utility')->group(function () {
        Route::post('todos/{uuid}/status', 'TodoController@toggleStatus');
        Route::apiResource('todos', 'TodoController');
    });

    // Site Routes
    Route::namespace('Site')->prefix('site')->group(function () {
        Route::get('pages/pre-requisite', 'PageController@preRequisite');
        Route::post('pages/{uuid}/media', 'PageController@addMedia');
        Route::delete('pages/{uuid}/media', 'PageController@removeMedia');
        Route::apiResource('pages', 'PageController');

        Route::apiResource('queries', 'QueryController')->only(['index', 'show', 'destroy']);
        Route::apiResource('subscribers', 'SubscriberController')->only(['index', 'show', 'destroy']);
    });
});

Broadcast::routes(["middleware" => ['auth:sanctum', '2fa']]);

//Fallback route
Route::fallback(function () {
    return response()->json(['message' => trans('general.api_not_found')], 404);
});
