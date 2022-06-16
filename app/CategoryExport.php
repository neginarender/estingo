<?php

namespace App;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Category::all();
    }

    public function headings(): array
    {
        return [
           'Name',
           'Category id',
        ];
    }

    /**
    * @var Product $product
    */
    public function map($category): array
    {
        return [
            $category->name,
            $category->id
        ];
    }
}
