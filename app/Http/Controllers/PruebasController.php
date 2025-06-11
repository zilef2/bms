<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Inertia\Inertia;

#[AllowDynamicProperties] class PruebasController extends Controller {
	
	public function __construct() {
		$this->titleindex = 'Rama Judicial';
	}
	
	public function index(Request $req) {
		$Numprocesos = 0;
		$numero_radicacion = null;
		$proceso = ['validacioncini' => false];
		
		if ($req->has('search')) {
			$radicacion = $req->search;
			// Validar exactamente 23 dígitos numéricos como string
			if (is_string($radicacion) && strlen($radicacion) === 23 && preg_match('/^\d{23}$/', $radicacion)) {
				$numero_radicacion = $radicacion;
			}
		}
		
		if ($numero_radicacion) {
			// Buscar primero en la base de datos
			$procesoDB = \App\Models\Proceso::where('llave_proceso', $numero_radicacion)->first();
			
			if ($procesoDB) {
				// Encontrado en la BD, usarlo
				$proceso = $procesoDB->toArray();
				$proceso['validacioncini'] = true;
				$Numprocesos = 1; // O puedes guardar en la BD el número real si lo necesitas
			}
			else {
				// No está en la BD, buscar en la API externa
				$data = $this->GetJsonRama($numero_radicacion);
				
				if (isset($data['procesos']) && is_array($data['procesos']) && count($data['procesos']) > 0) {
					$Numprocesos = count($data['procesos']);
					$proceso = $data['procesos'][0];
					$proceso['validacioncini'] = true;
					// Guardar en la BD para futuras consultas
					\App\Models\Proceso::create([
						                            'llave_proceso'          => $proceso['llaveProceso'] ?? $numero_radicacion,
						                            'id_proceso'             => $proceso['idProceso'] ?? null,
						                            'id_conexion'            => $proceso['idConexion'] ?? null,
						                            'fecha_proceso'          => isset($proceso['fechaProceso']) ? substr($proceso['fechaProceso'], 0, 10) : null,
						                            'fecha_ultima_actuacion' => isset($proceso['fechaUltimaActuacion']) ? substr($proceso['fechaUltimaActuacion'], 0, 10) : null,
						                            'despacho'               => $proceso['despacho'] ?? null,
						                            'departamento'           => $proceso['departamento'] ?? null,
						                            'sujetos_procesales'     => $proceso['sujetosProcesales'] ?? null,
						                            'es_privado'             => $proceso['esPrivado'] ?? false,
						                            'cant_filas'             => $proceso['cantFilas'] ?? null,
						                            'validacioncini'         => true,
						                            // 'pdf_path' => null, // Puedes dejarlo null por ahora
					                            ]);
				}
			}
		}
		
		
		return Inertia::render('rama', [
			'title'       => $this->titleindex,
			'Numprocesos' => $Numprocesos,
			'proceso'     => $proceso,
			'filters'     => $req->all(['search', 'field']),
		]);
	}
	
	/**
	 * @param float|int|string $numero_radicacion
	 * @return mixed
	 */
	public function GetJsonRama(float|int|string $numero_radicacion): mixed {
		$url = "https://consultaprocesos.ramajudicial.gov.co:448/api/v2/Procesos/Consulta/NumeroRadicacion?numero={$numero_radicacion}&SoloActivos=false&pagina=1";
		// Inicializar cURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json, text/plain, */*',
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
			'Origin: https://consultaprocesos.ramajudicial.gov.co',
			'Referer: https://consultaprocesos.ramajudicial.gov.co/',
		]);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response, true);
		
		
		return $data;
	}
}
//0 => array:10 [▼
//    "idProceso" => 146028713
//    "idConexion" => 392
//    "llaveProceso" => "11001020300020250066800"
//    "fechaProceso" => "2025-02-12T00:00:00"
//    "fechaUltimaActuacion" => "2025-04-09T00:00:00"
//    "despacho" => "DESPACHO 000 - CORTE SUPREMA DE JUSTICIA - CIVIL - BOGOTÁ *"
//    "departamento" => "BOGOTÁ"
//    "sujetosProcesales" => "Demandante: DARWIN MORENO LOZANO | Demandante: AULIO MORENO LOZANO | Dema
//    "esPrivado" => false
//    "cantFilas" => -1
//  ]
