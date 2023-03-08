<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use ApiResponser;
    
    public function getDiffMinues($start_date_time = null,$end_date_time){
        $startTime = Carbon::parse($start_date_time);
        $finishTime = Carbon::parse($end_date_time);
        return $finishTime->diffInMinutes($startTime);
    }
    public function generateCode($model, $prefix = '')
    {
        $code = "000001";
        $model = '\\App\\Models\\' . $model;
        $num_rows = $model::withTrashed()->count();
        if ($num_rows != 0) {
            $newCode = $num_rows + 1;
            $zeros = ['0', '00', '000', '0000', '00000'];
            $code = strlen($newCode) > count($zeros) ? $newCode : $zeros[count($zeros) - strlen($newCode)] . $newCode;
        }
        return $prefix . $code;
    }
    public function scopeWhereStatus($query,$status){
        return $this->where('status',$status);
    }
}

