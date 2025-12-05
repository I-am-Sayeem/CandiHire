<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';
    protected $primaryKey = 'QuestionID';
    public $timestamps = true;

    protected $fillable = [
        'ExamID','QuestionType','QuestionText','QuestionOrder','Points',
        'Difficulty','Category','Tags'
    ];

    public function exam() {
        return $this->belongsTo(Exam::class, 'ExamID');
    }

    public function options() {
        return $this->hasMany(ExamQuestionOption::class, 'QuestionID');
    }
}
