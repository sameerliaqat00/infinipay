@extends('admin.layouts.master')
@section('page_title', __('New Plan List'))

@section('content')
	<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Overdue Installments</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Dashboard</a>
                </div>
                <div class="breadcrumb-item active">
                    Overdue Installments
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="#" class="btn btn-primary">Export Excel</a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive-2">
                                <table class="table table-striped font-14">
                                    <tbody><tr>
                                        <th>User</th>
                                        <th class="text-left">Installment Plan</th>
                                        <th class="text-center">Product</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Due Date</th>
                                        <th>Actions</th>
                                    </tr>

                                                                            <tr>
                                            <td class="text-left">
                                                <div class="d-flex align-items-center">
                                                    <figure class="avatar mr-2">
                                                        <img src="https://lms.rocket-soft.org/store/995/avatar/6179b58ee64c7.png" alt="Cameron Schofield">
                                                    </figure>
                                                    <div class="media-body ml-1">
                                                        <div class="mt-0 mb-1 font-weight-bold">Cameron Schofield</div>

                                                                                                                    <div class="text-primary text-small font-600-bold">+12025550169</div>
                                                        
                                                                                                                    <div class="text-primary text-small font-600-bold">student@demo.com</div>
                                                                                                            </div>
                                                </div>
                                            </td>

                                            <td class="text-left">
                                                <div class="">
                                                    <span class="d-block font-16 font-weight-500">Christmas installment plan</span>
                                                    <span class="d-block font-12 mt-1">Courses</span>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                                                                    <a href="#" target="_blank" class="font-14">#2022-Installment and Secure Host</a>
                                                    <span class="d-block font-12">Courses</span>
                                                                                            </td>

                                            <td class="text-center">
                                                                                                    20% ($20)
                                                                                            </td>

                                            <td class="text-center">1 Sep 2023 (2 months ago)</td>

                                            <td>
                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu text-left webinars-lists-dropdown">

                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm">
                                                                <i class="fa fa-eye"></i>
                                                                <span class="ml-2">Show Details</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                                <i class="fa fa-user-shield"></i>
                                                                <span class="ml-2">Login</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                                <i class="fa fa-edit"></i>
                                                                <span class="ml-2">Edit</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1">
                                                                <i class="fa fa-comment"></i>
                                                                <span class="ml-2">Send Message</span>
                                                            </a>
                                                        
                                                                                                                    <button class="btn-transparent text-primary d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1 trigger--fire-modal-1" data-confirm="Are you sure? | Do you want to continue?" data-confirm-href="#" data-confirm-text-yes="Yes" data-confirm-text-cancel="Cancel">
            <i class="fa fa-times"></i><span class="ml-2">Cancel</span>
    </button>


                                                            <button class="btn-transparent text-primary d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1 trigger--fire-modal-2" data-confirm="Are you sure? | Do you want to continue?" data-confirm-href="#" data-confirm-text-yes="Yes" data-confirm-text-cancel="Cancel">
            <i class="fa fa-times-circle"></i><span class="ml-2">Refund</span>
    </button>
                                                                                                            </div>
                                                </div>
                                            </td>
                                        </tr>
                                                                            <tr>
                                            <td class="text-left">
                                                <div class="d-flex align-items-center">
                                                    <figure class="avatar mr-2">
                                                        <img src="https://lms.rocket-soft.org/store/995/avatar/6179b58ee64c7.png" alt="Cameron Schofield">
                                                    </figure>
                                                    <div class="media-body ml-1">
                                                        <div class="mt-0 mb-1 font-weight-bold">Cameron Schofield</div>

                                                                                                                    <div class="text-primary text-small font-600-bold">+12025550169</div>
                                                        
                                                                                                                    <div class="text-primary text-small font-600-bold">student@demo.com</div>
                                                                                                            </div>
                                                </div>
                                            </td>

                                            <td class="text-left">
                                                <div class="">
                                                    <span class="d-block font-16 font-weight-500">Christmas installment plan</span>
                                                    <span class="d-block font-12 mt-1">Courses</span>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                                                                    <a href="#" target="_blank" class="font-14">#2022-Installment and Secure Host</a>
                                                    <span class="d-block font-12">Courses</span>
                                                                                            </td>

                                            <td class="text-center">
                                                                                                    30% ($30)
                                                                                            </td>

                                            <td class="text-center">30 Nov 2023 (9 hours ago)</td>

                                            <td>
                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu text-left webinars-lists-dropdown">

                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm">
                                                                <i class="fa fa-eye"></i>
                                                                <span class="ml-2">Show Details</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                                <i class="fa fa-user-shield"></i>
                                                                <span class="ml-2">Login</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1">
                                                                <i class="fa fa-edit"></i>
                                                                <span class="ml-2">Edit</span>
                                                            </a>
                                                        
                                                                                                                    <a href="#" target="_blank" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1">
                                                                <i class="fa fa-comment"></i>
                                                                <span class="ml-2">Send Message</span>
                                                            </a>
                                                        
                                                                                                                    <button class="btn-transparent text-primary d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1 trigger--fire-modal-3" data-confirm="Are you sure? | Do you want to continue?" data-confirm-href="#" data-confirm-text-yes="Yes" data-confirm-text-cancel="Cancel">
            <i class="fa fa-times"></i><span class="ml-2">Cancel</span>
    </button>


                                                            <button class="btn-transparent text-primary d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm mt-1 trigger--fire-modal-4" data-confirm="Are you sure? | Do you want to continue?" data-confirm-href="#" data-confirm-text-yes="Yes" data-confirm-text-cancel="Cancel">
            <i class="fa fa-times-circle"></i><span class="ml-2">Refund</span>
    </button>
                                                                                                            </div>
                                                </div>
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
