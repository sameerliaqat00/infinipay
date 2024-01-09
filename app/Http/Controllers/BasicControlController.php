<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\FirebaseNotify;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

class BasicControlController extends Controller
{
	public function currencyExchangeApiConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.currencyExchangeApiConfig', compact('basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'currency_layer_access_key' => 'nullable|string|min:1',
				'coin_market_cap_app_key' => 'nullable|string|min:1',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$basicControl->currency_layer_access_key = $purifiedData->currency_layer_access_key;
			$basicControl->currency_layer_auto_update = $purifiedData->currency_layer_auto_update;
			$basicControl->currency_layer_auto_update_at = $purifiedData->currency_layer_auto_update_at;
			$basicControl->coin_market_cap_app_key = $purifiedData->coin_market_cap_app_key;
			$basicControl->coin_market_cap_auto_update = $purifiedData->coin_market_cap_auto_update;
			$basicControl->coin_market_cap_auto_update_at = $purifiedData->coin_market_cap_auto_update_at;
			$basicControl->save();
			return back()->with('success', 'Configuration changes successfully');
		}
	}

	public function pusherConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.pusherConfig', compact('basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'pusher_app_id' => 'required|integer|not_in:0',
				'pusher_app_key' => 'required|string|min:1',
				'pusher_app_secret' => 'required|string|min:1',
				'pusher_app_cluster' => 'required|string|min:1',
				'push_notification' => 'nullable|integer|min:0|in:0,1',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$envPath = base_path('.env');
			$env = file($envPath);
			$env = $this->set('PUSHER_APP_ID', $purifiedData->pusher_app_id, $env);
			$env = $this->set('PUSHER_APP_KEY', $purifiedData->pusher_app_key, $env);
			$env = $this->set('PUSHER_APP_SECRET', $purifiedData->pusher_app_secret, $env);
			$env = $this->set('PUSHER_APP_CLUSTER', $purifiedData->pusher_app_cluster, $env);

			$fp = fopen($envPath, 'w');
			fwrite($fp, implode($env));
			fclose($fp);

			$basicControl->push_notification = $purifiedData->push_notification;
			$basicControl->save();

			return back()->with('success', 'Configuration Changes Successfully');
		}
	}

	public function firebaseConfig(Request $request)
	{
		$control = FirebaseNotify::firstOrNew();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.firebaseConfig', compact('control'));
		} elseif ($request->isMethod('post')) {
			$request->validate([
				'server_key' => 'required|string',
				'vapid_key' => 'required|string',
				'api_key' => 'required|string',
				'auth_domain' => 'required|string',
				'project_id' => 'required|string',
				'storage_bucket' => 'required|string',
				'messaging_sender_id' => 'required|string',
				'app_id' => 'required|string',
				'measurement_id' => 'required|string',
			]);
			$purifiedData = Purify::clean($request->all());
			$purifiedData = (object)$purifiedData;

			$control->server_key = $purifiedData->server_key;
			$control->vapid_key = $purifiedData->vapid_key;
			$control->api_key = $purifiedData->api_key;
			$control->auth_domain = $purifiedData->auth_domain;
			$control->project_id = $purifiedData->project_id;
			$control->storage_bucket = $purifiedData->storage_bucket;
			$control->messaging_sender_id = $purifiedData->messaging_sender_id;
			$control->app_id = $purifiedData->app_id;
			$control->measurement_id = $purifiedData->measurement_id;
			$control->user_foreground = $purifiedData->user_foreground;
			$control->user_background = $purifiedData->user_background;
			$control->admin_foreground = $purifiedData->admin_foreground;
			$control->admin_background = $purifiedData->admin_background;

			$this->writeGolobalFirebase($control);

			$control->save();
			return back()->with('success', 'Updated Successfully.');
		}
	}

	public function writeGolobalFirebase($control)
	{
		$apikey = '"' . $control->api_key . '"';
		$authDomain = '"' . $control->auth_domain . '"';
		$projectId = '"' . $control->project_id . '"';
		$storageBucket = '"' . $control->storage_bucket . '"';
		$messagingSenderId = '"' . $control->messaging_sender_id . '"';
		$appId = '"' . $control->app_id . '"';
		$measurementId = '"' . $control->measurement_id . '"';


		$myfile = fopen("firebase-messaging-sw.js", "w") or die("Unable to open file!");
		$txt = "
        self.onnotificationclick = (event) => {
            if(event.notification.data.FCM_MSG.data.click_action){
               event.notification.close();
               event.waitUntil(clients.matchAll({
                    type: 'window'
               }).then((clientList) => {
                  for (const client of clientList) {
                      if (client.url === '/' && 'focus' in client)
                          return client.focus();
                      }
                  if (clients.openWindow)
                      return clients.openWindow(event.notification.data.FCM_MSG.data.click_action);
                  }));
            }
        };
        importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
               importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

        const firebaseConfig = {
        apiKey: $apikey,
        authDomain: $authDomain,
        projectId: $projectId,
        storageBucket: $storageBucket,
        messagingSenderId: $messagingSenderId,
        appId: $appId,
        measurementId: $measurementId
        };

       const app = firebase.initializeApp(firebaseConfig);
       const messaging = firebase.messaging();

       messaging.setBackgroundMessageHandler(function (payload) {
       if (payload.notification.background && payload.notification.background == 1) {
          const title = payload.notification.title;
          const options = {
            body: payload.notification.body,
            icon: payload.notification.icon,
          };
          return self.registration.showNotification(
            title,
            options,
          );
       }
        });";
		fwrite($myfile, $txt);
		fclose($myfile);

		return 0;
	}

	public function emailConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.emailConfig', compact('basicControl'));
		} else {
			$purifiedData = Purify::clean($request->all());


			$validateFor = [
				'mail_host' => 'required|string|min:5',
				'mail_port' => 'required|integer|not_in:0',
				'mail_username' => 'required|string|min:5',
				'mail_password' => 'required|string|min:5',
				'mail_from' => 'required|string|email',
				'email_notification' => 'nullable|integer|min:0|in:0,1',
				'email_verification' => 'nullable|integer|min:0|in:0,1',
			];
			$validator = Validator::make($purifiedData, $validateFor);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}


			$purifiedData = (object)$purifiedData;
			$basicControl->email_notification = $purifiedData->email_notification;
			$basicControl->email_verification = $purifiedData->email_verification;
			$basicControl->save();

			$envPath = base_path('.env');
			$env = file($envPath);

			$env = $this->set('MAIL_MAILER', '"smtp"', $env);
			$env = $this->set('MAIL_HOST', '"' . $purifiedData->mail_host . '"', $env);
			$env = $this->set('MAIL_PORT', '"' . $purifiedData->mail_port . '"', $env);
			$env = $this->set('MAIL_USERNAME', '"' . $purifiedData->mail_username . '"', $env);
			$env = $this->set('MAIL_PASSWORD', '"' . $purifiedData->mail_password . '"', $env);
			$env = $this->set('MAIL_FROM_ADDRESS', '"' . $purifiedData->mail_from . '"', $env);
			$env = $this->set('MAIL_ENCRYPTION', '"' . $purifiedData->mail_encryption . '"', $env);

			$fp = fopen($envPath, 'w');
			fwrite($fp, implode($env));
			fclose($fp);


			$emailtemplates = EmailTemplate::get();
			foreach ($emailtemplates as $emailtemplate) {
				$emailtemplate->email_from = $purifiedData->mail_from;
				$emailtemplate->save();
			}

			$basicControl = basicControl();
			$basicControl->sender_email = $purifiedData->mail_from;
			$basicControl->save();




			return back()->with('success', 'Successfully Updated');
		}
	}

	private function set($key, $value, $env)
	{
		foreach ($env as $env_key => $env_value) {
			$entry = explode("=", $env_value, 2);
			if ($entry[0] == $key) {
				$env[$env_key] = $key . "=" . $value . "\n";
			} else {
				$env[$env_key] = $env_value;
			}
		}
		return $env;
	}

	public function index($settings = null)
	{
		$settings = $settings ?? 'settings';
		abort_if(!in_array($settings, array_keys(config('generalsettings'))), 404);
		$settingsDetails = config("generalsettings.{$settings}");
		return view('admin.control_panel.settings', compact('settings', 'settingsDetails'));
	}

	public function basic_control(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.control_panel.basic-control', compact('currencies', 'basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'site_title' => 'required|min:3',
				'base_currency' => 'required|integer|min:1|not_in:0',
				'time_zone' => 'required',
				'deposit_commission' => 'nullable|integer|min:0|in:0,1',
				'login_commission' => 'nullable|integer|min:0|in:0,1',
				'joining_bonus' => 'nullable|numeric|min:0',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			$purifiedData = (object)$purifiedData;

			$base_currency_code = Currency::find($purifiedData->base_currency)->code;
			$basicControl->site_title = $purifiedData->site_title;
			$basicControl->primaryColor = $purifiedData->primaryColor;
			$basicControl->secondaryColor = $purifiedData->secondaryColor;
			$basicControl->joining_bonus = $purifiedData->joining_bonus;
			$basicControl->time_zone = $purifiedData->time_zone;
			$basicControl->base_currency = $purifiedData->base_currency;
			$basicControl->base_currency_code = isset($base_currency_code) ? $base_currency_code : 'USD';
			$basicControl->deposit_commission = $purifiedData->deposit_commission;
			$basicControl->signup_bonus_status = $purifiedData->signup_bonus_status;
			$basicControl->error_log = $purifiedData->error_log;
			$basicControl->is_active_cron_notification = $purifiedData->is_active_cron_notification;
			$basicControl->force_ssl = $purifiedData->force_ssl;
			$basicControl->save();

			config(['basic.base_currency_code' => $base_currency_code]);
			config(['basic.base_currency' => $purifiedData->base_currency]);

			$fp = fopen(base_path() . '/config/basic.php', 'w');
			fwrite($fp, '<?php return ' . var_export(config('basic'), true) . ';');
			fclose($fp);

			$envPath = base_path('.env');
			$env = file($envPath);
			$env = $this->set('APP_DEBUG', ($basicControl->error_log == 1) ? 'true' : 'false', $env);
			$env = $this->set('APP_TIMEZONE', '"' . $purifiedData->time_zone . '"', $env);

			$fp = fopen($envPath, 'w');
			fwrite($fp, implode($env));
			fclose($fp);

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function serviceControl(Request $request)
	{
		$serviceControl = basicControl();

		if ($request->isMethod('get')) {
			return view('admin.control_panel.serviceControl', compact('serviceControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'transfer' => 'nullable|integer|min:0|in:0,1',
				'request' => 'nullable|integer|min:0|in:0,1',
				'exchange' => 'nullable|integer|min:0|in:0,1',
				'redeem' => 'nullable|integer|min:0|in:0,1',
				'escrow' => 'nullable|integer|min:0|in:0,1',
				'voucher' => 'nullable|integer|min:0|in:0,1',
				'deposit' => 'nullable|integer|min:0|in:0,1',
				'payout' => 'nullable|integer|min:0|in:0,1',
				'invoice' => 'nullable|integer|min:0|in:0,1',
				'store' => 'nullable|integer|min:0|in:0,1',
				'qr_payment' => 'nullable|integer|min:0|in:0,1',
				'virtual_card' => 'nullable|integer|min:0|in:0,1',
				'bill_payment' => 'nullable|integer|min:0|in:0,1',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$serviceControl->transfer = $purifiedData->transfer;
			$serviceControl->request = $purifiedData->request;
			$serviceControl->exchange = $purifiedData->exchange;
			$serviceControl->redeem = $purifiedData->redeem;
			$serviceControl->escrow = $purifiedData->escrow;
			$serviceControl->voucher = $purifiedData->voucher;
			$serviceControl->deposit = $purifiedData->deposit;
			$serviceControl->payout = $purifiedData->payout;
			$serviceControl->invoice = $purifiedData->invoice;
			$serviceControl->store = $purifiedData->store;
			$serviceControl->qr_payment = $purifiedData->qr_payment;
			$serviceControl->virtual_card = $purifiedData->virtual_card;
			$serviceControl->bill_payment = $purifiedData->bill_payment;
			$serviceControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function voucherSettings(Request $request)
	{
		$basicControl = basicControl();

		if ($request->isMethod('get')) {
			return view('admin.control_panel.voucherSettings', compact('basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'allowUser' => 'nullable|integer|min:0|in:0,1',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$basicControl->allowUser = $purifiedData->allowUser;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function invoiceSettings(Request $request)
	{
		$basicControl = basicControl();

		if ($request->isMethod('get')) {
			return view('admin.control_panel.invoiceSettings', compact('basicControl'));
		} elseif ($request->isMethod('post')) {

			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'invoiceCharge' => 'nullable|integer|min:0|in:0,1',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$basicControl->invoice_charge = $purifiedData->invoiceCharge;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function virtualCardSettings(Request $request)
	{
		$basicControl = basicControl();

		if ($request->isMethod('get')) {
			return view('admin.control_panel.virtualCardSettings', compact('basicControl'));
		} elseif ($request->isMethod('post')) {

			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'v_card_multiple' => 'nullable|integer|min:0|in:0,1',
				'v_card_charge' => 'nullable|integer|min:0',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$basicControl->v_card_multiple = $purifiedData->v_card_multiple;
			$basicControl->v_card_charge = $purifiedData->v_card_charge;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function pluginConfig()
	{
		return view('admin.control_panel.pluginConfig');
	}

	public function tawkConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.control_panel.tawkControl', compact('currencies', 'basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'tawk_id' => 'required|min:3',
				'tawk_status' => 'nullable|integer|min:0|in:0,1',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$basicControl->tawk_id = $purifiedData->tawk_id;
			$basicControl->tawk_status = $purifiedData->tawk_status;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function fbMessengerConfig(Request $request)
	{
		$basicControl = basicControl();

		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.control_panel.fbMessengerControl', compact('currencies', 'basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'fb_messenger_status' => 'nullable|integer|min:0|in:0,1',
				'fb_app_id' => 'required|min:3',
				'fb_page_id' => 'required|min:3',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$basicControl->fb_app_id = $purifiedData->fb_app_id;
			$basicControl->fb_page_id = $purifiedData->fb_page_id;
			$basicControl->fb_messenger_status = $purifiedData->fb_messenger_status;

			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function googleRecaptchaConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.control_panel.googleReCaptchaControl', compact('currencies', 'basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'reCaptcha_status_login' => 'nullable|integer|min:0|in:0,1',
				'reCaptcha_status_registration' => 'nullable|integer|min:0|in:0,1',
				'NOCAPTCHA_SECRET' => 'required|min:3',
				'NOCAPTCHA_SITEKEY' => 'required|min:3',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$basicControl->reCaptcha_status_login = $purifiedData->reCaptcha_status_login;
			$basicControl->reCaptcha_status_registration = $purifiedData->reCaptcha_status_registration;
			$basicControl->save();


			$envPath = base_path('.env');
			$env = file($envPath);
			$env = $this->set('NOCAPTCHA_SECRET', $purifiedData->NOCAPTCHA_SECRET, $env);
			$env = $this->set('NOCAPTCHA_SITEKEY', $purifiedData->NOCAPTCHA_SITEKEY, $env);
			$fp = fopen($envPath, 'w');
			fwrite($fp, implode($env));
			fclose($fp);

			Artisan::call('config:clear');
			Artisan::call('cache:clear');

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function googleAnalyticsConfig(Request $request)
	{
		$basicControl = basicControl();
		if ($request->isMethod('get')) {
			$currencies = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
			return view('admin.control_panel.analyticControl', compact('currencies', 'basicControl'));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'MEASUREMENT_ID' => 'required|min:3',
				'analytic_status' => 'nullable|integer|min:0|in:0,1',
			]);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;

			$basicControl->MEASUREMENT_ID = $purifiedData->MEASUREMENT_ID;
			$basicControl->analytic_status = $purifiedData->analytic_status;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}

	public function sandBoxApi(Request $request)
	{
		$basicControl = basicControl();
		$gateways = Gateway::where('status', 1)->get();
		if ($request->isMethod('get')) {
			return view('admin.control_panel.apiSandbox', compact('basicControl', 'gateways'));
		} else {
			$purifiedData = Purify::clean($request->all());

			$validator = Validator::make($purifiedData, [
				'gateways' => 'required',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
			$purifiedData = (object)$purifiedData;
			$basicControl->sandbox_gateways = $purifiedData->gateways;
			$basicControl->save();

			return back()->with('success', 'Successfully Updated');
		}
	}
}
