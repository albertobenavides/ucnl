<?php

namespace App\Http\Controllers;

use App\Imports\ReportesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

class ReporteController extends Controller
{
    public function NuevaActa(Request $request){
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
                
                $total = $request->nivel != 'mae' ? 11 : 9;
                if ($request->nivel == 'lic1'){
                    $total = 12;
                }
                
                $subtotal = $row[$total];
                
                // Para las calificaciones extra
                if ($request->nivel != 'mae') {
                    if ($request->nivel == 'lic1'){ // Si es para primer semestre
                        if (is_numeric($row[8])){ // Si tiene un valor numérico
                            if (!is_numeric($r->actividades)){ // Si es '-' el valor de actividades, se pasa a 0
                                $r->actividades = 0;
                            }
                            $r->actividades = floatval($r->actividades) + floatval($row[8]); // Se suma el Cuestionario diagnóstico
                        }
                        $r->p_extra = $row[9];
                        $r->video = $row[10];
                    } else {
                        $r->p_extra = $row[8];
                        $r->video = $row[9];
                    }
                } else {
                    $r->p_extra = $row[7];
                    $r->video = $row[8];
                }

                if ($subtotal > 0 && $subtotal < 5.5){
                    $r->total = 5;
                    $r->total_s = 'CINCO';
                } else {
                    $r->total = round(floatval($subtotal));
                }

                switch ($r->total) {
                    case 0:
                        $r->total_s = 'NC';
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
                    case 10:
                        $r->total_s = 'DIEZ';
                        break;
                }

                if ($request->nivel != 'mae'){
                    $r->extra = round(floatval($row[10]));
                    if ($request->nivel == 'lic1'){
                        $r->extra = round(floatval($row[11]));
                    }
                    switch ($r->extra) {
                        case 0:
                            $r->extra_s = '';
                            $r->extra = '';
                            break;
                        case 1:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 2:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 3:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 4:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
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
                            $r->extra_s = 'OCHO';
                            $r->extra = 8;
                            break;
                        case 10:
                            $r->extra_s = 'OCHO';
                            $r->extra = 8;
                            break;
                    }
                    if ($r->total >= 7) {
                        $r->extra_s = '';
                        $r->extra = '';
                    }
                }
                $c->add($r);
            };

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $formato = $request->nivel != 'mae' ? "formato_bac.xlsx" : "formato_mae.xlsx";
            $spreadsheet = $reader->load($formato);
            $s = 0;
            $sheet = $spreadsheet->getSheet($s);
            if ($request->nivel == 'lic1'){
                $request->nivel = 'lic';
            }
            switch ($request->nivel) {
                case 'bac':
                    $nivel = "BACHILLERATO";
                    break;
                case 'lic':
                    $nivel = "LICENCIATURA";
                    break;
                case 'mae':
                    $nivel = "MAESTRÍA";
                    break;
            }
            $sheet->setCellValue("D2", $nivel);

            $i = 11;
            foreach ($c->sortBy('mat') as $t) {
                $sheet->setCellValue("B$i", $t->mat);
                $sheet->setCellValue("C$i", $t->nombre);
                $sheet->setCellValue("D$i", $t->apellido);
                $sheet->setCellValue("E$i", $t->actividades);
                if ($request->nivel != 'mae'){
                    $sheet->setCellValue("F$i", $t->p_extra);
                    $sheet->setCellValue("G$i", $t->video);

                    $sheet->setCellValue("H$i", $t->parcial);
                    $sheet->setCellValue("I$i", $t->final);
                    
                    $sheet->setCellValue("J$i", $t->total);
                    $sheet->setCellValue("K$i", $t->total_s);

                    $sheet->setCellValue("L$i", $t->extra);
                    $sheet->setCellValue("M$i", $t->extra_s);

                    $sheet->setCellValue("L5", env('FECHA_BAC'));
                } else {
                    $sheet->setCellValue("F$i", $t->p_extra);
                    $sheet->setCellValue("G$i", $t->video);

                    $sheet->setCellValue("H$i", $t->parcial);
                    $sheet->setCellValue("I$i", $t->final);
                    $sheet->setCellValue("J$i", $t->total);
                    $sheet->setCellValue("K$i", $t->total_s);

                    $sheet->setCellValue("I5", env('FECHA_MAE'));
                }
                $sheet->setCellValue("D71", env('FECHA', ''));

                $i += 1;

                if ($i == 71){
                    $i = 11;
                    $s += 1;
                    $sheet = $spreadsheet->getSheet($s);
                    $sheet->setCellValue("D2", $nivel);
                }
            }

