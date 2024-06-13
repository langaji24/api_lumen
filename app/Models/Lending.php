<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lending extends Model
{
    use SoftDeletes;
    protected $fillable = [ "date_time", "name", "user_id", "notes", "total_stuff", "stuff_id"];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function stuff(){
        return $this->belongsTo(Stuff::class);
    }
    public function restoration(){
        return $this->hasOne(Restoration::class);
    }
}

