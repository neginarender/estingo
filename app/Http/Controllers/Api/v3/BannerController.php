<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\BannerCollection;
use App\Models\Banner;
use App\MasterBanner;

class BannerController extends Controller
{

    public function index()
    {
    
        return new BannerCollection(MasterBanner::where('published',1)->get());
    }
}
