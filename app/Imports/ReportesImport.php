<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use stdClass;

class ReportesImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $c = collect();
        foreach ($rows as $row) 
        {
            $r = new stdClass;
            $r->nombre = $row[0];
            $r->apellido = $row[1];
            $r->id = $row[4];
            $r->calif = $row[6];
            $r->extra = $row[5];
            $c->add($r);
        };
        return $c;
    }
}
