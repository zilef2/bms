<?php

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

use App\Http\Controllers\{PruebasController, RoleController, ParametrosController, PermissionController};
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
	return Inertia::render('Welcome');
})->name('home');


Route::get('/rama', [PruebasController::class, 'index'])->name('rama');

Route::get('dashboard', function () {
	return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware('auth', 'verified')->group(function () {
	//<editor-fold desc="profile - role - permission">
//	Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//	Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//	Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
	Route::resource('/role', RoleController::class)->except('create', 'show', 'edit');
	Route::post('/role/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('role.destroy-bulk');
	Route::resource('/permission', PermissionController::class)->except('create', 'show', 'edit');
	Route::post('/permission/destroy-bulk', [PermissionController::class, 'destroyBulk'])->name('permission.destroy-bulk');
	//</editor-fold>
	Route::resource('/Parametros', ParametrosController::class);
	
	//<editor-fold desc="User">
	Route::resource('/user', UserController::class)->except('create', 'show', 'edit');
	Route::get('/IndexTrashed', [UserController::class, 'IndexTrashed'])->name('IndexTrashed');
	//aquipues
}); //fin verified



// <editor-fold desc="Artisan">
Route::get('/exception', function () {
    throw new Exception('Probandof excepciones y enrutamiento. La prueba ha concluido exitosamente.');
});

Route::get('/clear-c', function () {
    // Artisan::call('optimize');
    Artisan::call('optimize:clear');

    return 'Optimizacion finalizada';
    // throw new Exception('Optimizacion finalizada!');
});
Route::get('/back-up', function () {
    $result = Artisan::call('backup:run');
    $output = Artisan::output();
    if ($result === 0) {
        // Éxito
        return response()->json(['status' => 'success', 'message' => 'Backup completed successfully!', 'output' => $output]);
    } else {
        // Error
        return response()->json(['status' => 'error', 'message' => 'Backup failed!', 'output' => $output]);
        //         throw new Exception('Backup failed!'. $output);
    }
});

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Este es un correo de prueba.', function ($message) {
            $message->to('ajelof2@gmail.com')
                ->subject('Correo de prueba');
        });
        return 'Correo enviado con éxito.';
    } catch (\Exception $e) {
        return 'Error al enviar el correo: ' . $e->getMessage();
    }
});
//</editor-fold>