<?php

namespace App;

use App\DOFO;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Auth;

class ImportDOFO implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $existingDofo = DOFO::where('email',$row['email'])->exists();
        if(!empty($row['name']) && $existingDofo != true){
            return new DOFO([
                'name'     => $row['name'],
                'email'    => $row['email'],
                'phone'    => $row['mobile'],
                'pincode'    => $row['pincode'],
                'address'    => $row['address'],
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
