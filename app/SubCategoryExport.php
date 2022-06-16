<?php

namespace App;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubCategoryExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return SubCategory::all();
    }

    public function headings(): array
    {
        return [
           'name',
           'Sub Category id',
           "category id"
        ];
    }

    /**
    * @var Product $product
    */
    public function map($subcategory): array
    {
        return [
            $subcategory->name,
            $subcategory->id,
            $subcategory->category_id
            
        ];
    }
}
