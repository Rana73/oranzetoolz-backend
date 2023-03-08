<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\UsersOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    // ======================== Before Login ===========================
    public function sendEmail(Request $request){
        $request->validate([
            'email' => 'required|email:rfc,dns|max:60'
        ]);
        try {
            $user_info = User::where('email',$request->email)->select('id','email','name')->first();
            if(!empty($user_info))
            {
                DB::beginTransaction();           
                $email = $user_info->email;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    DB::rollBack();
                    return responder()
                        ->status('failed')
                        ->code(404)
                        ->message('Your email is invalid!');
                }
                $reset_code = User::generateResetPasswordCode();
                $url =$request->base_url."/user-reset-password/".$reset_code;
                $name = $user_info->name;
                $user = [
                    'name' => $name,
                    'email' => $email,
                    'url' => $url
                ];
                User::where('id',$user_info->id)->update(['pass_reset_code'=>$reset_code]);              
                Mail::to($email)->send(new \App\Mail\ResetPasswordMail($user));
                if(Mail::failures()){
                    DB::rollBack();
                    return responder()
                        ->status('failed')
                        ->code(404)
                        ->message('Your email is invalid!');
                }
                $email_first_clause = substr($user_info->email,0,3);
                $email_last_clause = explode("@",$user_info->email)[1];
                $email_address = $email_first_clause.'*****@'.$email_last_clause;
                DB::commit();
            return responder()
                ->status('success')
                ->code(200)
                ->message('We sent your password reset link to: '.$email_address);

            }
            else
            {
            return responder()
                ->status('failed')
                ->code(404)
                ->message('Your email does not match.');
            }
         } catch (\Throwable $th) {
            DB::rollBack();
            return responder()
             ->status('failed')
             ->code(422)
             ->message('Something Went Wrong !');
         }
    }
    public function checkResetPasswordTime(Request $request, $resetcode)
    {
        try {
            $user_info =  User::where('pass_reset_code',$resetcode)->select('updated_at')->first();
            $data['totalDuration'] = $this->getDiffMinues($user_info->updated_at,Carbon::now());
            return responder()
                ->status('success')
                ->code(200)
                ->data($data)
                ->message('Data Available');
        } catch (\Throwable $th) {
            return responder()
                ->status('failed')
                ->code(422)
                ->message('Your url is wrong');
        }
    }
    public function resetPassword(Request $request, $resetcode)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6',
        ]);
        try {          
            $request_type =  $request->method();
            if($request_type == "POST")
            {
                $user_info =  User::where('pass_reset_code',$resetcode)->select('id','email','updated_at')->first();
                $totalDuration = $this->getDiffMinues($user_info->updated_at,Carbon::now());
                if(!empty($user_info) && $totalDuration < 5)
                {
                    DB::beginTransaction();
                    $new_password = Hash::make(request()->get('password'));
                    User::where('pass_reset_code', $resetcode)->update(['password' => $new_password,'pass_reset_code'=> ""]);
                    DB::commit();
                    return responder()
                    ->status('success')
                    ->code(200)
                    ->message('Password Reset Successfully');
                }
                else if($totalDuration > 5){
                    return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('This url is expired !');
                }
                else
                {
                    return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('Your url is wrong');
                }
            }
            else
            {
                return responder()
                    ->status('failed')
                    ->code(422)
                    ->message('Bad Method!');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return responder()
                ->status('failed')
                ->code(422)
                ->message('Something went wrong !');
        }  
    }
    public function sendOTPCreateAccount(Request $request){
        $request->validate([
            'email' => 'required|email:rfc,dns|max:60',
        ]);
        try {           
            if(isset($request->email) && !empty($request->email))
            {
                if(User::where(['email'=> $request->email,'status'=> 1])->exists()){
                    return responder()
                        ->status('failed')
                        ->code(404)
                        ->message('Your Account Already Exists !');
                }
                DB::beginTransaction();           
                $email = $request->email;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return responder()
                        ->status('failed')
                        ->code(404)
                        ->message('Your email is invalid!');
                }
                $otp = User::generateOTPCode();
                $user = [
                    'name' => 'User',
                    'email' => $email,
                    'otp' => $otp
                ];
                UsersOtp::updateOrInsert(['email' => $email],['email' => $email,'otp' => $otp,'ip_address' => $request->ip()]);            
                Mail::to($email)->send(new \App\Mail\sendOTP($user));
                if(count(Mail::failures())>0){
                    DB::rollBack();
                    return responder()
                        ->status('failed')
                        ->code(404)
                        ->message('Something went wrong!. Try Agin');
                }
                $email_first_clause = substr($email,0,3);
                $email_last_clause = explode("@",$email)[1];
                $email_address = $email_first_clause.'*****@'.$email_last_clause;
                DB::commit();
            return responder()
                ->status('success')
                ->code(200)
                ->message('We sent your OTP verification code to: '.$email_address);

            }
            else
            {
            return responder()
                ->status('failed')
                ->code(404)
                ->message('Your email does not match.');
            }
         } catch (\Throwable $th) {
            DB::rollBack();
            return responder()
             ->status('failed')
             ->code(422)
             ->message('Something Went Wrong !');
         }
    }

    public function verifyOTPCreateAccount(Request $request){
        $request->validate([
            'email' => 'required|email:rfc,dns|max:60',
            'otp' => 'required|numeric|min:6'
        ]);

        try {
            $condition = [
                'email' => $request->email,
                'otp' => $request->otp,
            ];
            if(UsersOtp::where($condition)->exists()){
                $account_set_code = User::generateResetPasswordCode();
                UsersOtp::where($condition)->update(['account_set_code' => $account_set_code]);             
                return responder()
                    ->status('success')
                    ->code(200)
                    ->message('OTP Verified. Redirecting to Set Password Link')
                    ->data($account_set_code);
            }
            else{
                return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('Your OTP Code is Invalid');
            }
        } catch (\Throwable $th) {
            return responder()
                    ->status('failed')
                    ->code(422)
                    ->message('Something went wrong!');
        }
    }

    public function setPassword(Request $request, $setAccountCode){
        $request->validate([
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6',
        ]);
        try {          
            $request_type =  $request->method();
            if($request_type == "POST") {
                if(UsersOtp::where('account_set_code',$setAccountCode)->select('id','email')->exists())
                {
                    $user_info =  UsersOtp::where('account_set_code',$setAccountCode)->select('id','email')->first();
                    $new_password = Hash::make(request()->get('password'));
                    $identify = [
                        'email'=> $user_info->email,
                    ];
                   $code =  $this->generateCode('User','U-');
                    $data = [
                        'email'=> $user_info->email,
                        'password' => $new_password,
                        'user_code' => $code,
                        'ip_address' => $request->ip()
                    ];
                    DB::beginTransaction();
                    User::updateOrInsert($identify, $data);
                    $user = [
                        'email' => $user_info->email,
                        'name'     => 'User',
                        'url'      => ""
                    ];
                    Mail::to($user_info->email)->send(new \App\Mail\CreateAccountSuccessMail($user));
                    if(Mail::failures()){
                        DB::rollBack();
                        return responder()
                            ->status('failed')
                            ->code(404)
                            ->message('Your email is invalid!');
                    }   
                    UsersOtp::where('email', $user_info->email)->delete();                
                    DB::commit();
                    return responder()
                    ->status('success')
                    ->code(200)
                    ->message('Password set Successfully');
                }
                else
                {
                    return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('Your url is wrong');
                }
            }
            else
                {
                    return responder()
                    ->status('failed')
                    ->code(422)
                    ->message('This Method is not Supported!');
                }
        } catch (Exception $ex) {
            DB::rollBack();
            return responder()
                ->status('failed')
                ->code(422)
                ->message('Something went wrong !'.$ex->getMessage());
        }  
    }

    // ======================== After Login ===========================

    public function userProfileUpdate(Request $request){
        $request->validate([
            'email' => 'required|email:rfc,dns|max:60'
        ]);
        try {
        $update = [
            'name' => $request->name,
            'mobile' => $request->mobile
        ];
        User::where('email',$request->email)->update($update);
        return responder()
                    ->status('success')
                    ->code(200)
                    ->message('Profile Updated Successfully');
       } catch (\Throwable $th) {
        return responder()
                    ->status('failed')
                    ->code(422)
                    ->message('Something went wrong !');
       }
    }

    
    public function getTaskItem(){
        try {
            $data = Task::whereStatus(1)->where('user_id', Auth::guard()->user()->id)
                    ->select('id','user_id','name','task_code','progress','note','start_date','end_date','key_points','description')
                    ->latest()
                    ->paginate(20);
            if($data->count() > 0){
                return responder()
                ->status('success')
                ->code(200)
                ->data($data)
                ->message('Data Available');
            }
            else{
                return responder()
                ->status('success')
                ->code(200)
                ->message('Data Not Available');
            }
        } catch (\Throwable $th) {
            return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('Data Not Available');
        }
    }

    public function getAllTaskItem(){
        try {
            $data['all'] = Task::whereStatus(1)->where('user_id', Auth::guard()->user()->id)
                    ->select('id','user_id','name','task_code','progress','note','start_date','end_date','key_points','description')
                    ->latest()
                    ->get();
            $data['pending'] = $data['all']->where('progress',1);
            $data['progress'] = $data['all']->where('progress',2);
            $data['testing'] = $data['all']->where('progress',3);
            $data['done'] = $data['all']->where('progress',4);
            if($data['all']->count() > 0){
                return responder()
                ->status('success')
                ->code(200)
                ->data($data)
                ->message('Data Available');
            }
            else{
                return responder()
                ->status('success')
                ->code(200)
                ->message('Data Not Available');
            }
        } catch (\Throwable $th) {
            return responder()
                    ->status('failed')
                    ->code(404)
                    ->message('Data Not Available'.$th->getMessage());
        }
    }

    public function saveTaskItem(Request $request){
        $request->validate([
            'name' => 'required|max:100',
            'key_points' => 'required'
        ]);
        try {
            $task = new Task();
            $task->user_id = Auth::guard()->user()->id;
            $task->name = $request->name;
            $task->key_points = $request->key_points;
            $task->description = $request->description;
            $task->start_date = $request->start_date;
            $task->end_date = $request->end_date;
            $task->note = $request->note;
            $task->task_code = $this->generateCode('Task','T-');
            $task->save();
            return responder()
                ->status('success')
                ->code(200)
                ->message('Successfully Inserted');
        } catch (\Throwable $th) {
            return responder()
                ->status('error')
                ->code(422)
                ->message('Something went wrong!');
        }
    }
    
    public function updateTaskItem(Request $request){
        $request->validate([
            'name' => 'required|max:100',
            'key_points' => 'required'
        ]);
        try {
            $task = Task::find($request->id);
            $task->name = $request->name;
            $task->key_points = $request->key_points;
            $task->description = $request->description;
            $task->start_date = $request->start_date;
            $task->end_date = $request->end_date;
            $task->note = $request->note;
            $task->save();
            return responder()
                ->status('success')
                ->code(200)
                ->message('Successfully Updated');
        } catch (\Throwable $th) {
            return responder()
                ->status('error')
                ->code(422)
                ->message('Something went wrong!');
        }
    }

    public function deleteTaskItem(Request $request){
        $request->validate([
            'id' =>'required'
        ]);
        try {
            Task::where('id',$request->id)->delete();
            return responder()
                ->status('success')
                ->code(200)
                ->message('Successfully Deleted');
        } catch (\Throwable $th) {
            return responder()
                ->status('failed')
                ->code(422)
                ->message('Something Went Wrong !');
        }
    }

    public function updateTaskList(Request $request){
        try {

            if(count($request->pending) > 0){
                foreach($request->pending as $item){
                    Task::where('id',$item['id'])->update(['progress' => 1]);
                }
            }
            if(count($request->progress) > 0){
                foreach($request->progress as $item){
                    Task::where('id',$item['id'])->update(['progress' => 2]);
                }
            }
            if(count($request->testing) > 0){
                foreach($request->testing as $item){
                    Task::where('id',$item['id'])->update(['progress' => 3]);
                }
            }
            if(count($request->done) > 0){
                foreach($request->done as $item){
                    Task::where('id',$item['id'])->update(['progress' => 4]);
                }
            }
            return responder()
                ->status('success')
                ->code(200)
                ->message('Successfully Updated');
        } catch (\Throwable $th) {
            return responder()
            ->status('failed')
            ->code(422)
            ->message('Updated Failed'.$th->getMessage());
        }
    }
}
