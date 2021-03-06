<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $serial_number = trim($row['alrkm_altslsly']);
        $name    = trim($row['alasm']);
        $section = trim($row['alksm']);
        $status  = trim($row['odaa_altalb']);

        if(!is_null($row['alrkm_altslsly']) && !is_null($row['alasm']) && !is_null($row['alksm']) && !is_null($row['odaa_altalb'])){

            Student::create([
                'serial_number' => $serial_number,
                'name'    => $name,
                'section' => $section == 'بنين' ? '1' : '2',
                'status'  => $status == 'منتظم' ? '1' : '0',
            ]);
        }
    }

    public function batchSize(): int
    {
        return 300;
    }

    public function chunkSize(): int
    {
        return 300;
    }

}
