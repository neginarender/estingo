<?php

namespace App\Observers;

use App\PeerSetting;

class PeerSettingObserver
{
    /**
     * Handle the peer setting "created" event.
     *
     * @param  \App\PeerSetting  $peerSetting
     * @return void
     */
    public function created(PeerSetting $peerSetting)
    {
        $product_id = substr($peerSetting->product_id,1,-1);
        $shortId['sorting_hub_id'] = json_decode($peerSetting->sorting_hub_id)[0];
        finalProduct($shortId,$product_id,'Peer observer Created');
    }

    /**
     * Handle the peer setting "updated" event.
     *
     * @param  \App\PeerSetting  $peerSetting
     * @return void
     */
    public function updated(PeerSetting $peerSetting)
    {
        //
    }

    /**
     * Handle the peer setting "deleted" event.
     *
     * @param  \App\PeerSetting  $peerSetting
     * @return void
     */
    public function deleted(PeerSetting $peerSetting)
    {
        //
    }

    /**
     * Handle the peer setting "restored" event.
     *
     * @param  \App\PeerSetting  $peerSetting
     * @return void
     */
    public function restored(PeerSetting $peerSetting)
    {
        //
    }

    /**
     * Handle the peer setting "force deleted" event.
     *
     * @param  \App\PeerSetting  $peerSetting
     * @return void
     */
    public function forceDeleted(PeerSetting $peerSetting)
    {
        //
    }
}
