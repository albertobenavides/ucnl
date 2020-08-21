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
            $formato = $request->nivel != 'mae' ? "formato_lic.xlsx" : "formato_mae.xlsx";
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
