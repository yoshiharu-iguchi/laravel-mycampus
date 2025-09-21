<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TransportRequestStatus;


class TransportRequest extends Model
{
    use HasFactory;
    protected $fillable=[
        'student_id','facility_id','from_station_name','to_station_name','travel_date','dep_time','arr_time','fare_yen','seat_fee_yen','total_yen','search_url','status','approved_by','approved_at','admin_note',];

    protected $casts = [
        'travel_date' => 'date','approved_at' => 'datetime','fare_yen' => 'integer','seat_fee_yen' => 'integer','total_yen' => 'integer','status' => TransportRequestStatus::class,];
    
    public function student()
     { 
        return $this->belongsTo(Student::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
    
    public function approver()
    {
        return $this->belongsTo(Admin::class,'approved_by');
    }
}
