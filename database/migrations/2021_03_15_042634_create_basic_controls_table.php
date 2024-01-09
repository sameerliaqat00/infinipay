<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBasicControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('basic_controls', function (Blueprint $table) {
            $table->id();
            $table->string('currency_layer_access_key')->nullable();
            $table->boolean('currency_layer_auto_update')->default(0);
            $table->string('currency_layer_auto_update_at')->nullable();
            $table->string('coin_market_cap_app_key')->nullable();
            $table->boolean('coin_market_cap_auto_update')->default(0);
            $table->string('coin_market_cap_auto_update_at')->nullable();
            $table->string('site_title')->nullable();
            $table->string('primaryColor')->nullable();
            $table->string('yellow')->nullable();
            $table->string('lightBlue')->nullable();
            $table->string('blueberry')->nullable();
            $table->string('mobileApp')->nullable();
            $table->string('gradient2')->nullable();
            $table->string('pink')->nullable();
            $table->string('footerBaseColor')->nullable();
            $table->string('footerSecondaryColor')->nullable();
            $table->string('copyRightColor')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('base_currency')->nullable();
            $table->string('base_currency_code')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('fraction_number')->nullable();
            $table->string('paginate')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('social_title')->nullable();
            $table->text('social_description')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_email_name')->nullable();
            $table->string('email_description')->nullable();
            $table->boolean('push_notification')->default(0);
            $table->boolean('email_notification')->default(0);
            $table->boolean('email_verification')->default(0);
            $table->boolean('sms_notification')->default(0);
            $table->boolean('sms_verification')->default(0);
            $table->boolean('allowUser')->default(0);
            $table->string('joining_bonus')->nullable();
            $table->boolean('deposit_commission')->default(0);
            $table->boolean('login_commission')->default(0);
            $table->boolean('transfer')->default(0);
            $table->boolean('request')->default(0);
            $table->boolean('exchange')->default(0);
            $table->boolean('redeem')->default(0);
            $table->boolean('escrow')->default(0);
            $table->boolean('voucher')->default(0);
            $table->boolean('deposit')->default(0);
            $table->string('tawk_id')->nullable();
            $table->boolean('tawk_status')->default(0);
            $table->boolean('fb_messenger_status')->default(0);
            $table->string('fb_app_id')->nullable();
            $table->string('fb_page_id')->nullable();
            $table->boolean('reCaptcha_status_login')->default(0);
            $table->boolean('reCaptcha_status_registration')->default(0);
            $table->string('MEASUREMENT_ID')->nullable();
            $table->boolean('analytic_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basic_controls');
    }
}
