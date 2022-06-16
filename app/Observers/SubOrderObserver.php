<?php

namespace App\Observers;

use App\SubOrder;

class SubOrderObserver
{
    /**
     * Handle the sub order "created" event.
     *
     * @param  \App\SubOrder  $subOrder
     * @return void
     */
    public function created(SubOrder $subOrder)
    {
        $subOrder->sub_order_code = mt_rand(10000000,99999999).$subOrder->id;
        $subOrder->save();
    }

    /**
     * Handle the sub order "updated" event.
     *
     * @param  \App\SubOrder  $subOrder
     * @return void
     */
    public function updated(SubOrder $subOrder)
    {
        //
    }

    /**
     * Handle the sub order "deleted" event.
     *
     * @param  \App\SubOrder  $subOrder
     * @return void
     */
    public function deleted(SubOrder $subOrder)
    {
        //
    }

    /**
     * Handle the sub order "restored" event.
     *
     * @param  \App\SubOrder  $subOrder
     * @return void
     */
    public function restored(SubOrder $subOrder)
    {
        //
    }

    /**
     * Handle the sub order "force deleted" event.
     *
     * @param  \App\SubOrder  $subOrder
     * @return void
     */
    public function forceDeleted(SubOrder $subOrder)
    {
        //
    }
}
