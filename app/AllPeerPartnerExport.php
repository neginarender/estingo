<?php

namespace App;

use App\Product;
use App\Color;
use Auth;
use Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;
use App\ShortingHub;
use App\PeerPartner;
use App\User;
use App\Category;
use App\PeerSetting;
use App\ProductStock;
use App\MappingProduct;
use App\OtpMapping;
use App\OrderReferalCommision;

class AllPeerPartnerExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($status = NULL)
    {

       $this->status = $status;
    }
    
    public function collection()
    {
        // dd('ddd');
    	$peerpartner = PeerPartner::where('peer_type', 'sub')->OrderBy('parent', 'DESC')->get();    	
    	return $peerpartner;
    }

    public function headings(): array
    {
			
        return [
            'Master Name/Phone',            
            'Master Email/Code',
            'Order Amount',
            'Master Commission',
            'Peer Commission',            
            'Peer Name/Phone',
            'Peer Email/Code',
            'Master Created Date',
            'Master Address 1',
            'Master Address 2',
            'Master Address 3',
            'Peer Created Date', 
            'Peer Address 1',
            'Peer Address 2',
            'Peer Address 3'
        ];
    }

    /**
    * @var Order $order
    */
    public function map($peerpartner): array
    {

    	$name = $peerpartner->user['name'];    	
	    $created_at = date('d M Y', strtotime($peerpartner->created_at));

        $masters = PeerPartner::where('id', $peerpartner->parent)->first();
        $master_created_at = date('d M Y', strtotime($masters['created_at']));

        $all_commission = OrderReferalCommision::where('wallet_status', 1)->where('partner_id', $peerpartner->user_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(master_discount) as total_master, SUM(referal_commision_discount) as total_peerdiscount')->groupBy('partner_id')->first(); 
        

		return [
            $masters['name'].' / '.$masters['phone'],            
            $masters['email'].' / '.$masters['code'],
            $all_commission['total_orderamount'],
            $all_commission['total_master'], 
            $all_commission['total_peerdiscount'],            
            $name.' / '.$peerpartner->user['phone'],                      
            $peerpartner->user['email'].' / '.$peerpartner->code,
            $master_created_at,
            $masters['address'],
            $masters['addressone'],
            $masters['addresstwo'],
            $created_at,
            $peerpartner->address,
            $peerpartner->addressone,
            $peerpartner->addresstwo
        ]; 

    }
}
