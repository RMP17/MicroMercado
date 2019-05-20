<?php

use Illuminate\Http\Request;
use App\Persona;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json(\App\Cuenta::all());
});

/*Route::get('/cuentassss', function () {
    return request()->user();
})->middleware('auth:api');*/
Route::get('/mac', function (Request $request) {
    ob_start(); // Turn on output buffering
    system('ipconfig /all'); //Execute external program to display output
    $mycom=ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean the output buffer

    $find_word = "CC-52-AF-93-94-30";
    $pmac = strpos($mycom, $find_word); // Find the position of Physical text in array
    $mac=substr($mycom, $pmac,17); // Get Physical Address

    /*echo $mac;
    $localIP = getHostByName(getHostName());
    echo '<br>'.$localIP.'<br>';*/
    var_dump(request()->ips());
});

Route::post('login','CuentaController@login');
Route::post('logout','CuentaController@logout')->middleware('auth:api');
Route::middleware('auth:api')->group(function() {
    Route::prefix('personas')->group(function () {
        Route::get('empleados', 'PersonaController@getAllEmpleados');
        Route::get('search/{ci?}', 'PersonaController@searchPersonaCi');
        Route::get('search/{ci?}', 'PersonaController@searchPersonaCi');
        Route::get('frequent-clients/{initialDate}/{endDate}', 'PersonaController@getFrequentClients');
        Route::put('contratar/{id?}', 'PersonaController@contratar');
        Route::post('suggestions', 'PersonaController@showSuggestions');
    });

    Route::prefix('productos')->group(function () {
        Route::get('limits-to-show-notifications', 'ProductoController@getLimitsToShowNotifications');
        Route::get('quantity', 'ProductoController@getQuantityProducts');
        Route::get('quantity-to-expire', 'ProductoController@getQuantityProductsToExpire');
        Route::get('proveedores/{idProducto}', 'ProductoController@getProveedoresForProduct');
        Route::put('update-date/{id?}', 'ProductoController@updateDate');
        Route::put('update-notifications/{id?}', 'ProductoController@updateNotifications');
        Route::put('transferencia/{id?}', 'ProductoController@transferirProductos');
        Route::post('upload-image', 'ProductoController@imageUploadPost');
        Route::get('image/{id?}', 'ProductoController@getImage');
        Route::post('producto-marca', 'ProductoController@storeProductoMarca');
        Route::post('delete-producto-marca', 'ProductoController@deleteProductoMarca');
        Route::post('categorias', 'ProductoController@deleteProductoCategorias');
        Route::post('categorias/store', 'ProductoController@storeProductoCategorias');
        Route::get('categorias/{id}', 'ProductoController@showProductoCategorias');
    });

    Route::get('show-sessions/{date1}/{date2}', 'CuentaController@showSessions');

    Route::prefix('configuracion')->group(function () {
        Route::get('', 'ConfiguracionController@index');
        Route::post('', 'ConfiguracionController@store');
        Route::post('test-code', 'ConfiguracionController@testControlCode');
    });
    Route::prefix('compras')->group(function () {
        Route::get('search/{date}/{date1}/{empledo?}', 'CompraVentaController@showCompra');
        Route::get('creditos', 'CompraVentaController@showCreditosCompras');
        Route::post('realizar-pago', 'CompraVentaController@realizarPago');
    });

    Route::prefix('ventas')->group(function () {
        Route::post('invoice', 'CompraVentaController@addInvoice');
        Route::post('generate-lower-sales-invoice', 'CompraVentaController@generateLowersSalesInvoice');
        Route::get('creditos', 'CompraVentaController@showCreditosVentas');
        Route::post('register-pago-deudor', 'CompraVentaController@registerPagoDeudor');
        Route::get('detail-of-lower-sales/{idFactura}', 'CompraVentaController@getDetailOfLowerSales');
        Route::put('cancel-sale/{idVenta}', 'CompraVentaController@cancelSale');
        Route::put('cancel-minor-sales-invoice/{idFactura}', 'CompraVentaController@cancelMinorSalesInvoice');
        Route::put('cancel-invoice/{idVenta}', 'CompraVentaController@cancelInvoice');
        Route::get('export/{date}/{date1}', 'CompraVentaController@showVentaForExport');
        Route::get('search/{date}/{date1}/{empledo?}', 'CompraVentaController@showVentas');
        Route::get('get-lower-sales-invoice/{date}/{date1}', 'CompraVentaController@getLowerSalesInvoice');
        Route::get('menores/export/{date}/{date1}', 'CompraVentaController@showVentasMenoresForExport');
        /*Route::post('realizar-pago', 'CompraVentaController@realizarPago');*/
    });
    Route::prefix('cajas')->group(function () {
        Route::get('efectivo', 'CajaController@showEfectivo');
        Route::post('movements', 'CajaController@saveMovement');
        Route::get('movements/{date1}/{date2}', 'CajaController@showMovements');
        Route::get('cash-closing/{date1}/{date2}', 'CajaController@getCashClosing');
        Route::post('cash-closing', 'CajaController@storeCashClosing');
    });
    Route::get('proveedores', 'PersonaController@getProveedores');
	Route::get('proveedores-names', 'PersonaController@getProveedoresOnlyName');
    Route::apiResources([
		'compras-ventas' => 'CompraVentaController',
        'productos' => 'ProductoController',
        'marcas' => 'MarcaController',
        'categorias' => 'CategoriaController',
        'personas' => 'PersonaController',
        'cuentas' => 'CuentaController',
        'codigos' => 'CodigoController',
        'cajas' => 'CajaController',

//    'compras-ventas' => 'CompraVentaController' // Eliminar controlador
    ]);
});
//Route::get('generate', 'CompraVentaController@generateControlCode');
//Route::get('producto-id/{id}', 'ProductoController@showProductoId'); // eliminar en controlador

/*Route::group(['middleware' => 'throttle:300,1'], function () {
    Route::get('cash-closure/{date}', 'CajaController@calculateCashClosure');
});*/