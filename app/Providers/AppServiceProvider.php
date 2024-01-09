<?php

namespace App\Providers;

use App\Models\ContentDetails;
use App\Models\Template;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Paginator::useBootstrap();

		try {
			DB::connection()->getPdo();

			$data['basic'] = basicControl();
			View::share($data);

			if ($data['basic']->force_ssl == 1) {
				if ($this->app->environment('production') || $this->app->environment('local')) {
					\URL::forceScheme('https');
				}
			}

			view()->composer([
					'frontend.layouts.master',
				]
				, function ($view) {
					$contentSection = ['extra-pages', 'social'];
					$view->with('contentDetails', ContentDetails::select('id', 'content_id', 'description')
						->whereHas('content', function ($query) use ($contentSection) {
							return $query->whereIn('name', $contentSection);
						})
						->with(['content:id,name',
							'content.contentMedia' => function ($q) {
								$q->select(['content_id', 'description']);
							}])
						->get()->groupBy('content.name'));
				});
			view()->composer(['frontend.layouts.footer'], function ($view) {
				$view->with('cookie', Template::where('section_name', 'cookie-consent')->first());
			});
		} catch (\Exception $e) {
			//die("Could not connect to the database.  Please check your configuration according to documentation");
		}
	}
}
