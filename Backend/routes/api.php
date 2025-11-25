<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Doctors\DoctorsOrdersController;
use App\Http\Controllers\Dietary\{
    FoodRequestsController,
    PatientsDietHistoryController,
    NutritionAssessmentController,
    MealsController,
    DietsController,
    SNSController,
    FoodController,
    ReportsController
};
use App\Http\Controllers\{
    PatientsController,
    NutritionController,
    DietTypesController,
    NotificationsController,
    WardsController,
    EmployeesController,
    UsersController,
};



Route::middleware('auth:sanctum')->group(function () {

    /*  ANCHOR: Meals Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(MealsController::class)->group(function () {
        Route::get('/meal-history/{enccode}', 'history');
        Route::get('/meal-list', 'list');
        Route::get('/meal-list/printable', 'printableList');
        Route::get('/meal-list/{date}', 'listByDate');
        Route::get('/meal-tags', 'tags');
        Route::get('/meal-census/{date}', 'census');
        Route::put('/accept-late-order', 'accept');
    });

    /*   ANCHOR: Notifcations Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(NotificationsController::class)->group(function () {
        Route::get('/notifications/{date_range}', 'notifications');
        Route::put('/acknowledge', 'acknowledge');
    });

    /*  ANCHOR: Diets Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(DietsController::class)->group(function () {
        Route::put('/diet/update-meal-status', 'update');
        Route::put('/diet/update-status-afterDishcarge/{hpercode}', 'updateStatusDischarge');
    });

    /*  ANCHOR: SNS Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(SNSController::class)->group(function () {
        Route::get('/sns/{date}', 'sns');
        Route::put('/sns/update-meal-status', 'updateMealStatus');
        Route::put('/sns/update-status', 'updateStatus');
        Route::put('/sns/acknowledge', 'acknowledge');
    });

    /*  ANCHOR: Food Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(FoodController::class)->group(function () {
        Route::get('/food-service-history', 'history');
        Route::post('/food-service-verify', 'verify');
    });

    /*   ANCHOR: Food Requests Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
    Route::controller(FoodRequestsController::class)->group(function () {
        Route::get('/food-requests/{date}', 'index');
        Route::post('/food-requests/create', 'create');
        Route::put('/food-requests/update', 'update');
    });

    /*   ANCHOR: Reports Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
    Route::controller(ReportsController::class)->group(function () {
        Route::get('/monthly-statistics/{date}', 'monthly');
        Route::get('/monthly-statistics-export/{date}', 'export');
    });

    /*  ANCHOR: Doctors Orders Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(DoctorsOrdersController::class)->group(function () {
        Route::get('/doctors-orders-history/{hpercode}', 'orders');
        Route::get('/doctors-orders-sns/{docointkey}', 'sns')->where('docointkey', '.+');
        Route::get('/doctors-order/{docointkey}', 'order')->where('docointkey', '.+');
        Route::get('/doctors-orders', 'all');
        Route::get('/doctors-orders-total', 'total');
        Route::post('/update-precautions', 'updatePrecautions');
        Route::post('/save-doctors-order', 'save');

        // Drafts Routes
        Route::get('/get-doctors-order-draft/{emp_id}', 'getDraft');
        Route::post('/save-doctors-order-draft', 'saveDraft');
        Route::delete('/delete-doctors-order-draft/{id}', 'deleteDraft');
    });

    /*  ANCHOR: Patients Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(PatientsController::class)->group(function () {
        Route::get('/patients-admitted', 'admitted');
        Route::get('/patient/{enccode}', 'details')->where('enccode', '.+');
        Route::get('/patient-measurements/{enccode}', 'measurements')->where('enccode', '.+');
        Route::get('/my-patients/{id}', 'myPatients');
        Route::get('/allergies', 'allergies');
        Route::get('/precautions', 'precautions');
        Route::post('/update-patient-food-allergies', 'updateFoodAllergies');
    });

    /*   ANCHOR: Nutrition Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
    Route::controller(NutritionController::class)->group(function () {
        Route::get('/patients-admitted-nutrition', 'admittedNutrition');
        Route::get('/patient-nutrition/{enccode}', 'patientNutrition')->where('enccode', '.+');
        Route::get('/patient-nutrition-assessment/{enccode}', 'assessment')->where('enccode', '.+');
        Route::post('/save-nutrition-screening', 'saveScreening');
        Route::post('/save-nutrition-assessment', 'saveAssessment');
        Route::delete('/delete-nutrition-screening/{id}', 'deleteScreening');
    });

    /*  ANCHOR: Diet Types Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(DietTypesController::class)->group(function () {
        Route::get('/diet-types-active', 'active');
        Route::get('/diet-types-enteral-all', 'enteral');
        Route::get('/diet-types-enteral-feeding-modes', 'modes');
        Route::put('/update-diet-status', 'updateStatus');
    });



    /*   ANCHOR: Wards Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
    Route::controller(WardsController::class)->group(function () {
        Route::get('/wards-active', 'active');
    });

    /*   ANCHOR: Employees Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
    Route::controller(EmployeesController::class)->group(function () {
        Route::get('/employee/{id}', 'employee');
        Route::get('/employees/allowed-personnel', 'allowed');
    });

    /*   ANCHOR: Users Controller API's
     *  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    Route::controller(UsersController::class)->group(function () {

        Route::get('/userLevels', 'userLevels');
        Route::get('/userlevel/{position}', 'getUserLevel');
        Route::get('/userDetails/{empid}', 'getUserDetails');
    });

    Route::get('/ping', function () {
        return response()->json(['message' => 'pong']);
    });
});

Route::get('/dietaryTunnel/{empid}/{enccode}/{hpercode}', [UsersController::class, 'dietaryTunnel'])->name('dietaryTunnel');

Route::middleware('auth:sanctum')->get('/testAPI', function () {
    return response()->json(['message' => 'API is working']);
});
