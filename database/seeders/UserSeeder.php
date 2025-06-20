<?php

namespace Database\Seeders;

use App\helpers\MyModels;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {
	
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$sexos = ['Masculino', 'Femenino'];
		$genPa = $this->validarEnv();
		
		$superadmin = User::create([
               'name'              => 'Superadmin',
               'email'             => 'superadminmodnom@superadmin.com',
               'password'          => bcrypt($genPa . 'superadmin00.+-*' . $genPa),
               //1423superadmin00.+-*1423
               'email_verified_at' => date('Y-m-d H:i'),
               //		                            'cedula'            => '11232454',
               //		                            'cargo_id'          => 2
           ]);
		$superadmin->assignRole('superadmin');
		
		$nombresGenericos = [
			'maria'        => '777117711',
			'Alejandro222' => '1234567890',
			'Dispercion'   => '1052566569',
		];
		
		$rolesUser = MyModels::CargosYModelos('soloNombresRoles');
		
		foreach ($rolesUser as $rol) {
			foreach ($nombresGenericos as $key => $value) {
				$yearRandom = (rand(5, 49));
				$nombresRandom = (rand(1, count($rolesUser)));
//				$anios = Carbon::now()->subYears($yearRandom)->format('Y-m-d H:i');
				$unUsuario = User::create([
					                          'name'              => substr($key, $nombresRandom) . ' el ' . $rol,
					                          'email'             => $key . '@' . $rol . $key . '.com',
					                          'password'          => bcrypt($genPa . ' _ ' . $rol), //1234_modnom _ Empleado
					                          'email_verified_at' => date('Y-m-d H:i'),
					                          //				                           'cedula'            => $value,
					                          //				                           'sexo'              => 'masculino',
					                          //				                           'celular'           => $value * 2,
					                          //				                           'salario'           => 1432000,
					                          //				                           'cargo_id'          => 3,
					                          //				                           'centro_costo_id'   => 1,
				                          ]);
				$unUsuario->assignRole($rol);
			}
		}
		
	}
	
	public function validarEnv() {
		$genPa = env('sap_gen');
		if (is_null($genPa)) {
			dd('enviroment is null', $genPa);
			//			abort(403, 'No env');
		}
		
		
		return $genPa;
	}
}
