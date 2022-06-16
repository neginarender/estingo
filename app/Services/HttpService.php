<?php
namespace App\Services;

class HttpService{
    protected $client;
    public function __construct($base_url){
        $this->client = new \GuzzleHttp\Client(['base_uri' => $base_url]);
    }
    public function apiRequest($method,$endpoint,$body){
        try{

        $response = $this->client->request($method, $endpoint, $body);
        //dd($response->getBody()->__toString());
        if($response->getReasonPhrase()){
            return response()->json([
                'success'=>true,
                'response'=>json_decode((string)$response->getBody())
            ]);
        }

    }  catch (\GuzzleHttp\Exception\ClientException $e) {
      
    } catch(\GuzzleHttp\Exception\ServerException $e){
        
    } catch(\GuzzleHttp\Exception\RequestException $e){
        
    } catch(\GuzzleHttp\Exception\BadResponseException $e){
        
    } catch(\Exception $e){
        
    }
        dd($e);
        return response()->json([
            'success'=>false,
            'response'=>json_decode((string)$e->getResponse()->getBody())->error->description
        ]);

    }
}