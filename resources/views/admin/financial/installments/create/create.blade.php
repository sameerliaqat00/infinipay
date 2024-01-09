@extends('admin.layouts.master')
@section('page_title', __('New Plan List'))

@section('content')
	<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Installment Plans</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin">Dashboard</a>
                </div>
                <div class="breadcrumb-item active">
                    Installment Plans
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tbody><tr>
                                        <th>Title</th>
                                        <th class="text-center">Sales</th>
                                        <th class="text-center">Upfront</th>
                                        <th class="text-center">Number of Installments</th>
                                        <th class="text-center">Amount of Installments</th>
                                        <th class="text-center">Capacity</th>
                                        <th class="text-center">Created Date</th>
                                        <th class="text-center">End Date</th>
                                        <th class="text-center">Status</th>
                                        <th>Actions</th>
                                    </tr>

                                                                            <tr>
                                            <td>
                                                <div class="">
                                                    <span class="d-block font-16 font-weight-500">Christmas installment plan</span>
                                                    <span class="d-block font-12 mt-1">Courses</span>
                                                </div>
                                            </td>

                                            <td class="text-center"></td>

                                            <td class="text-center">
                                                30%
                                            </td>

                                            <td class="text-center">3</td>

                                            <td class="text-center">
                                                
                                                <span class=""></span>

                                                                                                    <span>80%</span>
                                                                                            </td>

                                            <td class="text-center">10</td>

                                            <td class="text-center">2023 Mar 15 | 14:14</td>

                                            <td class="text-center">-</td>

                                            <td class="text-center">
                                                                                                    <span class="text-success">Active</span>
                                                                                            </td>

                                            <td>
                                                                                                    <a href="#" class="btn-sm btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                
                                                                                                    <button class="btn-transparent text-primary trigger--fire-modal-1" data-confirm="Are you sure? | Do you want to continue?" data-confirm-href="/admin/financial/installments/1/delete" data-confirm-text-yes="Yes" data-confirm-text-cancel="Cancel" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
            <i class="fa fa-times" aria-hidden="true"></i>
    </button>
                                                                                            </td>
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
