<?php
Route::namespace('Mint\Service\Controllers')->prefix('api')->group(function () {
    Route::get('install/pre-requisite', 'InstallController@preRequisite');
    Route::post('install/validate', 'InstallController@store');
    Route::post('install', 'InstallController@store');
    Route::post('license', 'LicenseController@verify');

    Route::get('info', 'HomeController@info');
    Route::post('download', 'UpdateController@download');
    Route::post('update', 'UpdateController@update');

    Route::get('license/validate', 'HomeController@licenseValidate');
});
