<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
	use HasFactory, Translatable;

	protected $casts = [
		'description' => 'object'
	];

	public function templateMedia()
	{
		return TemplateMedia::where('section_name', $this->section_name)->first();
	}


}
