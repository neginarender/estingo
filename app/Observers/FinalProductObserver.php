<?php

namespace App\Observers;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use App\FinalProduct;

class FinalProductObserver
{
    /**
     * Handle the final product "created" event.
     *
     * @param  \App\FinalProduct  $finalProduct
     * @return void
     */
    public $client;
    public function __construct(){
        $this->client = new \GuzzleHttp\Client([
            'request.options' => array(
                'exceptions' => false,
              )
        ]);
    }
    
    public function curlRequest($params,$url){
    $curl = curl_init();
    $params = json_encode(['params'=>$params]);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$params,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    info($response);

    }

    public function created(FinalProduct $finalProduct)
    {
        //send record to elastic
       return $this->addOrUpdateRecord($finalProduct);
    }

    /**
     * Handle the final product "updated" event.
     *
     * @param  \App\FinalProduct  $finalProduct
     * @return void
     */
    public function updated(FinalProduct $finalProduct)
    {
        //update record to elastic'
        return $this->addOrUpdateRecord($finalProduct);
    }

    /**
     * Handle the final product "deleted" event.
     *
     * @param  \App\FinalProduct  $finalProduct
     * @return void
     */
    public function deleted(FinalProduct $finalProduct)
    {
        
    }

    /**
     * Handle the final product "restored" event.
     *
     * @param  \App\FinalProduct  $finalProduct
     * @return void
     */
    public function restored(FinalProduct $finalProduct)
    {
        //
    }

    /**
     * Handle the final product "force deleted" event.
     *
     * @param  \App\FinalProduct  $finalProduct
     * @return void
     */
    public function forceDeleted(FinalProduct $finalProduct)
    {
         //Delete Product from elastic search
        //Delete Product from elastic search
        info($finalProduct);
        $params = [
            'id'    => $finalProduct->id
        ];
        
        // Delete doc at /my_index/_doc_/my_id
        $url = "http://elastic.rozana.in/api/v1/elastic-search/delete-record";
        $response = $this->curlRequest($params,$url);
        info("Deleted observer from elasticsearch");
        info($response);
    }

    public function addOrUpdateRecord($finalProduct){
        $params = [
            'index' => 'products',
            'id'    => $finalProduct->id,
            'type'=>"_doc",
            'body'  => [
                'name'=>$finalProduct->name, 
                'product_id'=>$finalProduct->product_id, 
                'category_id'=>$finalProduct->category_id,
                'slug'=>$finalProduct->slug,
                'subcategory_id'=>$finalProduct->subcategory_id,
                'subsubcategory_id'=>$finalProduct->subsubcategory_id,
                'stock_price'=>$finalProduct->stock_price,
                'base_price'=>$finalProduct->base_price,
                'variant'=>$finalProduct->variant,
                'tags'=>$finalProduct->tags,
                'json_tags'=>$finalProduct->json_tags,
                'quantity'=>$finalProduct->quantity,
                'max_purchase_qty'=>$finalProduct->max_purchase_qty,
                'discount_type'=>$finalProduct->discount_type,
                'discount_percentage'=>$finalProduct->discount_percentage,
                'customer_off'=>$finalProduct->customer_off,
                'thumbnail_image'=>$finalProduct->thumbnail_image,
                'photos'=>$finalProduct->photos,
                'sorting_hub_id'=>$finalProduct->sorting_hub_id,
                'flash_deal'=>$finalProduct->flash_deal,
                'top_product'=>$finalProduct->top_product,
                'published'=>$finalProduct->published,
                'choice_options'=>$finalProduct->choice_options,
                'unit'=>$finalProduct->unit,
                'rating'=>$finalProduct->rating,
                'sales'=>$finalProduct->sales,
                'links' => json_encode([
                    'details' => route('products.show', $finalProduct->id),
                    'reviews' => route('api.reviews.index', $finalProduct->id),
                    'related' => route('products.related', $finalProduct->id)
                ])
            ]
        ];
        
        // Update doc at /my_index/_doc/my_id
        $url = 'http://elastic.rozana.in/api/v1/elastic-search/create-update-record';
        $response = $this->curlRequest($params,$url);
        info($response);
        info("Elastic Final Product Created/Updated");
        
    }
}
