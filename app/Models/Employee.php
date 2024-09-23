<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_no', 'category_id', 'profile_pic'];

    public function hobbies()
    {
        return $this->hasMany(HobbyMapping::class, 'employee_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
