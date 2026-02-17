<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageDetailsController;
use App\Http\Controllers\ManageImageController;
use App\Http\Controllers\S3ImageUploadController;
use App\Http\Controllers\S3UserProfileImageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchTagController;
use App\Http\Controllers\UserImagesSearchController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserProfileEditController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::name('api.')->group(function (){

        Route::group(['prefix' => 'v1', 'as' => 'v1.'], function (){

            Route::group(['prefix' => 'auth', 'as' => 'auth.'], function (){
                Route::post('/login', [AuthController::class, 'login'])
                    ->name('login');
                Route::post('/register', [AuthController::class, 'register'])
                    ->name('register');
            });

            Route::group(['prefix' => 'image', 'as' => 'image.'], function (){
                Route::group(['middleware' => 'auth:sanctum'], function (){
                    Route::get('/s3_pre_signed_url', S3ImageUploadController::class)
                        ->name('s3_pre_signed_url');
                    Route::get('/of-user', UserImagesSearchController::class)
                        ->name('user-search');
                    Route::delete('/{image}', [ManageImageController::class, 'delete'])
                        ->name('delete');
                    Route::put('/{image}', [ManageImageController::class, 'edit'])
                        ->name('edit');

                    Route::get('/{imageId}/details/edit', ImageDetailsController::class)
                        ->name('details-for-edit');
                });

                Route::get('/', SearchController::class)
                    ->name('search');

                Route::bind('imageId', function ($imageId){
                    return $imageId;
                });

                Route::get('/{imageId}/details', ImageDetailsController::class)
                    ->name('details');
            });

            Route::group(['prefix' => 'profile', 'as' => 'profile.'], function (){
                Route::get('/{userId}/details', UserProfileController::class)
                    ->name('details');

                Route::group(['middleware' => 'auth:sanctum'], function (){
                    Route::put('/edit', UserProfileEditController::class)
                        ->name('edit');
                    Route::get('/s3_pre_signed_url', S3UserProfileImageController::class)
                        ->name('s3_pre_signed_url');
                });
            });

            Route::group(['prefix' => 'tag', 'as' => 'tag.'], function (){
                Route::get('/', SearchTagController::class)
                    ->name('search');
            });
        });

    });
});
