<?php

namespace App;

use App\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DofoOrderExport implements FromCollection
{
    public function collection()
    {
        $order = Order::select('id','platform','user_id')->where('payment_status','paid')->get();
        dd($order->orderDetails);
    }

    public function headings(): array
    {
        return [
            'id',
            'platform',
            'user_id',
        ];
    }
}
