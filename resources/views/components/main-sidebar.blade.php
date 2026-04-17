@php
$currentUrl = \Illuminate\Support\Facades\Request::segment(2);
@endphp
 

 <div class="app-sidebar sidebar-shadow bg-mean-fruit sidebar-text-dark" style="overflow: auto"> 
                  <div class="app-header__logo">
                   @if(!auth()->user()->isRole('Super Admin'))
            @if(auth()->user()->isRole('Admin'))
                @if(\Storage::disk('public')->has(auth()->user()->company_logo))
                    <img src="{{ asset('storage/'.auth()->user()->company_logo) }}" alt="" class="brand-image img-circle elevation-3"
                    style="opacity: .8;height:40px;border-radius: 50%">
                @else
                    <img src="{{ asset('images/no-img-100x92.jpg') }}" alt="" class="brand-image img-circle elevation-3"
                    style="opacity: .8;height:40px;border-radius: 50%">
                @endif
            @else
                @if(\Storage::disk('public')->has(auth()->user()->company->company_logo))
                    <img src="{{ asset('storage/'.auth()->user()->company->company_logo) }}" alt="" class="brand-image img-circle elevation-3"
                    style="opacity: .8;height:40px;border-radius: 50%">
                @else
                    <img src="{{ asset('images/no-img-100x92.jpg') }}" alt="" class="brand-image img-circle elevation-3"
                    style="opacity: .8;height:40px;border-radius: 50%">
                @endif
            @endif
        @else
            @if(\Storage::disk('public')->has('settings/'.config('get.MAIN_LOGO')))
                <img src="{{ asset('storage/settings/' . config('get.MAIN_LOGO')) }}" alt="" class="brand-image img-circle elevation-3"
                style="opacity: .8;height:40px;border-radius: 50%">
            @else
                <img src="{{ asset('images/no-img-100x92.jpg') }}" alt="" class="brand-image img-circle elevation-3"
                style="opacity: .8;height:40px;border-radius: 50%">
            @endif
        @endif
                        <div class="header__pane ml-auto">
                            <div>
                                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                                    <span class="hamburger-box" style="background-color:aliceblue !important">
                                        <span class="hamburger-inner"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                   
                    <div class="app-header__menu">
                        <span>
                            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                                <span class="btn-icon-wrapper">
                                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                                </span>
                            </button>
                        </span>
                    </div>
                    <div class="scrollbar-sidebar">
                        <div class="app-sidebar__inner">
                            <ul class="vertical-nav-menu">
                                <li class="app-sidebar__heading">Menu</li>
                                <li class="mm-{{(Request::segment(1)=='dashboard')?'active':''}}" @php /* style="background: rgba(0, 0, 0, 0.15)" */ @endphp >
                                    <a href="{{route('dashboard')}}">
                                        <i class="metismenu-icon pe-7s-rocket"></i>
                                        Dashboard
                                        @php /* <i style="color:red" class="metismenu-state-icon pe-7s-target"></i>*/ @endphp
                                    </a>
                                      <!--<ul>
                                        <li>
                                             <a href="{{route('carbondashboard')}}">
                                        <i class="metismenu-icon pe-7s-rocket"></i>
                                        Carbon Dashboard
                                        @php /* <i style="color:red" class="metismenu-state-icon pe-7s-target"></i>*/ @endphp
                                    </a>
                                            </li>
                                            </ul>-->
                                    
                                </li>
                                 @if ((auth()->user()->can('access', 'workflow visible')))
								 <li class="mm-{{ (request()->is('workflow*')) ? 'active' : '' }}">
                                         <a href="{{ route('workflow') }}">
                                         <i class="metismenu-icon pe-7s-note"></i>
                                         Workflow
                                        </a>
                                  </li>
                                   @endif
                                @php
                                    $canViewSuppliers = auth()->user()->can('access', 'suppliers visible');
                                    $canViewQuotations = auth()->user()->can('access', 'quotations visible');
                                    $canViewPurchaseOrders = auth()->user()->can('access', 'purchase orders visible');
                                    $isPurchaseOrderOnlyUser = $canViewPurchaseOrders
                                        && !$canViewSuppliers
                                        && !$canViewQuotations
                                        && !auth()->user()->can('access', 'projects visible')
                                        && !auth()->user()->can('access', 'users visible')
                                        && !auth()->user()->can('access', 'admins visible')
                                        && !auth()->user()->can('access', 'reports visible')
                                        && !auth()->user()->can('access', 'timesheets visible')
                                        && !auth()->user()->can('access', 'workflow visible');
                                @endphp
                                @if ($isPurchaseOrderOnlyUser)
                                <li class="mm-{{ request()->is('purchase-orders*') ? 'active' : '' }}">
                                    <a href="{{ route('purchase.orders.index') }}">
                                        <i class="metismenu-icon pe-7s-note2"></i>
                                        Purchase Orders
                                    </a>
                                </li>
                                @elseif ($canViewSuppliers || $canViewQuotations || $canViewPurchaseOrders)
                                <li>
                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-monitor"></i>
                                        Suppliers Management
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                        @if ($canViewSuppliers)
                                        <li>
                                            <a href="{{ route('suppliers.index') }}" class="mm-{{ request()->is('supplierss/suppliers*') ? 'active' : '' }}">
                                                <i class="metismenu-icon"></i>
                                                Supplier Management
                                            </a>
                                        </li>
                                        @endif
                                        @if ($canViewQuotations)
                                        <li>
                                            <a href="{{ route('quotations.index') }}" class="mm-{{ request()->is('quotations*') ? 'active' : '' }}" >
                                                <i class="metismenu-icon"></i>
                                                Request for Quotation (RFQs)
                                            </a>
                                        </li>
                                        @endif
                                        @if ($canViewPurchaseOrders)
                                        <li>
                                            <a href="{{ route('purchase.orders.index') }}" class="mm-{{ request()->is('purchase-orders*') ? 'active' : '' }}">
                                                <i class="metismenu-icon"></i>
                                                Purchase Orders
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                               </li>
                                @endif
                                 @if (auth()->user()->can('access', 'users visible'))
                                   <!-- Supplier --> 
                                   
                                   
                               <li>
                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-users"></i>
                                         User Manager
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                        <li class="mm-{{ request()->is('users*') ? 'active' : '' }}">
                                          <a href="{{route('users.index')}}">
                                          <i class="metismenu-icon pe-7s-users"></i>
                                         User Manager
                                        </a>
                                      </li>
                                                                
   @if(auth()->user()->month != "14 DAYS FREE TRAIL" && (Auth::user()->roles->first()->slug == 'super_admin' || Auth::user()->roles->first()->slug == 'admin'))
                                  
                                   <!-- Default Permission -->                                    
                                   
                                <li class="mm-{{(Request::segment(1)=='permissions')?'active':''}}">
                                    <a href="{{ route('permissions.index') }}">
                                        <i class="metismenu-icon pe-7s-target"></i>
                                       Default Permission                                       
                                    </a>
                                    
                                </li>
                                
                             @endif
                                
                                
                                
                       @if(Auth::user()->roles->first()->slug=='super_admin')
                                   
                                <li class="mm-{{ in_array(\Request::route()->getName(), ['emailtemplates'])?'active':''}}">
                                    <a href="{{ route('emailtemplates') }}">
                                        <i class="metismenu-icon pe-7s-mail"></i>
                                       Email Templates
                                       
                                    </a>
                                    
                                </li>
                                
                             @endif
                             
                           
                                   
                                <li class="mm-{{ in_array(\Request::route()->getName(), ['helpdesk'])?'active':''}}">
                                    <a href="{{ route('helpdesk') }}">
                                        <i class="metismenu-icon pe-7s-phone"></i>
                                       ICost Help Desk
                                       
                                    </a>
                                    
                                </li>
                                
                          
                             
                             
                             
                             @if(auth()->user()->month != "14 DAYS FREE TRAIL")
                                   
                                <li class="mm-{{ in_array(\Request::route()->getName(), ['users.password']) ? 'active' : '' }}">
                                    <a href="{{ route('users.password') }}">
                                        <i class="metismenu-icon pe-7s-key"></i>
                                       Change Password
                                       
                                    </a>
                                    
                                </li>
                                
                             @endif
                                    </ul>
                               </li>
                                
                                @endif
                                
                              @if (auth()->user()->can('access', 'admins visible'))
                                  <!-- Company --> 
                                <li class="mm-{{ request()->is('companies*') ? 'active' : '' }}">
                                    <a href="{{ route('companies.index') }}">
                                        <i class="metismenu-icon pe-7s-portfolio"></i>
                                        Company Manager
                                       
                                    </a>
                                    
                                </li>
                                
                                @endif
                                
                              @if (auth()->user()->can('access', 'projects visible'))
                                   <!-- Project --> 
                                   
                                   
                                <li class="mm-{{ request()->is('projects*') ? 'active':''}}">
                                    <a href="{{ route('projects')}}">
                                        <i class="metismenu-icon pe-7s-news-paper"></i>
                                       Projects
                                    </a>
                                    
                                </li>
                                
                             @endif
                             
                              @if ((auth()->user()->can('access', 'estimates visible')))
                                   <!-- Estimates --> 
                                   
                                   
                               <li class="mm-{{ (request()->is('carbon/projects*')) ? 'active' : '' }}">
												<a href="{{ route('carbon.projects') }}">
													<i class="metismenu-icon pe-7s-print"></i>
												    Estimate
												   
												</a>
										</li>
										 <li class="mm-{{ (request()->is('library/projects*')) ? 'active' : '' }}">
                                    <a href="{{ route('library.projects') }}">
                                        <i class="metismenu-icon pe-7s-albums"></i>
                                       Library
                                       
                                    </a>
                                    
                                </li>
                                <li class="mm-{{ (request()->is('formulas*')) ? 'active' : '' }}">
												<a href="{{ route('formulas') }}">
													<i class="metismenu-icon pe-7s-target"></i>
												    Formulas
												   
												</a>
										</li>
                                 
                           @endif
                            
                            
                           
                           
                           @if (auth()->user()->can('access', 'carbontoolkit visible'))
                            <li>
							   <a href="#">
                                        <i class="metismenu-icon pe-7s-print"></i>
                                        Carbon Management
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                       
                                        <li class="mm-{{ (request()->is('carbondatabase*')) ? 'active' : '' }}">
                                            <a href="{{ route('carbondatabase') }}"  >
                                                <i class="metismenu-icon pe-7s-photo-gallery"></i>
                                               Carbon Database
                                            </a>
                                        </li>
                                        <li class="mm-{{ (request()->is('carboncalculator*')) ? 'active' : '' }}">
                                            <a href="{{ route('carboncalculator') }}">
                                                <i class="metismenu-icon pe-7s-folder"></i>
                                                CO<sub>2</sub> Calculator
                                            </a>
                                        </li>
                                 </ul>
                               </li> 
                             @endif
							  @if (auth()->user()->can('access', 'central document visible')) 
                             <li class="mm-{{ (request()->is('documentmanager*')) ? 'active' : '' }}">
							  
                               <a href="{{ route('documentmanager') }}">
                                 <i class="metismenu-icon pe-7s-file"></i>
                                   Central Document Manager
                               </a>
                            </li>
                            @endif  
                          @if ((auth()->user()->can('access', 'timesheets visible')))
                                   
                               
                                
                                
                                 <li>
                                    <a href="#">
                                        <i class="metismenu-icon pe-7s-date"></i>
                                       Timesheet Manager
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                         <li>
                                            <a href="{{ route('timesheets.staff') }}" class="mm-{{ (request()->is('timesheets/staff*')) ? 'active' : '' }}">
                                                <i class="metismenu-icon pe-7s-date"></i>
                                               Daily Activity
                                            </a>
                                        </li>
                                       
                                        <li>
                                            <a href="{{ route('timesheets.labour') }}" class="mm-{{ (request()->is('timesheets/labour*')) ? 'active' : '' }}" >
                                                <i class="metismenu-icon"></i>
                                                Site Operative Timesheet
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('timesheets.staff.weekly') }}" class="mm-{{ (request()->is('timesheets/staff-weekly*')) ? 'active' : '' }}">
                                                <i class="metismenu-icon"></i>
                                                Weekly Staff Timesheet
                                            </a>
                                        </li>
                                        
                                         <li>
                                            <a href="{{ route('timesheets.labour.weekly') }}" class="mm-{{ (request()->is('timesheets/labour-weekly*')) ? 'active' : '' }}">
                                                <i class="metismenu-icon"></i>
                                                Weekly Site Ops Timesheet
                                            </a>
                                        </li>
                                    </ul>
                               </li>
                                
                             @endif
                             
   
                           <!-- @if ((auth()->user()->can('access', 'timesheets visible')))
                                   
                               <li>
                                            <a href="{{ route('timesheets.staff') }}" class="mm-{{ (request()->is('timesheets/staff*')) ? 'active' : '' }}">
                                                <i class="metismenu-icon"></i>
                                                Staff Timesheet
                                            </a>
                                        </li>
                                       
                                       
                                
                             @endif-->
                             
                             
                             @if ((auth()->user()->can('access', 'reports visible')))
                                   <!-- Reports --> 
                                   
                                   
                                <li class="mm-{{ (request()->is('reports*')) ? 'active' : '' }}">
                                    <a href="{{ route('reports.index') }}">
                                        <i class="metismenu-icon pe-7s-news-paper"></i>
                                       Reporting
                                       
                                    </a>
                                    
                                </li>
                                
                             @endif
                                
                             
                             
                             
                       
                                   
                                <li class="mm-{{ in_array(\Request::route()->getName(), ['logout']) ? 'active' : '' }}">
                                    <a href="#" data-toggle="modal" data-target="#Logout">
                                        <i class="metismenu-icon pe-7s-power"></i>
                                       {{ __('Logout') }}
                                       
                                    </a>
                                    
                                </li>
                                
                             
                            </ul>
                        </div>
                    </div>
                </div>

