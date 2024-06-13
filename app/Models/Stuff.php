<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    use SoftDeletes; // digunakan hanya untuk table yang menggunakan fitur softdeletes
    protected $fillable = ["name","category"];
    // mendifinisikan relasi
    // table yang berperan sebagai primary key : hasOne/hasMany/ ...
    // table yang berperan sebagai foreign key : belongsTO
    // nama function disarankan menggunakan aturan berikut: 
    // 1. one to one : nama model yang tehubung dengan versi tunggal
    // 2. one to many :nama model yang terhubung versi jamak ( untuk foreign key nya)

    public function stuffStock(){
        return $this->hasOne(StuffStock::class);
    }

    public function inboundStuffs(){
        return $this->hasMany(InboundStuff::class);
    }

    public function lending(){
        return $this->hasMany(Lending::class);
    }
}


