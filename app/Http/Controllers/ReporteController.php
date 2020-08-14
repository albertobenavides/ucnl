<?php

namespace App\Http\Controllers;

use App\Exports\ActasExport;
use App\Imports\ReportesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class ReporteController extends Controller
{
    public function NuevaActa(Request $request)
    {
        $request->validate([
            'reporte' => 'required|mimes:xlsx',
            'nivel' => 'required'
        ]);

        try {
            $registros = Excel::toArray(new ReportesImport, request()->file('reporte'));

            $registros = $registros[0];

            array_splice($registros, 0, 1);

            $c = collect();
            for ($i=0; $i < count($registros); $i++) { 
                
                $row = $registros[$i];
                $r = new stdClass;
                $r->id = $i + 1;
                $r->mat = $request->nivel != 'mae' ? $row[4] : $row[0];
                $r->nombre = $request->nivel != 'mae' ? $row[0] : $row[1];
                $r->apellido = $request->nivel != 'mae' ? $row[1] : $row[2];
                $r->actividades = $request->nivel != 'mae' ? $row[5] : $row[4];
                $r->parcial = $request->nivel != 'mae' ? $row[6] : $row[5];
                $r->final = $request->nivel != 'mae' ? $row[7] : $row[6];

                $total = $request->nivel != 'mae' ? 9 : 7;
                
                $r->total = round($row[$total]);
                switch (round($row[$total])) {
                    case 0:
                        $r->total_s = $row[$total] > 0 ? 'CINCO' : 'NC';
                        break;
                    case 1:
                        $r->total_s = 'CINCO';
                        break;
                    case 2:
                        $r->total_s = 'CINCO';
                        break;
                    case 3:
                        $r->total_s = 'CINCO';
                        break;
                    case 4:
                        $r->total_s = 'CINCO';
                        break;
                    case 5:
                        $r->total_s = 'CINCO';
                        break;
                    case 6:
                        $r->total_s = 'SEIS';
                        break;
                    case 7:
                        $r->total_s = 'SIETE';
                        break;
                    case 8:
                        $r->total_s = 'OCHO';
                        break;
                    case 9:
                        $r->total_s = 'NUEVE';
                        break;
                }

                if ($request->nivel != 'mae'){
                    $r->extra = round($row[8]);
                    switch (round($row[8])) {
                        case 0:
                            $r->extra_s = '';
                            $r->extra = '';
                            break;
                        case 1:
                            $r->extra_s = 'CINCO';
                            break;
                        case 2:
                            $r->extra_s = 'CINCO';
                            break;
                        case 3:
                            $r->extra_s = 'CINCO';
                            break;
                        case 4:
                            $r->extra_s = 'CINCO';
                            break;
                        case 5:
                            $r->extra_s = 'CINCO';
                            break;
                        case 6:
                            $r->extra_s = 'SEIS';
                            break;
                        case 7:
                            $r->extra_s = 'SIETE';
                            break;
                        case 8:
                            $r->extra_s = 'OCHO';
                            break;
                        case 9:
                            $r->extra_s = 'NUEVE';
                            break;
                    }
                }
                $c->add($r);
            };

            $nombre = 'acta_' . request()->file('reporte')->getClientOriginalName();

            return $c->sortBy('id')->downloadExcel(
                $nombre,
                $writerType = null,
                $headings = true
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
