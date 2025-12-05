<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $table = 'exam_answers';
    protected $primaryKey = 'AnswerID';
    public $timestamps = true;

    protected $fillable = [
        'AttemptID','QuestionID','AnswerText','SelectedOptionID',
        'IsCorrect','PointsEarned','TimeSpent'
    ];

    public function attempt() {
        return $this->belongsTo(ExamAttempt::class, 'AttemptID');
    }

    public function question() {
        return $this->belongsTo(ExamQuestion::class, 'QuestionID');
    }

    public function selectedOption() {
        return $this->belongsTo(ExamQuestionOption::class, 'SelectedOptionID');
    }
}
