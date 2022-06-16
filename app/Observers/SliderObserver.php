<?php

namespace App\Observers;

use App\Slider;

class SliderObserver
{
    /**
     * Handle the slider "created" event.
     *
     * @param  \App\Slider  $slider
     * @return void
     */
    public function created(Slider $slider)
    {
        Cache::forget('sliders');
    }

    /**
     * Handle the slider "updated" event.
     *
     * @param  \App\Slider  $slider
     * @return void
     */
    public function updated(Slider $slider)
    {
        //
    }

    /**
     * Handle the slider "deleted" event.
     *
     * @param  \App\Slider  $slider
     * @return void
     */
    public function deleted(Slider $slider)
    {
        //
    }

    /**
     * Handle the slider "restored" event.
     *
     * @param  \App\Slider  $slider
     * @return void
     */
    public function restored(Slider $slider)
    {
        //
    }

    /**
     * Handle the slider "force deleted" event.
     *
     * @param  \App\Slider  $slider
     * @return void
     */
    public function forceDeleted(Slider $slider)
    {
        //
    }
}
