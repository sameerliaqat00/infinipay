<?php
return [
	'banner' => [
		'field_name' => [
			'title' => 'text',
			'sub_title' => 'text',
			'button_name' => 'text',
            'button_link' => 'url',
			'image' => 'file',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'sub_title.*' => 'required|min:2|max:100',
			'button_name.*' => 'required|max:100',
            'button_link.*' => 'required|url',
			'image.*' => 'max:3072|image|mimes:jpg,jpeg,png',
		],
		'size' => [
            'image' => '700x1110'
        ]
	],

	'feature' => [
		'field_name' => [
			'title' => 'text',
			'image' => 'file',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
			'short_description.*' => 'required|min:2|max:2000',
		],
		'size' => [
            'image' => '95x105'
        ]
	],

	'about-us' => [
		'field_name' => [
			'title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'services' => [
		'field_name' => [
			'title' => 'text',
			'image' => 'file',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'get-started' => [
		'field_name' => [
			'title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'faq' => [
		'field_name' => [
			'title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'clients-feedback' => [
		'field_name' => [
			'title' => 'text',
			'designation' => 'text',
			'image' => 'file',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'designation.*' => 'required|min:2|max:100',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
			'short_description.*' => 'required|min:2|max:2000',
		],
		'size' => [
            'image' => '75x75'
        ]
	],

	'blog' => [
		'field_name' => [
			'title' => 'text',
			'image' => 'file',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'contact' => [
		'field_name' => [
			'title' => 'text',
			'short_description' => 'textarea',
		],
		'validation' => [
			'title.*' => 'required|min:2|max:100',
			'short_description.*' => 'required|min:2|max:2000',
		],
	],

	'social-links' => [
		'language' => 0,
		'field_name' => [
			'social_icon' => 'icon',
			'social_link' => 'url',
		],
		'validation' => [
			'social_icon.*' => 'required',
			'social_link.*' => 'required|url',
		],
	],
	'extra-pages' => [
        'field_name' => [
            'title' => 'text',
            'description' => 'textarea'
        ],
        'validation' => [
            'title.*' => 'required|max:100',
            'description.*' => 'required|max:100000'
        ]
    ],

	'message' => [
		'required' => 'This field is required.',
		'min' => 'This field must be at least :min characters.',
		'max' => 'This field may not be greater than :max characters.',
		'image' => 'This field must be image.',
		'mimes' => 'This image must be a file of type: jpg, jpeg, png.',
	],

	'content_media' => [
		'image' => 'file',
		'thumbnail' => 'file',
		'youtube_link' => 'url',
		'social_icon' => 'icon',
		'social_link' => 'url',
		'button_link' => 'url'
	]
];
