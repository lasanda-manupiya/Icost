<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Modules\ProjectManager\Entities\Project;
use Modules\SettingManager\Mail\PlanExpireMailToUser;
class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    public function firstclasspostcodes($pcode) {
        
        
    $client = new \GuzzleHttp\Client();
    $request = $client->get('https://api.getAddress.io/find/'.$pcode.'?expand=true&api-key=4R-5iSK4hUaOCcJTN3HPuQ30491');
    $response = $request->getBody();
   
  return $response;
       /*
        include(app_path() . '\firstclasspostcodes\firstclasspostcodes\Client.php');


        $API_KEY = 'HMtIJK1IKZetn9xoLOF1H4Qgatn6jy1Y9aHBYc00';
                $client = new \Firstclasspostcodes\Client(['apiKey' => $API_KEY ]);
                $data = $client->getPostcode($pcode);
                
                if(!empty($data))
                {
                    $data1['city']      = $data['city'];
                    $data1['county']    = $data['county'];
                    $data1['numbers']   = $data['numbers'];
                    $data1['streets']   = $data['streets'];
                    $data1['country']   = $data['country'];
                    echo json_encode($data1);
                }
                else
                {
                    echo 'Response Error !!!';
                }*/
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {

        return redirect()->route('purchase.orders.index');

        $title = 'Dashboard';
        $query1 = User::query();
        $query2 = User::query();
        $query3 = User::query();
        
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $query1->where('company_id', auth()->id());
                $query2->where('company_id', auth()->id());
                $query3->where('company_id', auth()->id());
            }else{
                $query1->whereHas('company', function($q){
                    $q->where('id', auth()->user()->company_id);
                });
                $query2->whereHas('company', function($q){
                    $q->where('id', auth()->user()->company_id);
                });
                $query3->whereHas('company', function($q){
                    $q->where('id', auth()->user()->company_id);
                });
            }
        }
  
        $admin_count = $query1->whereHas('roles', function($query) {
                            $query->where('slug', '=', 'admin');
                        })->count();
        
        $supplier_count =  $query2->whereHas('roles', function($query) {
                            $query->where('slug', '=', 'supplier');
                        })->count();
        
        $user_count =  $query3->whereHas('roles', function($query) {
                            $query->whereNotIn('slug', ['super_admin', 'admin', 'supplier']);
                        })->count();
                        
        $query = Project::whereStatus(1);
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $query->where('company_id', auth()->id());
            }else{
                $query->where('company_id', auth()->user()->company_id);
            }
        }
        $projects = $query->get(['id', 'project_title', 'version']);
        $projects = $projects->pluck('display_project_title', 'id');
        
        
        
        $yearStart = date('Y-01-01');
        $yearEnd = date('Y-m-d', strtotime('last day of december'));
        
      
      
        //last week total hours
        
        $today = date("Y-m-d"); 
        $day2 = date('Y-m-d', strtotime($today. ' - 1 days'));
        $day3 = date('Y-m-d', strtotime($day2. ' - 1 days'));
        $day4 = date('Y-m-d', strtotime($day3. ' - 1 days'));
        $day5 = date('Y-m-d', strtotime($day4. ' - 1 days'));
        $day6 = date('Y-m-d', strtotime($day5. ' - 1 days'));
        $day7 = date('Y-m-d', strtotime($day6. ' - 1 days'));
        $last_seven_days = array($today,$day2,$day3,$day4,$day5,$day6,$day7);
        
        $weekdayss = array();
        foreach($last_seven_days as $key => $weekdays){
            $staff_lastweeek_totals =  \DB::table('staff_timesheets')->join('projects','staff_timesheets.project_id', '=', 'projects.id')->where('role', 'detail')->where('timesheet_date', $weekdays);
            if(!auth()->user()->isRole('Super Admin')){
                if(auth()->user()->isRole('Admin')){
                    $staff_lastweeek_totals->where('projects.company_id', auth()->id());
                }else{
                    $staff_lastweeek_totals->where('projects.company_id', auth()->user()->company_id);
                }
            }
            $staff_lastweeek_total = $staff_lastweeek_totals->selectRaw('sum(hours) as sum')->pluck('sum');
            $labour_lastweeek_totals = \DB::table('labour_timesheets')->join('projects','labour_timesheets.project_id', '=', 'projects.id')->join('labour_timesheet_materials','labour_timesheets.id', '=', 'labour_timesheet_materials.labour_timesheet_id')->where('labour_timesheets.timesheet_date', $weekdays); 
            if(!auth()->user()->isRole('Super Admin')){
                if(auth()->user()->isRole('Admin')){
                    $labour_lastweeek_totals->where('projects.company_id', auth()->id());
                }else{
                    $labour_lastweeek_totals->where('projects.company_id', auth()->user()->company_id);
                }
            }
            $labour_lastweeek_total = $labour_lastweeek_totals->selectRaw('sum(labour_timesheet_materials.hours) as sum')->pluck('sum');
            
            $total = $staff_lastweeek_total[0] + $labour_lastweeek_total[0];
         
            $weekdayss[$key]['hours'] = $total;
            //$weekdayss[$key]['dates'] = $weekdays;
            
            $date = strtotime($weekdays); 
            $weekdayss[$key]['dates'] = date('d-M-Y', $date); 
           
        }  
        
        $past_week = array_reverse($weekdayss);
        //---------------
        
        //yearly project graph
        $labour_yearly_projects = \DB::table('labour_timesheets')->join('projects','labour_timesheets.project_id', '=', 'projects.id')->join('labour_timesheet_materials','labour_timesheets.id', '=', 'labour_timesheet_materials.labour_timesheet_id')->whereBetween('labour_timesheets.timesheet_date', [$yearStart, $yearEnd]); 
        if(!auth()->user()->isRole('Super Admin')){
                if(auth()->user()->isRole('Admin')){
                    $labour_yearly_projects->where('projects.company_id', auth()->id());
                }else{
                    $labour_yearly_projects->where('projects.company_id', auth()->user()->company_id);
                }
            }
        $labour_yearly_project = $labour_yearly_projects->selectRaw('sum(labour_timesheet_materials.hours) as sum, labour_timesheets.project_id as project_id')->groupBy('labour_timesheets.project_id')->get()->toArray();
        /*foreach($labour_yearly_task as $lab_task){
            $lab_task->type = "Labour";
        }*/
        $staff_yearly_projects =  \DB::table('staff_timesheets')->join('projects','staff_timesheets.project_id', '=', 'projects.id')->where('role', 'detail')->whereBetween('timesheet_date', [$yearStart, $yearEnd]);
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $staff_yearly_projects->where('projects.company_id', auth()->id());
            }else{
                $staff_yearly_projects->where('projects.company_id', auth()->user()->company_id);
            }
        }
        $staff_yearly_project = $staff_yearly_projects->selectRaw('sum(hours) as sum, project_id as project_id')->groupBy('project_id')->get()->toArray();
        /*foreach($staff_yearly_task as $staff_task){
            $staff_task->type = "Staff";
        }*/
        $yearly_timesheet_project = array_merge($labour_yearly_project,$staff_yearly_project); 
        
        $tmp = array();
        
        foreach($yearly_timesheet_project as $arg)
        {
            $tmp[$arg->project_id][] = $arg->sum;
        }
        
        $yearly_timesheet_proj = array();
        
        foreach($tmp as $type => $labels)
        {
            $yearly_timesheet_proj[] = array(
                'project' => $type,
                'sum' => $labels
            );
        }
        foreach($yearly_timesheet_proj as $key =>$sum_data){
         
            $project_name =  \DB::table('projects')->where('id', $sum_data['project'])->select('project_title')->value('project_title');

           $yearly_timesheet_proj[$key]['task'] = $project_name;
           $yearly_timesheet_proj[$key]['total'] = array_sum ( $sum_data['sum'] );
        }
        $timesheets =array();
        $timesheets['yearly_project_timesheet'] = $yearly_timesheet_proj;
        
        //Estimate Gantt
        
     
        $graphs['costperformance'] = $this->projects();
        
        
        
        
        
        $query = Project::whereStatus(1);
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $query->where('company_id', auth()->id());
            }else{
                $query->where('company_id', auth()->user()->company_id);
            }
        }
        $allProjects = $query->get(['id', 'project_title', 'version']);
        
         $all_proj = array();
         $da = array();
         $i=0;
        // print_r($allProjects);exit;
        $graphs['stf_lbr']=array();
        foreach($allProjects as $k => $v){
           
            $staff_total_hours=0;
            $datas= \DB::table('staff_timesheets')->where('project_id', '=', $v->id)->where('role', '=', 'detail')->sum('total_hours');
            $activityies_st = \DB::table('staff_timesheets')->where('project_id', '=', $v->id)->where('role', '=', 'detail')->distinct('activity_id')->get('activity_id');
            $staff_total_hours = 0;
            foreach($activityies_st as $ids){
            $quan= \DB::table('activities')->where('id', '=', $ids->activity_id)->pluck('quantity')->first();
            $staff_total_hours+= $quan; 
            }
            
            $hr=0;
            $datas_labour = \DB::table('labour_timesheets')->groupby('activity_id')->where('project_id', '=', $v->id)->pluck('allocated_hour');
            $datas_labours =0;
            foreach($datas_labour as $val){
                $mt=0;
                $split = explode(":",$val);
               
                $hr+=(int)$split[0];
                if($split[1] != 00){
                    $mt+=$split[1];
                }
                $quotient=0;
                $remainder=0;
                if($mt >= 60){
                    $quotient = (int)($mt / 60);
                    $remainder = $mt % 60;
                    
                   
                }
                if($quotient > 0){
                    $hr+=$quotient;
                }
               //$datas_labours = $hr.":".$remainder;
               $datas_labours = (int)$hr;
            }
            
            
            $hr1=0;
            $datas_labour_spent = \DB::table('labour_timesheets')->where('project_id', '=', $v->id)->pluck('spent_hour');
            $datas_labour_spents =0;
            foreach($datas_labour_spent as $vals){
                
                $mt1=0;
                $splits = explode(":",$vals);
            
                $hr1+=(int)$splits[0];
                /*if($splits[1] != 00 || $splits[1] != 0){
                    $mt1+=$splits[1];
                }*/
                $quotient1=0;
                $remainder1=0;
                if($mt1 >= 60){
                    $quotient1 = (int)($mt / 60);
                    $remainder1 = $mt % 60;
                    
                   
                }
                if($quotient1 > 0){
                    $hr1+=$quotient1;
                }
               //$datas_labour_spents = $hr1.":".$remainder1;
               $datas_labour_spents = $hr1;
            }
          $graphs['stf_lbr'][$i]['labour_spent'] =    $datas_labour_spents;
          $graphs['stf_lbr'][$i]['labour_total_hours'] =    $datas_labours;
         $graphs['stf_lbr'][$i]['title'] =  $v->project_title; 
         $graphs['stf_lbr'][$i]['id'] =  $v->id; 
         $graphs['stf_lbr'][$i]['staff_total_hours'] =  $datas; 
         $graphs['stf_lbr'][$i]['staff_estimate'] =  number_format($staff_total_hours, 2);  
        
            $i++;
        }
      
     

        return view('home', compact('title', 'user_count', 'admin_count', 'supplier_count', 'projects','past_week','timesheets','graphs'));
    }
    
    public function projects(){
      
        try{
                $query = Project::query();
                //$query = \DB::table('projects');
                if(!auth()->user()->isRole('Super Admin')){
                    if(auth()->user()->isRole('Admin')){
                        $query->where('company_id', auth()->id());
                    }else{
                        $query->where('company_id', auth()->user()->company_id);
                    }
                }
                /*$query->when(($request->has('project_id') && $request->query('project_id')), function($q) use($request){
                    $q->where('id', $request->query('project_id'));
                });*/
                $projects = $query->get();
                //$view = view('reportmanager::project_report_list', compact('projects'))->render();
            }catch(\Exception $e){}
        return $projects;
    }
    
     public function purchaseReports(Request $request){
        $view = "";
        if($request->ajax()){
            try{
                $project = Project::where('id', $request->query('project_id'))->first();
                if($project){
                    $dates = [];
                    $query = $project->purchases();
                    if($request->query('date_form') && $request->query('date_to')){
                        $date['form'] = $request->query('date_form');
                        $date['to'] = $request->query('date_to');
                        $query->where('delivery_date', [$date['form'], $date['to']]);
                    }
                    $purchases = $query->groupBy('sub_activity_id')->orderBy('delivery_date', 'DESC')->get();
                    $view = view('purchase_report_list', compact('project', 'purchases', 'dates'))->render();
                }  
            }catch(\Exception $e){

            }
        }
      
        return response()->json(['html'=> $view]);
    }
    /**
     * Get the project report.
     * @param Request $request
     * @return Response
     */
    public function projectStatics(Request $request){
        $view = "";
        $chart = "";
        
        if($request->ajax()){
            try{
                $estimate_month1 = 0;
                $estimate_month2 = 0;
                $estimate_month3 = 0;
                $estimate_month4 = 0;
                $estimate_month5 = 0;
                $estimate_month6 = 0;
                $estimate_month7 = 0;
                $estimate_month8 = 0;
                $estimate_month9 = 0;
                $estimate_month10 = 0;
                $estimate_month11 = 0;
                $estimate_month12 = 0;

                $purchases_month1 = 0;
                $purchases_month2 = 0;
                $purchases_month3 = 0;
                $purchases_month4 = 0;
                $purchases_month5 = 0;
                $purchases_month6 = 0;
                $purchases_month7 = 0;
                $purchases_month8 = 0;
                $purchases_month9 = 0;
                $purchases_month10 = 0;
                $purchases_month11 = 0;
                $purchases_month12 = 0;

                $profit_month1 = 0;
                $profit_month2 = 0;
                $profit_month3 = 0;
                $profit_month4 = 0;
                $profit_month5 = 0;
                $profit_month6 = 0;
                $profit_month7 = 0;
                $profit_month8 = 0;
                $profit_month9 = 0;
                $profit_month10 = 0;
                $profit_month11 = 0;
                $profit_month12 = 0;
                            
                $sunday = date( 'Y-m-d', strtotime( 'last Sunday' ));
                $saturday = date('Y-m-d', strtotime('-1 day', strtotime($sunday)));
                $friday = date('Y-m-d', strtotime('-1 day', strtotime($saturday)));
                $thursday = date('Y-m-d', strtotime('-1 day', strtotime($friday)));
                $wednesday = date('Y-m-d', strtotime('-1 day', strtotime($thursday)));
                $tuesday = date('Y-m-d', strtotime('-1 day', strtotime($wednesday)));
                $monday = date('Y-m-d', strtotime('-1 day', strtotime($tuesday)));

                $estimate_monday = 0;
                $purchases_monday = 0;
                $profit_monday = 0;

                $estimate_tuesday = 0;
                $purchases_tuesday = 0;
                $profit_tuesday = 0;

                $estimate_wednesday = 0;
                $purchases_wednesday = 0;
                $profit_wednesday = 0;

                $estimate_thursday = 0;
                $purchases_thursday = 0;
                $profit_thursday = 0;

                $estimate_friday = 0;
                $purchases_friday = 0;
                $profit_friday = 0;

                $estimate_saturday = 0;
                $purchases_saturday = 0;
                $profit_saturday = 0;

                $estimate_sunday = 0;
                $purchases_sunday = 0;
                $profit_sunday = 0;

                $estimate_week = 0;
                $purchases_week = 0;
                $profit_week = 0;


                $estimate_today = 0;
                $purchases_today = 0;
                $profit_today = 0;
                $i = 1;

                $project = Project::findOrFail($request->project_id);

                while($i <= 12){
                    $purchaseMonths = $project->purchases()->whereMonth('created_at', $i)
                                        ->whereYear('created_at', \Carbon\Carbon::now())
                                        ->get();
                    foreach($purchaseMonths as $purchaseMonth){
                        foreach($purchaseMonth->orders as $purchaseMonthOrder){

                            if($i == 1){
                                $estimate_month1 =  $estimate_month1 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month1 =  $purchases_month1 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 2){
                                $estimate_month2 =  $estimate_month2 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month2 =  $purchases_month2 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 3){
                                $estimate_month3 =  $estimate_month3 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month3 =  $purchases_month3 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 4){
                                $estimate_month4 =  $estimate_month4 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month4 =  $purchases_month4 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 5){
                                $estimate_month5 =  $estimate_month5 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month5 =  $purchases_month5 + $purchaseMonthOrder->total;
                            }
                               
                               
                            if($i == 6){
                                $estimate_month6 =  $estimate_month6 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month6 =  $purchases_month6 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 7){
                                $estimate_month7 =  $estimate_month7 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month7 =  $purchases_month7 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 8){
                                $estimate_month8 =  $estimate_month8 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month8 =  $purchases_month8 + $purchaseMonthOrder->total;
                            }
                               
                            if($i == 9){
                                $estimate_month9 =  $estimate_month9 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month9 =  $purchases_month9 + $purchaseMonthOrder->total;
                            }
                           
                            if($i == 10){
                                $estimate_month10 =  $estimate_month10 + $purchaseMonthOrder->activityOfOrder->total;
                         
                                $purchases_month10 =  $purchases_month10 + $purchaseMonthOrder->total;
                            }
                            if($i == 11){
                                $estimate_month11 =  $estimate_month11 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month11 =  $purchases_month11 + $purchaseMonthOrder->total;
                            }
                           
                            if($i == 12){
                                $estimate_month12 =  $estimate_month12 + $purchaseMonthOrder->activityOfOrder->total;
                                $purchases_month12 =  $purchases_month12 + $purchaseMonthOrder->total;
                            }
                              
                        }
                             
                    }
                    $i = $i+1;        
                }
            
                $profit_month1 = $estimate_month1-$purchases_month1;
                $profit_month2 = $estimate_month2-$purchases_month2;
                $profit_month3 = $estimate_month3-$purchases_month3;
                $profit_month4 = $estimate_month4-$purchases_month4;
                $profit_month5 = $estimate_month5-$purchases_month5;
                $profit_month6 = $estimate_month6-$purchases_month6;
                $profit_month7 = $estimate_month7-$purchases_month7;
                $profit_month8 = $estimate_month8-$purchases_month8;
                $profit_month9 = $estimate_month9-$purchases_month9;
                $profit_month10 = $estimate_month10-$purchases_month10;
                $profit_month11 = $estimate_month11-$purchases_month11;
                $profit_month12 = $estimate_month12-$purchases_month12;
                
                $rounded_high = 20;
            
                $purchases1 = $project->purchases()->whereDate('created_at', $monday)->get();
                foreach($purchases1 as $purchase1){
                    foreach($purchase1->orders as $purchase1Order){
                        $estimate_monday =  $estimate_monday + $purchase1Order->activityOfOrder->total;
                        $purchases_monday =  $purchases_monday + $purchase1Order->total;
                    }
                }
                $profit_monday = $estimate_monday - $purchases_monday;
                if($rounded_high < $profit_monday){
                    $rounded_high = $profit_monday;
                }
                $purchases2 = $project->purchases()->whereDate('created_at', $tuesday)->get();
                foreach($purchases2 as $purchase2){
                    foreach($purchase2->orders as $purchase2Order){
                        $estimate_tuesday =  $estimate_tuesday + $purchase2Order->activityOfOrder->total;
                        $purchases_tuesday =  $purchases_tuesday + $purchase2Order->total;
                    }
                }
                $profit_tuesday=$estimate_tuesday - $purchases_tuesday;
                if($rounded_high<$profit_tuesday){
                    $rounded_high=$profit_tuesday;
                }

                $purchases3 = $project->purchases()->whereDate('created_at', $wednesday)->get();
                foreach($purchases3 as $purchase3){
                    foreach($purchase3->orders as $purchase3Order){
                        $estimate_wednesday =  $estimate_wednesday + $purchase3Order->activityOfOrder->total;
                        $purchases_wednesday =  $purchases_wednesday + $purchase3Order->total;
                    }
                }
                $profit_wednesday = $estimate_wednesday - $purchases_wednesday;
                if($rounded_high < $profit_wednesday){
                    $rounded_high = $profit_wednesday;
                }
 
                $purchases4 = $project->purchases()->whereDate('created_at', $thursday)->get();
                foreach($purchases4 as $purchase4){
                    foreach($purchase4->orders as $purchase4Order){
                        $estimate_thursday =  $estimate_thursday + $purchase4Order->activityOfOrder->total;
                        $purchases_thursday =  $purchases_thursday + $purchase4Order->total;
                    }
                }
                $profit_thursday=$estimate_thursday - $purchases_thursday;
                if($rounded_high<$profit_thursday){
                    $rounded_high=$profit_thursday;
                }  
              
                $purchases5 = $project->purchases()->whereDate('created_at', $friday)->get();
                foreach($purchases5 as $purchase5){
                    foreach($purchase5->orders as $purchase5Order){
                        $estimate_friday =  $estimate_friday + $purchase5Order->activityOfOrder->total;
                        $purchases_friday =  $purchases_friday + $purchase5Order->total;
                    }
                }
                $profit_friday=$estimate_friday - $purchases_friday;
                if($rounded_high<$profit_friday){
                    $rounded_high=$profit_friday;
                }

                $purchases6 = $project->purchases()->whereDate('created_at', $saturday)->get();
                foreach($purchases6 as $purchase6){
                    foreach($purchase6->orders as $purchase6Order){
                        $estimate_saturday =  $estimate_saturday + $purchase6Order->activityOfOrder->total;
                        $purchases_saturday =  $purchases_saturday + $purchase6Order->total;
                    }
                }
                $profit_saturday=$estimate_saturday - $purchases_saturday;
                if($rounded_high<$profit_saturday){
                    $rounded_high=$profit_saturday;
                }

                $purchases7 = $project->purchases()->whereDate('created_at', $sunday)->get();
                foreach($purchases7 as $purchase7){
                    foreach($purchase7->orders as $purchase7Order){
                        $estimate_sunday =  $estimate_sunday + $purchase7Order->activityOfOrder->total;
                        $purchases_sunday =  $purchases_sunday + $purchase7Order->total;
                    }
                }
                $profit_sunday = $estimate_sunday - $purchases_sunday;
                if($rounded_high < $profit_sunday){
                  $rounded_high = $profit_sunday;
                }
                $rounded_high = $rounded_high + 20;

                $purchases8 = $project->purchases()->whereDate('created_at', \Carbon\Carbon::now())->get();
                foreach($purchases8 as $purchase8){
                    foreach($purchase8->orders as $purchase8Order){
                        $estimate_today .= ",".$purchase8Order->activityOfOrder->total;
                        $purchases_today .= ",".$purchase8Order->total;
                        $profit_today .= ",".(($purchase8Order->activityOfOrder->total - $purchase8Order->total)/50);
                    }
                }

                 $weeklyPurchases = $project->purchases()->whereBetween('created_at', [$monday, $sunday])->get();
                
                
                foreach($weeklyPurchases as $weeklyPurchase){
                    
                   
                    foreach($weeklyPurchase->orders as $weeklyPurchaseOrder){ 
                      
                        $estimate_week.= ",".(int)$weeklyPurchaseOrder->activityOfOrder->total;
                        $purchases_week.= ",".(int)$weeklyPurchaseOrder->total;
                        $profit_week.= ",".($weeklyPurchaseOrder->activityOfOrder->total - $weeklyPurchaseOrder->total);
                        
                       
                       
                    } 
                }

                $view = view('project_statics')->render();
                $chart = [
                    'profit_today'=> $profit_today,
                    'profit_monday'=> $profit_monday,
                    'profit_tuesday'=> $profit_tuesday,
                    'profit_wednesday'=> $profit_wednesday,
                    'profit_thursday'=> $profit_thursday,
                    'profit_friday'=> $profit_friday,
                    'profit_saturday'=> $profit_saturday,
                    'profit_sunday'=> $profit_sunday,
                    'rounded_high'=> $rounded_high,
                    'estimate_week'=> $estimate_week,
                    'purchases_week'=> $purchases_week,
                    'estimate_month1'=> $estimate_month1,
                    'estimate_month2'=> $estimate_month2,
                    'estimate_month3'=> $estimate_month3,
                    'estimate_month4'=> $estimate_month4,
                    'estimate_month5'=> $estimate_month5,
                    'estimate_month6'=> $estimate_month6,
                    'estimate_month7'=> $estimate_month7,
                    'estimate_month8'=> $estimate_month8,
                    
                    
                    
                    'estimate_month9'=> $estimate_month9,
                    'estimate_month10'=> $estimate_month10,
                    'estimate_month11'=> $estimate_month11,
                    'estimate_month12'=> $estimate_month12,
                    'purchases_month1'=> $purchases_month1,
                    'purchases_month2'=> $purchases_month2,
                    'purchases_month3'=> $purchases_month3,
                    'purchases_month4'=> $purchases_month4,
                    'purchases_month5'=> $purchases_month5,
                    'purchases_month6'=> $purchases_month6,
                    'purchases_month7'=> $purchases_month7,
                    'purchases_month8'=> $purchases_month8,
                    'purchases_month9'=> $purchases_month9,
                    'purchases_month10'=> $purchases_month10,
                    'purchases_month11'=> $purchases_month11,
                    'purchases_month12'=> $purchases_month12,
                    'profit_month1'=> $profit_month1,
                    'profit_month2'=> $profit_month2,
                    'profit_month3'=> $profit_month3,
                    'profit_month4'=> $profit_month4,
                    'profit_month5'=> $profit_month5,
                    'profit_month6'=> $profit_month6,
                    'profit_month7'=> $profit_month7,
                    'profit_month8'=> $profit_month8,
                    'profit_month9'=> $profit_month9,
                    'profit_month10'=> $profit_month10,
                    'profit_month11'=> $profit_month11,
                    'profit_month12'=> $profit_month12,
                ];
                
           
            }catch(\Exception $e){ }
        }
       
        return response()->json(['html'=> $view, 'chart'=> $chart]);
    }
    
    /**
     * Get the project report.
     * @param Request $request
     * @return Response
     */
    public function projectGantt(Request $request){
      
        return 1;
    
    }
    
    public function suppliervsestimate(Request $request){
      $query = \DB::table('purchases')
       ->join('users', 'purchases.supplier_id', '=', 'users.id')
      ->selectRaw('sum(grand_total) as sum, supplier_id, full_name')
      ->groupBy('supplier_id')
      ->where('project_id', $request->project_id)
      ->pluck('sum','full_name');
    
        return $query;
    
    }
    public function projecttasktime(Request $request){
        
        
        //yearly task graph $request->project_id
        $yearStart = date('Y-01-01');
        $yearEnd = date('Y-m-d', strtotime('last day of december'));
        $labour_yearly_tasks = \DB::table('labour_timesheets')->join('labour_timesheet_materials','labour_timesheets.id', '=', 'labour_timesheet_materials.labour_timesheet_id')->join('projects','labour_timesheets.project_id', '=', 'projects.id')->whereBetween('labour_timesheets.timesheet_date', [$yearStart, $yearEnd])->where('labour_timesheets.project_id',$request->project_id); 
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $labour_yearly_tasks->where('projects.company_id', auth()->id());
            }else{
                $labour_yearly_tasks->where('projects.company_id', auth()->user()->company_id);
            }
        }
        
        $labour_yearly_task = $labour_yearly_tasks->selectRaw('sum(labour_timesheet_materials.hours) as sum, labour_timesheets.activity as task')->groupBy('labour_timesheets.activity')->get()->toArray();
        foreach($labour_yearly_task as $lab_task){
            $lab_task->type = "Labour";
        }
        
        
        
        
        $staff_yearly_tasks =  \DB::table('staff_timesheets')->join('projects','staff_timesheets.project_id', '=', 'projects.id')->where('role', 'detail')->whereBetween('timesheet_date', [$yearStart, $yearEnd])->where('staff_timesheets.project_id',$request->project_id);
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $staff_yearly_tasks->where('projects.company_id', auth()->id());
            }else{
                $staff_yearly_tasks->where('projects.company_id', auth()->user()->company_id);
            }
        }
        $staff_yearly_task = $staff_yearly_tasks->selectRaw('sum(hours) as sum, activity as task')->groupBy('activity_id')->get()->toArray();
        foreach($staff_yearly_task as $staff_task){
            $staff_task->type = "Staff";
        }
        $yearly_timesheet_task = array_merge($labour_yearly_task,$staff_yearly_task); 
       
        return $yearly_timesheet_task;
    }
    public function getuserhourthisweek(Request $request){
    $weekly_staff_labour =array();
        $d = strtotime("today");
        $start_week = strtotime("last sunday midnight",$d);
        $end_week = strtotime("next saturday",$d);
        $start = date("Y-m-d",$start_week); 
        $end = date("Y-m-d",$end_week);  
        
        $staff_weekly_dat =  \DB::table('staff_timesheets')->where('role', 'detail')->join('projects','staff_timesheets.project_id', '=', 'projects.id')->whereBetween('timesheet_date', [$start, $end])->where('staff_timesheets.project_id',$request->project_id);   
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $staff_weekly_dat->where('projects.company_id', auth()->id());
            }else{
                $staff_weekly_dat->where('projects.company_id', auth()->user()->company_id);
            }
        }
        $staff_weekly_data = $staff_weekly_dat->selectRaw('sum(hours) as sum, activity as user')->groupBy('activity')->get()->toArray();
        foreach($staff_weekly_data as $staff){
            $staff->type = "Staff";
        }
        
        $labour_weekly_data = array();
        $labour_weekly_dat = \DB::table('labour_timesheets')->join('labour_timesheet_materials','labour_timesheets.id', '=', 'labour_timesheet_materials.labour_timesheet_id')->join('projects','labour_timesheets.project_id', '=', 'projects.id')->whereBetween('labour_timesheets.timesheet_date', [$start, $end])->where('labour_timesheets.project_id',$request->project_id);   
        if(!auth()->user()->isRole('Super Admin')){
            if(auth()->user()->isRole('Admin')){
                $labour_weekly_dat->where('projects.company_id', auth()->id());
            }else{
                $labour_weekly_dat->where('projects.company_id', auth()->user()->company_id);
            }
        }
        $labour_weekly_data = $labour_weekly_dat->selectRaw('sum(labour_timesheet_materials.hours) as sum, labour_timesheet_materials.operative as user')->groupBy('labour_timesheet_materials.operative')->get()->toArray();
        if(!empty($labour_weekly_data)){
            foreach($labour_weekly_data as $lab){
                $lab->type = "Labour";
            }
        }
        $weekly_staff_labour = array_merge($labour_weekly_data,$staff_weekly_data); 
        return $weekly_staff_labour;
    }
    
    
    
}
