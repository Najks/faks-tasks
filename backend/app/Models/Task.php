<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    use HasFactory;
    protected $table = 'tasks';
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'grade',
        'status_id',
        'subject_id',
        'user_id',
    ];

    protected $casts = [
        "due_date" => "date",
        "grade" => "double"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, "status_id");
    }
}
