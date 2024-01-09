<?php
return [

	'feature' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
			'image' => 'file',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
		],
		'size' => [
			'image' => '339x642'
		]
	],

	'about-us' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'services' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'get-started' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
			'image' => 'file',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
		],
		'size' => [
			'image' => '601x641'
		]
	],

	'faq' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'clients-feedback' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
		],
	],

	'blog' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'customers-content' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'textarea',
			'total_users' => 'text',
			'total_currency' => 'text',
			'total_wallet' => 'text',
			'gateways' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
			'total_users.*' => 'required|numeric|not_in:0',
			'total_currency.*' => 'required|numeric|not_in:0',
			'total_wallet.*' => 'required|numeric|not_in:0',
			'gateways.*' => 'required|numeric|not_in:0',
		],
	],

	'contact' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:500',
		],
	],

	'login' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'text',
			'button_name' => 'text',
			'button_link' => 'url',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:150',
			'short_description.*' => 'required|min:2|max:600',
			'button_name.*' => 'required|max:100',
			'button_link.*' => 'required|url',
		],
	],

	'register' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'text',
			'button_name' => 'text',
			'button_link' => 'url',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:150',
			'short_description.*' => 'required|min:2|max:600',
			'button_name.*' => 'required|max:100',
			'button_link.*' => 'required|url',
		],
	],

	'forget-password' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'text',
			'button_name' => 'text',
			'button_link' => 'url',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:150',
			'short_description.*' => 'required|min:2|max:600',
			'button_name.*' => 'required|max:100',
			'button_link.*' => 'required|url',
		],
	],

	'reset-password' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'short_description' => 'text',
			'button_name' => 'text',
			'button_link' => 'url',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:150',
			'short_description.*' => 'required|min:2|max:600',
			'button_name.*' => 'required|max:100',
			'button_link.*' => 'required|url',
		],
	],

	'email-verification' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
		],
	],

	'sms-verification' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
		],
	],

	'2fa-verification' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
		],
	],

	'add-fund' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'send-money' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'request-money' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'exchange-money' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'generate-redeem-code' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'escrow' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'voucher-payment' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'invoice-payment' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'virtual-card' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'payout-request' => [
		'field_name' => [
			'short_description' => 'textarea'
		],
		'validation' => [
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'cookie-consent' => [
		'field_name' => [
			'title' => 'text',
			'popup_short_description' => 'textarea',
			'description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:180',
			'popup_short_description.*' => 'required|min:2|max:300',
			'description.*' => 'required|min:2|max:100000',
		],
	],

	'message' => [
		'required' => 'This field is required.',
		'min' => 'This field must be at least :min characters.',
		'max' => 'This field may not be greater than :max characters.',
		'image' => 'This field must be image.',
		'mimes' => 'This image must be a file of type: jpg, jpeg, png.',
	],

	'template_media' => [
		'image' => 'file',
		'button_link' => 'url'
	]
];
