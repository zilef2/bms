<?php

namespace App\Http\Controllers;

use AllowDynamicProperties;
use App\Models\Proceso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

#[AllowDynamicProperties] class PruebasController extends Controller {
	
	public function __construct() {
		$this->titleindex = 'Rama Judicial';
	}
	
	public function index(Request $req) {
		Carbon::setLocale('es');
		
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
			$procesoDB = Proceso::where('llave_proceso', $numero_radicacion)->first();
			
			//			if ($procesoDB) {
			//				// Encontrado en la BD, usarlo
			//				$proceso = $procesoDB->toArray();
			//				$proceso = $this->traduccionBackFront($proceso);
			//				//				dd($proceso);
			//				$Numprocesos = 1; // O puedes guardar en la BD el número real si lo necesitas
			//				
			//				$urlDetalle = "https://consultaprocesos.ramajudicial.gov.co:448/api/v2/Proceso/Detalle/{$proceso['idProceso']}";
			//				$urlactuaciones = "https://consultaprocesos.ramajudicial.gov.co:448/api/v2/Proceso/Actuaciones/{$proceso['idProceso']}";
			//				
			//				$Detalle = $this->obtenerAPIProceso($proceso['idProceso'],$urlDetalle);
			//				$Actuaciones = $this->obtenerAPIProceso($proceso['idProceso'],$urlactuaciones);
			//			}
			//			else {
			// No está en la BD, buscar en la API externa
			$data = $this->GetJsonRama($numero_radicacion);
			
			if (isset($data['procesos']) && is_array($data['procesos']) && count($data['procesos']) > 0) {
				$Numprocesos = count($data['procesos']);
				$proceso = $data['procesos'][0];
				
				$urlDetalle = "https://consultaprocesos.ramajudicial.gov.co:448/api/v2/Proceso/Detalle/{$proceso['idProceso']}";
				$urlactuaciones = "https://consultaprocesos.ramajudicial.gov.co:448/api/v2/Proceso/Actuaciones/{$proceso['idProceso']}";
				$Detalle = $this->obtenerAPIProceso($proceso['idProceso'], $urlDetalle);
				$Actuaciones = $this->obtenerAPIProceso($proceso['idProceso'], $urlactuaciones);
				$UltimaActuacion = $Detalle['ultimaActualizacion'] ? Carbon::parse($Detalle['ultimaActualizacion'])->diffForHumans() : null;
				$proceso['validacioncini'] = true;
				// Guardar en la BD para futuras consultas
				Proceso::create([
					                'llave_proceso'          => $proceso['llaveProceso'] ?? $numero_radicacion,
					                'idProceso'              => $proceso['idProceso'] ?? null,
					                'id_conexion'            => $proceso['idConexion'] ?? null,
					                'fecha_proceso'          => isset($proceso['fechaProceso']) ? substr($proceso['fechaProceso'], 0, 10) : null,
					                'fecha_ultima_actuacion' => isset($proceso['fechaUltimaActuacion']) ? substr($proceso['fechaUltimaActuacion'], 0, 10) : null,
					                'despacho'               => $proceso['despacho'] ?? null,
					                'departamento'           => $proceso['departamento'] ?? null,
					                'sujetos_procesales'     => $proceso['sujetosProcesales'] ?? null,
					                'es_privado'             => $proceso['esPrivado'] ?? false,
					                'cant_filas'             => $proceso['cantFilas'] ?? null,
					                'validacioncini'         => true,
					                'pdf_path'               => null,
					                'Numprocesos'            => $Numprocesos,
					                'UltimaActuacion'        => $UltimaActuacion,
				                ]);
			}
			else {
				$mensajeError = 'No se encontraron procesos para el número de radicación: ' . $numero_radicacion;
			}
			
			//			}
		}
		
		
		return Inertia::render('rama', [
			'title'                 => $this->titleindex,
			'Numprocesos'           => $Numprocesos,
			'proceso'               => $proceso,
			'UltimaActuacion'       => $UltimaActuacion ?? '',
			'obtenerDetalleProceso' => $Detalle ?? [],
			'Actuaciones'           => $Actuaciones['actuaciones'] ?? [],
		'PagActuaciones'            => $Actuaciones['paginacion'] ?? [],
			'filters'               => $req->all(['search', 'field']),
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
	
	public function obtenerAPIProceso($idProceso, $url) {
		
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
		
		
		return json_decode($response, true);
	}
	
	private function traduccionBackFront($proceso) {
		$proceso['validacioncini'] = true;
		$proceso['llaveProceso'] = $proceso['llave_proceso'];
		$proceso['idConexion'] = $proceso['id_conexion'];
		$proceso['fechaProceso'] = $proceso['fecha_proceso'];
		$proceso['fechaUltimaActuacion'] = $proceso['fecha_ultima_actuacion'];
		$proceso['sujetosProcesales'] = $proceso['sujetos_procesales'];
		$proceso['esPrivado'] = $proceso['es_privado'];
		$proceso['cantFilas'] = $proceso['cant_filas'];
		
		
		return $proceso;
	}
	
}
