<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestionOption extends Model
{
    protected $table = 'exam_question_options';
    protected $primaryKey = 'OptionID';
    public $timestamps = false;

    protected $fillable = [
        'QuestionID','OptionText','IsCorrect','OptionOrder'
    ];

    public function question() {
        return $this->belongsTo(ExamQuestion::class, 'QuestionID');
    }
}
