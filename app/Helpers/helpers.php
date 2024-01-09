<?php

use App\Models\BasicControl;
use App\Models\Fund;
use App\Models\Role;
use App\Models\Voucher;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

function hex2rgba($color, $opacity = false)
{
	$default = 'rgb(0,0,0)';
	//Return default if no color provided
	if (empty($color))
		return $default;
	//Sanitize $color if "#" is provided
	if ($color[0] == '#') {
		$color = substr($color, 1);
	}
	//Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
		$hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif (strlen($color) == 3) {
		$hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return $default;
	}
	//Convert hexadec to rgb
	$rgb = array_map('hexdec', $hex);
	//Check if opacity is set(rgba or rgb)
	if ($opacity) {
		if (abs($opacity) > 1)
			$opacity = 1.0;
		$output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode(",", $rgb) . ')';
	}
	//Return rgb(a) color string
	return $output;
}

function collapsedMenu($routeNames = [], $segment = null)
{
	$lastSegment = last(request()->segments());
	$currentName = request()->route()->getName();
	if (isset($segment)) {
		return (in_array($currentName, $routeNames) && $lastSegment == $segment) ? 'active' : '';
	}
	return in_array($currentName, $routeNames) ? '' : 'collapsed';
}

function activeMenu($routeNames = [], $segment = null)
{
	$lastSegment = last(request()->segments());
	$currentName = request()->route()->getName();
	if (isset($segment)) {
		return (in_array($currentName, $routeNames) && $lastSegment == $segment) ? 'active' : '';
	}
	return in_array($currentName, $routeNames) ? 'active' : '';
}

if (!function_exists('isMenuActive')) {
	function isMenuActive($routes, $type = 0)
	{
		$class = [
			'0' => 'active',
			'1' => 'style=display:block',
			'2' => true
		];

		if (is_array($routes)) {
			foreach ($routes as $key => $route) {
				if (request()->routeIs($route)) {
					return $class[$type];
				}
			}
		} elseif (request()->routeIs($routes)) {
			return $class[$type];
		}

		if ($type == 1) {
			return 'style=display:none';
		} else {
			return false;
		}
	}
}

function menuFormater($value)
{
	return ucwords(str_replace(['-', '_'], ' ', $value));
}

function showMenu($routeNames = [])
{
	$currentName = request()->route()->getName();
	return in_array($currentName, $routeNames) ? 'show' : '';
}

function basicControl()
{
	$general = Cache::get('GeneralSetting');
	if (!$general) {
		$general = BasicControl::with('currency')->firstOrCreate(['id' => 1]);
		Cache::put('GeneralSetting', $general);
	}
	return $general;
}

if (!function_exists('getTitle')) {
	function getTitle($title)
	{
		return ucwords(preg_replace('/[^A-Za-z0-9]/', ' ', $title));
	}
}

if (!function_exists('getRoute')) {
	function getRoute($route, $params = null)
	{
		return isset($params) ? route($route, $params) : route($route);
	}
}
function strRandom($length = 12)
{
	$characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function code($length = 6)
{
	if ($length == 0) return 0;
	$min = pow(10, $length - 1);
	$max = 0;
	while ($length > 0 && $length--) {
		$max = ($max * 10) + 9;
	}
	return random_int($min, $max);
}

function getFile($image, $clean = '')
{
	return file_exists($image) && is_file($image) ? asset($image) . $clean : asset(config('location.default'));
}

// $action 0 = deduct, 1 = Add //
function updateWallet($user_id, $currency_id, $amount, $action = 0)
{
	$wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id]);
	$balance = 0;

	if ($action == 1) { //add money
		$balance = $wallet->balance + $amount;
		$wallet->balance = $balance;
	} elseif ($action == 0) { //deduct money
		$balance = $wallet->balance - $amount;
		$wallet->balance = $balance;
	}

	$wallet->save();
	return $balance;
}

function camelToWord($str)
{
	$arr = preg_split('/(?=[A-Z])/', $str);
	return trim(join(' ', $arr));
}


function title2snake($string)
{
	return Str::title(str_replace(' ', '_', $string));
}

function snake2Title($string)
{
	return Str::title(str_replace('_', ' ', $string));
}

function kebab2Title($string)
{
	return Str::title(str_replace('-', ' ', $string));
}

function recursive_array_replace($find, $replace, $array)
{
	if (!is_array($array)) {
		return str_replace($find, $replace, $array);
	}
	$newArray = [];
	foreach ($array as $key => $value) {
		$newArray[$key] = recursive_array_replace($find, $replace, $value);
	}
	return $newArray;
}

