<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HobbyMapping extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'hobby_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function hobby()
    {
        return $this->belongsTo(Hobby::class, 'hobby_id');
    }
}
