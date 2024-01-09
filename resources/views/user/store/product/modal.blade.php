<div id="productImageDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-danger"
					id="primary-header-modalLabel">@lang('Image Delete Confirmation')</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to delete this image?')</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteProductImageForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Yes')</button>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="productDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-danger"
					id="primary-header-modalLabel">@lang('Product Delete Confirmation')</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to delete this Product?')</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteProductForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Yes')</button>
				</form>
			</div>
		</div>
	</div>
</div>
