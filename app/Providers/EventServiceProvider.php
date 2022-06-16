<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Observers\OrderDetailObserver;
use App\Observers\ApiOrderDetailObserver;
use App\Observers\SubOrderObserver;
use App\Observers\MappingProductObserver;
use App\Observers\PeerSettingObserver;
use App\Observers\FinalProductObserver;

use App\Events\OrderPlacedEmail;
use App\Listeners\SendOrderPlacedEmail;

use App\OrderDetail;
use App\Models\OrderDetail as ApiOrderDetail;
use App\SubOrder;
use App\MappingProduct;
use App\PeerSetting;
use App\FinalProduct;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    Registered::class => [
      SendEmailVerificationNotification::class,
    ],
    OrderPlacedEmail::class => [
      SendOrderPlacedEmail::class,
  ],
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();

    //
    SubOrder::observe(SubOrderObserver::class);
    //OrderDetail::observe(OrderDetailObserver::class);
    //ApiOrderDetail::observe(ApiOrderDetailObserver::class);
    MappingProduct::observe(MappingProductObserver::class);
    PeerSetting::observe(PeerSettingObserver::class);
    FinalProduct::observe(FinalProductObserver::class);
  }
}