function getAmount($amount, $length = 0)
{
	if ($amount == 0) {
		return 0;
	}

	if ($length == 0) {
		preg_match("#^([\+\-]|)([0-9]*)(\.([0-9]*?)|)(0*)$#", trim($amount), $o);
		return $o[1] . sprintf('%d', $o[2]) . ($o[3] != '.' ? $o[3] : '');
	}

	return round($amount, $length);
}

function getMethodCurrency($gateway)
{
	foreach ($gateway->currencies as $key => $currency) {
		if (property_exists($currency, $gateway->currency)) {
			if ($key == 0) {
				return $gateway->currency;
			} else {
				return 'USD';
			}
		}
//		return 'N/A';
	}
}

function twoStepPrevious($deposit)
{
	if ($deposit->depositable_type == Fund::class) {
		return route('fund.initialize');
	} elseif ($deposit->depositable_type == Voucher::class) {
		return route('voucher.public.payment', $deposit->depositable->utr);
	}
}

function wordTruncate($string, $offset = 0, $length = null)
{
	$words = explode(" ", $string);
	isset($length) ? array_splice($words, $offset, $length) : array_splice($words, $offset);
	return implode(" ", $words);
}

function linkToEmbed($string)
{
	$words = explode("/", $string);
	if (strpos($string, 'embed') == false) {
		array_splice($words, -1, 0, 'embed');
	}
	$words = str_ireplace('watch?v=', '', implode("/", $words));
	return $words;
}

function getIpInfo()
{
//	$ip = '210.1.246.42';
	$ip = null;
	$deep_detect = TRUE;

	if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
		$ip = $_SERVER["REMOTE_ADDR"];
		if ($deep_detect) {
			if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
	}
	$xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);

	$country = @$xml->geoplugin_countryName;
	$city = @$xml->geoplugin_city;
	$area = @$xml->geoplugin_areaCode;
	$code = @$xml->geoplugin_countryCode;
	$long = @$xml->geoplugin_longitude;
	$lat = @$xml->geoplugin_latitude;


	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$os_platform = "Unknown OS Platform";
	$os_array = array(
		'/windows nt 10/i' => 'Windows 10',
		'/windows nt 6.3/i' => 'Windows 8.1',
		'/windows nt 6.2/i' => 'Windows 8',
		'/windows nt 6.1/i' => 'Windows 7',
		'/windows nt 6.0/i' => 'Windows Vista',
		'/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
		'/windows nt 5.1/i' => 'Windows XP',
		'/windows xp/i' => 'Windows XP',
		'/windows nt 5.0/i' => 'Windows 2000',
		'/windows me/i' => 'Windows ME',
		'/win98/i' => 'Windows 98',
		'/win95/i' => 'Windows 95',
		'/win16/i' => 'Windows 3.11',
		'/macintosh|mac os x/i' => 'Mac OS X',
		'/mac_powerpc/i' => 'Mac OS 9',
		'/linux/i' => 'Linux',
		'/ubuntu/i' => 'Ubuntu',
		'/iphone/i' => 'iPhone',
		'/ipod/i' => 'iPod',
		'/ipad/i' => 'iPad',
		'/android/i' => 'Android',
		'/blackberry/i' => 'BlackBerry',
		'/webos/i' => 'Mobile'
	);
	foreach ($os_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$os_platform = $value;
		}
	}
	$browser = "Unknown Browser";
	$browser_array = array(
		'/msie/i' => 'Internet Explorer',
		'/firefox/i' => 'Firefox',
		'/safari/i' => 'Safari',
		'/chrome/i' => 'Chrome',
		'/edge/i' => 'Edge',
		'/opera/i' => 'Opera',
		'/netscape/i' => 'Netscape',
		'/maxthon/i' => 'Maxthon',
		'/konqueror/i' => 'Konqueror',
		'/mobile/i' => 'Handheld Browser'
	);
	foreach ($browser_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$browser = $value;
		}
	}

	$data['country'] = $country;
	$data['city'] = $city;
	$data['area'] = $area;
	$data['code'] = $code;
	$data['long'] = $long;
	$data['lat'] = $lat;
	$data['os_platform'] = $os_platform;
	$data['browser'] = $browser;
	$data['ip'] = request()->ip();
	$data['time'] = date('d-m-Y h:i:s A');

	return $data;
}


function checkTo($currencies, $selectedCurrency = 'USD')
{
	foreach ($currencies as $key => $currency) {
		if (property_exists($currency, strtoupper($selectedCurrency))) {
			return $key;
		}
	}
}

function slug($title)
{
	return Str::slug($title);
}

