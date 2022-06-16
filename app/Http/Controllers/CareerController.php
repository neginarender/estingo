<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Career;
use App\Opening;
use App\JobLocation;
use DB;
use Mail;
use Storage;
use App\Mail\InvoiceEmailManager;
use App\Mail\EmailManager;
use App\Mail\JobEmailManager;
use App\Mail\CareerEmailManager;

class CareerController extends Controller
{

    public function index(Request $request){

        $openings = Opening::where('status','1')->orderBy('designation','ASC');

        if(isset($request->location) && $request->location != 'all'){
            $openings = $openings->where('location', 'like', '%' . $request->location . '%');
        }

        $openings = $openings->paginate(10);

        $location = JobLocation::where('status','1')->select('id','city')->orderBy('city','ASC')->get();
        return view("frontend.career.index",compact('openings','location'));
    }

    public function store(Request $request){


        $validatedData = $request->validate([
            'email' => 'email',
            'cv' => 'required|mimes:jpg,pdf',
        ]);

        DB::beginTransaction();
        try{

                $career = new Career;
                $career->name = $request->name;
                $career->email = $request->email;
                $career->mobile = $request->mobile;
                // $career->address = $request->address;

                $career->expertise = $request->expertise;
                $career->designation_id = $request->designation_id;
                $career->about_yourself = $request->intro;

                if($request->hasFile('cv')){
                    $career->CV = $request->cv->store('uploads/careers');

                    $image = $request->file('cv');
                    $path = base_path('uploads/careers');
                    $file_name = $request->cv->getClientOriginalName();
                    $image->move($path, $file_name);
                    // $career->CV = $path.'/'.$file_name;
                }

                $designationName = Opening::where('id',$request->designation_id)->first()->designation;
                
                $career->save();
                $array = array(); 
                $applicant_mail = array();

                    $array['view'] = 'emails.send_hr_career';
                    $array['subject'] = 'Job Apply For -'.$designationName;
                    $array['from'] = env('mail_from_address');
                    $array['content'] = " ";
                    $array['file'] = Storage::disk('s3')->url($career->CV);
                    $array['file_name'] = $request->file('cv')->getClientOriginalName();

                    // Mail::to(env('HR_MAIL'))->queue(new JobEmailManager($array));
                    
                    Mail::to(env('HR_MAIL'))
                        ->cc(['monica@rozana.in','monica@freshcartons.in'])
                        ->queue(new JobEmailManager($array));
                $applicant_mail['view'] = 'emails.career';
                $applicant_mail['subject'] = 'Job Apply For -'.$designationName;
                $applicant_mail['from'] = env('mail_from_address');

                $applicant_mail['content'] = " 
                Hi Mr/Ms ".ucfirst($career->name).","."</br>".

                "We hope you are doing well."."</br>".
                
                "Thank you, for your interest in working with us. We'd like to let you know that we have received your application. Our HR team will be reviewing all applications and will get back to you if your profile gets shortlisted."."</br>".
                
                "In case you don't hear from us, please don't be disheartened. We have your details and will be in touch the moment something exciting and more suitable opens up."."</br>".
                
                "All the best,"."</br>".
                "Team Rozana";
                $applicant_mail['name'] = ucfirst($career->name);

                Mail::to($career->email)->queue(new CareerEmailManager($applicant_mail));
                DB::commit();
                flash(translate('Thank you for applying!'))->success();
                return back();
                
        }catch (\Exception $e) {
            DB::rollback();
            flash(translate($e->getMessage()))->error();
            return back();
           
           
        }

    }

    public function list(){
        $careers = Career::orderBy('created_at','DESC');
        $careers =$careers->paginate(10);
        return view('career.list', compact('careers'));
    }

    public function detail($id){
        $open = Opening::findOrFail($id);
        if($open){
            $openings = Opening::where('id',$id)->first();
            return view("frontend.career.detail",compact('openings'));
        }
        return back();
    }

    public function search(Request $request){

        $openings = Opening::where('status','1')->orderBy('designation','ASC');

        if(isset($request->location) && $request->location != 'all'){
            $openings = $openings->where('location', 'like', '%' . $request->location . '%');
        }

        $openings = $openings->paginate(10);

        $location = JobLocation::where('status','1')->select('id','city')->orderBy('city','ASC')->get();
        return view("frontend.career.index",compact('openings','location'));
    }

}
