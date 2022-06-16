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

class PeerPartnerExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($status = NULL)
    {

       $this->status = $status;
    }
    
    public function collection()
    {

    	if(isset($this->status) && $this->status != NULL){
    			$peerpartner = PeerPartner::where('verification_status', $this->status)->get();
    	}else{
    			$peerpartner = PeerPartner::all();
    	}    	
    	return $peerpartner;

    }

    public function headings(): array
    {
			
        return [
            'Name',
            'Type',
            'Parent',
            'Phone',
            'Email Address',
            'Refferal Code',
            'Address 1',
            'Address 2',
            'Address 3',
            'Zone',
            'Added By',
            'Approval',
            'Master',
            'Created Date', 
        ];
    }

    /**
    * @var Order $order
    */
    public function map($peerpartner): array
    {

    	$name = $peerpartner->user['name'];
    	$type = ($peerpartner->peer_type == 'master')?'Master':'Sub Peer'; 
			$master_name = PeerPartner::where('id', $peerpartner->parent)->first('name');
	      if(!empty($master_name)){
	          $parent = $master_name->name;
	      }else{
	          $parent = 'NA';
	      }

	    $user = User::find($peerpartner->added_by);
	    $added_by = ($user['user_type'] == 'admin')?'Admin':'Customer';
	    $approved = ($peerpartner->verification_status == 1)?'Approved':'Not Approved';
	    if($peerpartner->email != 'defaultpeer@rozana.in'){
	    		if($peerpartner->peertype_approval == 1){
	    		 	$master = 'Master';
	    		}else{
	    			$master = '-';
	    		}
	    }else{ 
	    		$master ='Default Master Peer';
	    }
	    $created_at = date('d M Y', strtotime($peerpartner->created_at));
		return [
            $name,
						$type,
            $parent,
            $peerpartner->user['phone'],
            $peerpartner->user['email'],
            $peerpartner->code,
            $peerpartner->address,
            $peerpartner->addressone,
            $peerpartner->addresstwo,
            $peerpartner->zone,
            $added_by,
            $approved,
            $master,
            $created_at,
        ]; 

    }
}