function diffForHumans($date)
{
	$lang = session()->get('lang');
	\Carbon\Carbon::setlocale($lang);
	return \Carbon\Carbon::parse($date)->diffForHumans();
}

function loopIndex($object)
{
	return ($object->currentPage() - 1) * $object->perPage() + 1;
}

function dateTime($date, $format = 'd/m/Y H:i A')
{
	return date($format, strtotime($date));
}

function getPercent($total, $current)
{
	if ($current > 0 && $total > 0) {
		$percent = (($current * 100) / $total) ?: 0;
	} else {
		$percent = 0;
	}
	return round($percent, 0);
}

function resourcePaginate($data, $callback)
{
	return $data->setCollection($data->getCollection()->map($callback));
}

function clean($string)
{
	$string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
	return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function getLevelUser($id)
{
	$ussss = new \App\Models\User();
	return $ussss->referralUsers([$id]);
}

function currencyCode($storeLink)
{
	$store = \App\Models\Store::with('user.storeCurrency')->where('link', $storeLink)->first();

	if ($store) {
		$currencyCode = optional($store->user->storeCurrency)->code ?? '@';
		return $currencyCode;
	} else {
		return '@';
	}
}

function currencySymbol($storeLink)
{
	$store = \App\Models\Store::with('user.storeCurrency')->where('link', $storeLink)->first();

	if ($store) {
		$currencySymbol = optional($store->user->storeCurrency)->symbol ?? '@';
		return $currencySymbol;
	} else {
		return '@';
	}
}

function shopImage($storeLink)
{
	$store = \App\Models\Store::where('link', $storeLink)->first();
	$shopImage = getFile(config('location.store.path') . $store->image);
	return $shopImage;
}

function isStageDone($position, $stage)
{
	if ($stage == null) {
		if ($position == 0) {
			return 'completed';
		}
	}
	if ($position <= $stage && $stage < 5) {
		return 'completed';
	}
	return 0;
}

function cancelFrom($position, $fromStage)
{
	if ($fromStage != null) {
		if ($position > $fromStage) {
			return 0;
		}
	}
	return 'completed';
}

function dateFormat($expiry_month, $expiry_year)
{
	return Carbon::createFromFormat('Y-m', "{$expiry_year}-{$expiry_month}")->endOfMonth()->format('d/m/y');
}

function dateflatterwaveFormat($expiry_date)
{
	return Carbon::createFromFormat('Y-m', $expiry_date)->endOfMonth()->format('d/m/y');
}

function cardCurrencyCheck($orderId = null)
{
	$data = array();
	$order = \App\Models\VirtualCardOrder::with(['cardMethod'])->find($orderId);
	if ($order) {
		$currencyCode = $order->currency;
		$data['currencyCode'] = $order->currency;
		$fundCurrencies = optional($order->cardMethod)->add_fund_parameter;
		foreach ($fundCurrencies as $key => $fundCurrency) {
			if ($currencyCode == $key) {
				foreach ($fundCurrency as $key => $item) {
					$data[$key] = $item->field_value;
				}
				$data['status'] = 'success';
				return $data;
			}
		}
	}
	$data['status'] = 'fail';
	return $data;
}

function convertRate($currencyCode, $payout)
{
	$convertRate = 0;
	$rate = optional($payout->payoutMethod)->convert_rate;

	if ($rate) {
		$convertRate = $rate->$currencyCode;
	}

	return (float)$convertRate;
}

function checkPermission($role)
{
	$loginUser = auth()->user();

	if ($loginUser->status != 1) {
		return false;
	}
	if ($loginUser->role_id == null) {
		return true;
	}

	$checkAllow = Role::where('status', 1)->find($loginUser->role_id);
	if (!$checkAllow) {
		return false;
	}

	if (in_array($role, $checkAllow->permission)) {
		return true;
	} else {
		return false;
	}
}

function checkPermissionByKey($key)
{
	$loginUser = auth()->user();

	if ($loginUser->status != 1) {
		return false;
	}
	if ($loginUser->role_id == null) {
		return true;
	}

	foreach (config('permissionList') as $key1 => $value) {
		if ($key1 == $key) {
			$roleId = $value;
		}
	}

	if (empty($roleId)) {
		return true;
	}

	$checkAllow = Role::where('status', 1)->find($loginUser->role_id);
	if (!$checkAllow) {
		return false;
	}

	if (in_array($roleId, $checkAllow->permission)) {
		return true;
	} else {
		return false;
	}
}

if (!function_exists('getProjectDirectory')) {
	function getProjectDirectory()
	{
		return str_replace((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]", "", url("/"));
	}
}