            $nombre = 'acta_' . request()->file('reporte')->getClientOriginalName();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $nombre .'"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } catch (\Throwable $th) {
            return $th;
            return redirect('/')->withErrors([
                'message1'=>'Error al leer el archivo',
                'message2'=>'Asegúrate de seleccionar todos los totales antes de exportar',
                'message3' => $th->getMessage()
            ]);
        }
    }

    public function ViejaActa(Request $request){
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
                
                if ($row[$total] > 0 && $row[$total] < 5.5){
                    $r->total = 5;
                    $r->total_s = 'CINCO';
                } else {
                    $r->total = round($row[$total]);
                }

                switch ($r->total) {
                    case 0:
                        $r->total_s = 'NC';
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
                    case 10:
                        $r->total_s = 'DIEZ';
                        break;
                }

                if ($request->nivel != 'mae'){
                    $r->extra = round($row[8]);
                    switch ($r->extra) {
                        case 0:
                            $r->extra_s = '';
                            $r->extra = '';
                            break;
                        case 1:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 2:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 3:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
                            break;
                        case 4:
                            $r->extra_s = 'CINCO';
                            $r->extra = 5;
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
                            $r->extra_s = 'OCHO';
                            $r->extra = 8;
                            break;
                        case 10:
                            $r->extra_s = 'OCHO';
                            $r->extra = 8;
                            break;
                    }
                }
                $c->add($r);
            };

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $formato = $request->nivel != 'mae' ? "formato_bac_viejo.xlsx" : "formato_mae_viejo.xlsx";
            $spreadsheet = $reader->load($formato);
            $s = 0;
            $sheet = $spreadsheet->getSheet($s);
            switch ($request->nivel) {
                case 'bac':
                    $nivel = "BACHILLERATO";
                    break;
                case 'lic':
                    $nivel = "LICENCIATURA";
                    break;
                case 'mae':
                    $nivel = "MAESTRÍA";
                    break;
                                
            }
            $sheet->setCellValue("D2", $nivel);

            $i = 11;
            foreach ($c->sortBy('mat') as $t) {
                $sheet->setCellValue("B$i", $t->mat);
                $sheet->setCellValue("C$i", $t->nombre);
                $sheet->setCellValue("D$i", $t->apellido);
                $sheet->setCellValue("E$i", $t->actividades);
                $sheet->setCellValue("F$i", $t->parcial);
                $sheet->setCellValue("G$i", $t->final);
                $sheet->setCellValue("H$i", $t->total);
                $sheet->setCellValue("I$i", $t->total_s);
                if ($request->nivel != 'mae'){
                    $sheet->setCellValue("J$i", $t->extra);
                    $sheet->setCellValue("K$i", $t->extra_s);
                }

                $i += 1;

                if ($i == 71){
                    $i = 11;
                    $s += 1;
                    $sheet = $spreadsheet->getSheet($s);
                }
            }

            $nombre = 'acta_' . request()->file('reporte')->getClientOriginalName();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $nombre .'"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');

        } catch (\Throwable $th) {
            return redirect('/')->withErrors([
                'message1'=>'Error al leer el archivo',
                'message2'=>'Asegúrate de seleccionar todos los totales antes de exportar',
            ]);
        }
    }
}
