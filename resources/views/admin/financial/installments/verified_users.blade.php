@extends('admin.layouts.master')
@section('page_title', __('New Plan List'))

@section('content')
	<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Verified Users</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin">Dashboard</a>
                </div>
                <div class="breadcrumb-item active">
                    Verified Users
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="/admin/financial/installments/verified_users/export" class="btn btn-primary">Export Excel</a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive-2">
                                <table class="table table-striped font-14">
                                    <tbody><tr>
                                        <th>User</th>
                                        <th class="">Registration Date</th>
                                        <th class="">Total Purchase</th>
                                        <th class="">Total Installments</th>
                                        <th class="text-center">Installments Count</th>
                                        <th class="text-center">Overdue Installments</th>
                                        <th>Actions</th>
                                    </tr>

                                    
                                </tbody></table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('extra_scripts')

@endpush
