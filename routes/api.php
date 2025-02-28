<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('posts', PostController::class);

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/transaction', function (Request $request) {
    \Midtrans\Config::$serverKey = config('midtrans.serverKey');
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    $params = array(
        'transaction_details' => array(
            'order_id' => rand(),
            'gross_amount' => 10000,
        ),
        'customer_details' => array(
            'first_name' => 'dummy user',
            'email' => 'dummyemail99876@gmail.com'
        )
    );
    $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
    dump($paymentUrl);
  
    // Redirect to Snap Payment Page
    return redirect()->away($paymentUrl);
});


Route::get('/transaction/success', function (Request $request) {
    dump("Payment success");
});

// Route::post('/transaction/notification', function (Request $request) {
//         // Configure Midtrans
//         \Midtrans\Config::$serverKey = config('midtrans.serverKey');
//         \Midtrans\Config::$isProduction = false;
//         \Midtrans\Config::$isSanitized = true;
//         \Midtrans\Config::$is3ds = true;

//         // Get JSON notification from Midtrans
//         $json = json_decode($request->getContent(), true);

//         // Extract transaction details
//         $orderId = $json['order_id']; // Ensure order_id is used in orders table
//         $transactionStatus = $json['transaction_status'];
//         if ($transactionStatus == 'settlement') {
//             dump("Order {$orderId} marked as PAID.");
//         } elseif ($transactionStatus == 'pending') {
//             dump("Order {$orderId} marked as PENDING.");
//         } elseif ($transactionStatus == 'expire') {
//             dump("Order {$orderId} marked as EXPIRED.");
//         } elseif ($transactionStatus == 'cancel') {
//             dump("Order {$orderId} marked as CANCELED.");
//         }

//         return response()->json(['message' => 'Notification received and order updated']);
//     });


Route::match(['get', 'post'], '/transaction/notification', function (Request $request) {
    dump($request->method()); // Check what method is used
    dump($request->all()); // See the incoming data
    return response()->json(['message' => 'Notification received']);
});


// Route::middleware(['auth:sanctum'])->get('/transaction', function (Request $request) {
//         // Set your Merchant Server Key
//     \Midtrans\Config::$serverKey = config('midtrans.serverKey');
//     \Midtrans\Config::$isProduction = false;
//     \Midtrans\Config::$isSanitized = true;
//     \Midtrans\Config::$is3ds = true;

//     $params = array(
//         'transaction_details' => array(
//             'order_id' => rand(),
//             'gross_amount' => 10000,
//         ),
//         'customer_details' => array(
//             'first_name' => 'dummy user',
//             'email' => 'dummyemail99876@gmail.com'
//         )
//     );

//     $snapToken = \Midtrans\Snap::getSnapToken($params);

//     return response()->json([
//         'token' => $snapToken
//     ]);
// })
