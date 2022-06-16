<?php

namespace App;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubSubCategoryExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return SubSubCategory::all();
    }

    public function headings(): array
    {
        return [
           'Sub-Sub-Category Name',
           'Sub-Sub-Category id',
           "Sub-category id"
        ];
    }

    /**
    * @var Product $product
    */
    public function map($subsubcategory): array
    {
        return [
            $subsubcategory->name,
            $subsubcategory->id,
            $subsubcategory->sub_category_id
            
        ];
    }
}
