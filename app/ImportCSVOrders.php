<?php

namespace App;

use App\DOFO;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Auth;
use App\CsvOrders;

class ImportCSVOrders implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if($row['email'] != null){
            return new CsvOrders([
            'email'     => $row['email'],
            'product_ids' => json_encode(explode(',',$row['product_ids'])),
            'product_qty' => json_encode(explode(',',$row['product_quantity'])),
            'peer_code'   => $row['peer_code'],
            'created_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['created_date']),
            'created_time' => $row['created_time'],
            'order_code'   => $row['order_code'],
            ]);
        }
    }


    public function rules(): array
    {
        return [
             // Can also use callback validation rules
            //  'unit_price' => function($attribute, $value, $onFailure) {
            //       if (!is_numeric($value)) {
            //            $onFailure('Unit price is not numeric');
            //       }
            //   }
        ];
    }

}
