<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\BannerCollection;
use App\Models\Banner;
use App\MasterBanner;

class BannerController extends Controller
{

    public function index()
    {
    
        return new BannerCollection(MasterBanner::where('published',1)->get());
    }
}
