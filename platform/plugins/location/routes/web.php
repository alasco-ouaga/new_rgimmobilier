<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Location\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'countries', 'as' => 'country.'], function () {
            Route::resource('', 'CountryController')->parameters(['' => 'country']);

            Route::get('list', [
                'as' => 'list',
                'uses' => 'CountryController@getList',
                'permission' => 'country.index',
            ]);
        });

        Route::group(['prefix' => 'states', 'as' => 'state.'], function () {
            Route::resource('', 'StateController')->parameters(['' => 'state']);

            Route::get('list', [
                'as' => 'list',
                'uses' => 'StateController@getList',
                'permission' => 'state.index',
            ]);
        });

        Route::group(['prefix' => 'cities', 'as' => 'city.'], function () {
            Route::resource('', 'CityController')->parameters(['' => 'city']);

            Route::get('list', [
                'as' => 'list',
                'uses' => 'CityController@getList',
                'permission' => 'city.index',
            ]);
        });

        Route::group(['prefix' => 'locations/bulk-import', 'as' => 'location.bulk-import.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'BulkImportController@index',
            ]);

            Route::post('/', [
                'as' => 'index.post',
                'uses' => 'BulkImportController@postImport',
                'permission' => 'location.bulk-import.index',
            ]);

            Route::post('/download-template', [
                'as' => 'download-template',
                'uses' => 'BulkImportController@downloadTemplate',
                'permission' => 'location.bulk-import.index',
            ]);

            Route::get('ajax/available-remote-locations', [
                'as' => 'available-remote-locations',
                'uses' => 'BulkImportController@ajaxGetAvailableRemoteLocations',
                'permission' => 'location.bulk-import.index',
            ]);

            Route::post('/import-location-data/{country}', [
                'as' => 'import-location-data',
                'uses' => 'BulkImportController@importLocationData',
                'permission' => 'location.bulk-import.index',
            ]);
        });

        Route::group(['prefix' => 'locations/export', 'as' => 'location.export.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ExportController@index',
            ]);

            Route::post('/', [
                'as' => 'process',
                'uses' => 'ExportController@export',
                'permission' => 'location.export.index',
            ]);
        });
    });

    Route::get('ajax/states-by-country', 'StateController@ajaxGetStates')
        ->name('ajax.states-by-country');
        
    Route::get('ajax/cities-by-state', 'CityController@ajaxGetCities')
        ->name('ajax.cities-by-state');
});
