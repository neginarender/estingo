<?php

namespace App\Observers;

use App\MappingProduct;
use App\FinalProduct;

class MappingProductObserver
{
    /**
     * Handle the mapping product "created" event.
     *
     * @param  \App\MappingProduct  $mappingProduct
     * @return void
     */
    public function created(MappingProduct $mappingProduct)
    {
        //
        // info("mapping id: ".$mappingProduct->id);
        // info("product id: ".$mappingProduct->product_id);
        // info("sorting hub id ".$mappingProduct->sorting_hub_id);
        //finalProduct(['sorting_hub_id'=>$mappingProduct->sorting_hub_id],$mappingProduct->product_id,'created');
    }

    /**
     * Handle the mapping product "updated" event.
     *
     * @param  \App\MappingProduct  $mappingProduct
     * @return void
     */
    public function updated(MappingProduct $mappingProduct)
    {
        //
        // info("mapping id: ".$mappingProduct->id);
        // info("product id: ".$mappingProduct->product_id);
        // info("sorting hub id ".$mappingProduct->sorting_hub_id);

        finalProduct(['sorting_hub_id'=>$mappingProduct->sorting_hub_id],$mappingProduct->product_id,'updated');
    }

    /**
     * Handle the mapping product "deleted" event.
     *
     * @param  \App\MappingProduct  $mappingProduct
     * @return void
     */
    public function deleted(MappingProduct $mappingProduct)
    {
        $response = FinalProduct::where(['product_id'=>$mappingProduct->product_id,'sorting_hub_id'=>$mappingProduct->sorting_hub_id])->delete();
        info("Final Product deleted");
        info($response);
    }

    /**
     * Handle the mapping product "restored" event.
     *
     * @param  \App\MappingProduct  $mappingProduct
     * @return void
     */
    public function restored(MappingProduct $mappingProduct)
    {
        //
    }

    /**
     * Handle the mapping product "force deleted" event.
     *
     * @param  \App\MappingProduct  $mappingProduct
     * @return void
     */
    public function forceDeleted(MappingProduct $mappingProduct)
    {
        //
    }
}
