<?php
	Route::namespace('Mint\Service\Controllers')->prefix('api')->group(function () {
		Route::get('/migrate', 'InstallController@forceMigrate');
	});
?>
