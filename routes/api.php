<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ExternalOfficeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\SaudiOfficeController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\MarketingLeadController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/clients/search', [ClientController::class, 'search']);
    Route::post('/clients/quick', [ClientController::class, 'quickStore']);
    Route::apiResource('clients', ClientController::class);

    Route::apiResource('saudi-offices', SaudiOfficeController::class);
    Route::apiResource('external-offices', ExternalOfficeController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('orders', OrderController::class)->except('show');
    Route::get('/orders/without-tracking', [OrderController::class, 'getOrdersWithoutTracking']);
    Route::apiResource('order-tracking', OrderTrackingController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/finance/summary', [TransactionController::class, 'summary']);

    Route::get('/settings/priority-levels', [SettingController::class, 'getPriorityLevels']);
    Route::get('/settings/passport-statuses', [SettingController::class, 'getPassportStatuses']);
    Route::get('/settings/transfer-statuses', [SettingController::class, 'getTransferStatuses']);
    Route::get('/settings/payment-methods', [SettingController::class, 'getPaymentMethods']);
    Route::get('/settings/bank-names', [SettingController::class, 'getBankNames']);
    Route::get('/settings/order-statuses', [SettingController::class, 'getOrderStatuses']);
    Route::get('/settings/service-types', [SettingController::class, 'getServiceTypes']);
    Route::get('/settings/authentication-statuses', [SettingController::class, 'getAuthenticationStatuses']);
    Route::get('/settings/authorization-statuses', [SettingController::class, 'getAuthorizationStatuses']);

    Route::post('/settings/priority-levels', [SettingController::class, 'updatePriorityLevels']);
    Route::post('/settings/passport-statuses', [SettingController::class, 'updatePassportStatuses']);
    Route::post('/settings/transfer-statuses', [SettingController::class, 'updateTransferStatuses']);
    Route::post('/settings/payment-methods', [SettingController::class, 'updatePaymentMethods']);
    Route::post('/settings/bank-names', [SettingController::class, 'updateBankNames']);
    Route::post('/settings/order-statuses', [SettingController::class, 'updateOrderStatuses']);
    Route::post('/settings/service-types', [SettingController::class, 'updateServiceTypes']);
    Route::post('/settings/authentication-statuses', [SettingController::class, 'updateAuthenticationStatuses']);
    Route::post('/settings/authorization-statuses', [SettingController::class, 'updateAuthorizationStatuses']);

    Route::delete('/settings/priority-levels/{id}', [SettingController::class, 'deletePriorityLevel']);
    Route::delete('/settings/passport-statuses/{id}', [SettingController::class, 'deletePassportStatus']);
    Route::delete('/settings/transfer-statuses/{id}', [SettingController::class, 'deleteTransferStatus']);
    Route::delete('/settings/payment-methods/{id}', [SettingController::class, 'deletePaymentMethod']);
    Route::delete('/settings/bank-names/{id}', [SettingController::class, 'deleteBankName']);
    Route::delete('/settings/order-statuses/{id}', [SettingController::class, 'deleteOrderStatus']);
    Route::delete('/settings/service-types/{id}', [SettingController::class, 'deleteServiceType']);
    Route::delete('/settings/authentication-statuses/{id}', [SettingController::class, 'deleteAuthenticationStatus']);
    Route::delete('/settings/authorization-statuses/{id}', [SettingController::class, 'deleteAuthorizationStatus']);

    Route::post('/order-tracking/{orderTracking}/attachments', [AttachmentController::class, 'store']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

    Route::get('/marketing-leads/saudi-offices', [MarketingLeadController::class, 'getSaudiOffices']);
    Route::get('/marketing-leads/external-offices', [MarketingLeadController::class, 'getExternalOffices']);
    Route::get('/marketing-leads/service-offices', [MarketingLeadController::class, 'getServiceOffices']);
    Route::get('/marketing-leads/statuses', [MarketingLeadController::class, 'getStatuses']);
    Route::get('/marketing-leads/priority-levels', [MarketingLeadController::class, 'getPriorityLevels']);

    Route::post('/marketing-leads/saudi-office', [MarketingLeadController::class, 'storeSaudiOffice']);
    Route::post('/marketing-leads/external-office', [MarketingLeadController::class, 'storeExternalOfficeNew']);
    Route::post('/marketing-leads/service-office', [MarketingLeadController::class, 'storeServiceOfficeNew']);

    Route::apiResource('marketing-leads', MarketingLeadController::class);
});