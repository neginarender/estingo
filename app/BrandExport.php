<?php

namespace App;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrandExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Brand::all();
    }

    public function headings(): array
    {
        return [
           'name',
           'id'
        ];
    }

    /**
    * @var Product $product
    */
    public function map($brand): array
    {
        return [
            $brand->name,
            $brand->id
            
        ];
    }
}
