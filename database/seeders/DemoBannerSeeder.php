<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class DemoBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('banners')->delete();
        //create categories by vendor type and assign to random products and services
        //glover
        $banner = new Banner();
        $banner->link = "https://codecanyon.net/item/fuodz-grocery-food-pharmacy-store-parcelcourier-delivery-mobile-app-with-php-laravel-backend/31145802";
        $banner->featured = true;
        $banner->save();
        $banner->clearMediaCollection();

        $imageUrl = "https://market-resized.envatousercontent.com/codecanyon.net/files/524543847/Glover%20fresh%20preview%20-%202024.png?auto=format&q=94&cf_fit=crop&gravity=top&h=8000&w=590&s=09b3b7a6d96adc3ec1b1b395b3147d787a488e9571b73283e81e871381061c26";
        $banner->addMediaFromUrl($imageUrl)->toMediaCollection();
        //instahaul
        $banner = new Banner();
        $banner->link = "https://codecanyon.net/item/instahaul-package-courier-delivery-app-with-admin-backend/50475924";
        $banner->featured = true;
        $banner->save();
        $banner->clearMediaCollection();

        $imageUrl = "https://market-resized.envatousercontent.com/codecanyon.net/files/511817927/SM%20-%20Inline%20Preview%20.png?auto=format&q=94&cf_fit=crop&gravity=top&h=8000&w=590&s=e673ebdcba7bfcaa457d7801789ffbd2003ad0fd42ddaaa7d25f853c38a101b9";
        $banner->addMediaFromUrl($imageUrl)->toMediaCollection();
        //trackPOS
        $banner = new Banner();
        $banner->link = "https://codecanyon.net/item/trackpos-pos-with-inventory-management-system/35826435";
        $banner->featured = true;
        $banner->save();
        $banner->clearMediaCollection();

        $imageUrl = "https://camo.envatousercontent.com/a52d2af7f7c0e6974c59b005dc1ca421b4b540b3/68747470733a2f2f7265732e636c6f7564696e6172792e636f6d2f736e61707461736b2f696d6167652f75706c6f61642f76313634323739353032392f747261636b504f532f315f75666b736e692e706e67";
        $banner->addMediaFromUrl($imageUrl)->toMediaCollection();
        //video call
        $banner = new Banner();
        $banner->link = "https://codecanyon.net/item/meetup-android-ios-and-web-video-conference-app-for-meeting-webinar-classes/29935384";
        $banner->featured = true;
        $banner->save();
        $banner->clearMediaCollection();

        $imageUrl = "https://market-resized.envatousercontent.com/codecanyon.net/files/501355574/Meetup.png?auto=format&q=94&cf_fit=crop&gravity=top&h=8000&w=590&s=e08ffa50e3ab697d7b0c457998575ea54a482c173012e05310c208563b6f04c8";
        $banner->addMediaFromUrl($imageUrl)->toMediaCollection();

        //our website
        $banner = new Banner();
        $banner->link = "https://setup.edentech.online/";
        $banner->featured = true;
        $banner->save();
        $banner->clearMediaCollection();

        $imageUrl = "https://nimbus-screenshots.s3.amazonaws.com/s/d020d06e84d069030c61f6bc402ed960.png";
        $banner->addMediaFromUrl($imageUrl)->toMediaCollection();
    }
}
