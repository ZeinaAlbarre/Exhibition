<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function(){
    Route::post('visitor_register','visitor_register')->name('visitor.register');
    Route::post('company_register','company_register')->name('company.register');
    Route::post('organizer_register','organizer_register')->name('organizer.register');
    Route::post('login','login')->name('user.login');

    Route::post('UserForgotPassword','UserForgotPassword');
    Route::post('UserCodeCheck','UserCodeCheck');
    Route::post('UserResetPassword/{id}','UserResetPassword');

    Route::post('code_check_verification/{id}','code_check_verification')->name(' code.check.verification');
    Route::get('refresh_code/{id}','refresh_code')->name('refresh.code');

});
//ewl
Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout',[AuthController::class,'logout']);
    Route::get('accept_company/{id}',[AuthController::class,'accept_company'])->middleware('can:accept.company');
    Route::get('reject_company/{id}',[AuthController::class,'reject_company'])->middleware('can:reject.company');
    Route::post('add_employee',[AuthController::class,'add_employee'])->name('add.employee')->middleware('can:add.employee');
    Route::get('delete_employee/{id}',[AuthController::class,'delete_employee'])->name('delete.employee')->middleware('can:delete.employee');
    Route::get('deleteAccount',[AuthController::class,'deleteAccount'])->name('delete.account')->middleware('can:delete.account');
    Route::get('showProfile',[AuthController::class,'showProfile'])->name('show.profile');
    Route::post('updateCompanyProfile',[AuthController::class,'updateCompanyProfile']);
    Route::get('showEmployee',[AuthController::class,'showEmployee']);
    Route::get('showCompanyRegisterRequest',[AuthController::class,'showCompanyRegisterRequest']);
    Route::post('reset_visitor_password',[AuthController::class,'reset_visitor_password']);


    Route::controller(ExhibitionController::class)->group(function () {
        //
        Route::post('addExhibition','addExhibition')->name('add.exhibition')->middleware('can:add.exhibition');
        Route::get('showExhibitionRequest','showExhibitionRequest');
        Route::get('acceptExhibition/{id}','acceptExhibition')->name('accept.exhibition')->middleware('can:accept.exhibition');
        Route::get('rejectExhibition/{id}','rejectExhibition')->name('reject.exhibition')->middleware('can:reject.exhibition');
        Route::get('deleteExhibition/{id}','deleteExhibition')->name('delete.exhibition')->middleware('can:delete.exhibition');
        Route::post('updateExhibition/{id}','updateExhibition')->name('update.exhibition')->middleware('can:update.exhibition');
        Route::get('showUpdateExhibitions','showUpdateExhibitions');
        Route::get('showUpdateExhibition/{id}','showUpdateExhibition');
        Route::get('acceptExhibitionUpdate/{id}','acceptExhibitionUpdate');
        Route::get('rejectExhibitionUpdate/{id}','rejectExhibitionUpdate');
        Route::get('showEmployeeExhibition','showEmployeeExhibition');
        Route::post('searchExhibition','searchExhibition');
        Route::get('showExhibitions','showExhibitions');
        Route::get('showEndExhibition','showEndExhibition');
        Route::get('showExhibition/{id}','showExhibition');
        Route::get('showAvailableExhibition','showAvailableExhibition');
        Route::get('showRegisterExhibition','showRegisterExhibition');
        Route::get('showAvailableCompanyExhibition','showAvailableCompanyExhibition');
        Route::post('changeExhibitionStatus/{id}','changeExhibitionStatus')->middleware('can:change.exhibition.status');
        Route::get('deleteExhibitionSection/{exhibition_id}/{section_id}','deleteExhibitionSection');
        Route::post('addExhibitionSection/{id}','addExhibitionSection');//show sections list on app and add to one of this
        Route::get('showExhibitionSection/{section_id}','showExhibitionSection');//all sections in app
        Route::post('changeEmployeeStatus','changeEmployeeStatus');

        //exhibitions
        Route::post('addExhibitionMedia/{exhibition_id}', 'addExhibitionMedia');
        Route::delete('deleteExhibitionMedia/{media_id}', 'deleteExhibitionMedia');
        Route::get('/showOrganizerExhibition', 'showOrganizerExhibition');
        Route::get('/acceptCompanyRequest/{company_id}/{stand_id}', 'acceptCompanyRequest');
        Route::get('/rejectCompanyRequest/{company_id}/{stand_id}', 'rejectCompanyRequest');
        Route::get('/company/{company_id}', 'showCompany');
        Route::get('/showExhibitionCompany/{exhibition_id}', 'showExhibitionCompany');

        // Schedule routes
        Route::post('/exhibitions/addSchedule/{exhibition_id}', 'addSchedule');
        Route::delete('/exhibitions/schedules/{schedule_id}', 'deleteSchedule');
        Route::post('/exhibitions/updateSchedule/{schedule_id}', 'updateSchedule');
        Route::get('/exhibitions/schedules/{schedule_id}', 'showSchedule');
        Route::get('/exhibition/schedule/{exhibition_id}', 'showExhibitionSchedule');


        // Stand routes
        Route::post('updateStand/{stand_id}', 'updateStand');
        Route::delete('deleteStand/{stand_id}', 'deleteStand');
        Route::post('addStand/{exhibition_id}', 'addStand');
        Route::get('/showExhibitionStands/{exhibition_id}','showExhibitionStands');

        Route::post('/addSponsor/{exhibition_id}', 'addSponsor');
        Route::delete('/deleteSponsor/{sponsor_id}', 'deleteSponsor');
        Route::get('/showExhibitionSponsors/{exhibition_id}','showExhibitionSponsors');

        //filter
        Route::get('filter_Exhibition_today','filter_Exhibition_today');
        Route::get('filter_Exhibition_thisWeek','filter_Exhibition_thisWeek');
        Route::get('filter_Exhibition_later','filter_Exhibition_later');

    });

    Route::controller(SectionController::class)->group(function () {
        Route::post('/addSection', 'addSection');
        Route::delete('/deleteSection/{section_id}', 'deleteSection');
        Route::get('/sections', 'showSections');
    });

    Route::controller(FavoriteController::class)->group(function () {
        Route::get('addFavorite/{exhibition_id}', 'addFavorite');
        Route::get('deleteFavorite/{id}', 'deleteFavorite');
        Route::get('showFavorite', 'showFavorite');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::post('addCategory/{exhibition_id}', 'addCategory');
        Route::delete('deleteCategory/{id}', 'deleteCategory');
        Route::get('showExhibitionCategory/{exhibition_id}', 'showExhibitionCategory');
    });

    Route::controller(TicketController::class)->group(function () {
        Route::get('/createTicket/{exhibition_id}', 'createTicket');
        Route::get('/showQR/{exhibition_id}', 'showQR');
        Route::post('/validate-ticket',  'validateTicket');
        Route::get('/showAvailableStand/{exhibition_id}',  'showAvailableStand');
        Route::post('/standBooking',  'standBooking');
        Route::get('/showStandInfo/{stand_id}',  'showStandInfo');
        Route::get('/countStandCompany/{stand_id}',  'countStandCompany');
        Route::post('/payCompanyEmployee',  'payCompanyEmployee');
        Route::post('/ScanExit',' ScanExit');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('/addMoney/{user_id}', 'addMoney');
        Route::get('/showMoney', 'showMoney');

    });

    Route::controller(CompanyController::class)->group(function (){
        // Add a new product
        Route::post('addProduct', 'addProduct');
        // Delete a product by ID
        Route::delete('deleteProduct/{id}',  'deleteProduct');
        // Update a product by ID
        Route::put('updateProduct/{id}', 'updateProduct');
        // Get a product by ID
        Route::get('showProducts/{id}', 'showProducts');
        Route::get('showCompanies', 'showCompanies');
        Route::post('searchCompany', 'searchCompany');
        Route::get('removeCompany/{company_id}', 'removeCompany');
        Route::get('showRegisterCompanyExhibition', 'showRegisterCompanyExhibition');
        Route::get('showUnRegisterCompanyExhibition', 'showUnRegisterCompanyExhibition');

    });

    Route::controller(RateController::class)->group(function (){
        Route::post('addRate/{id}', 'addRate');
        Route::post('updateRate/{id}', 'updateRate');
        Route::get('showExhibitionRate/{id}', 'showExhibitionRate');
    });

    Route::controller(VisitorController::class)->group(function (){
        Route::get('showVisitors', 'showVisitors');
        Route::post('searchVisitor', 'searchVisitor');
        Route::get('removeVisitor/{user_id}', 'removeVisitor');
    });

});

