<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversations';
    protected $primaryKey = 'ConversationID';
    public $timestamps = true;

    protected $fillable = [
        'ParticipantOneID','ParticipantOneType',
        'ParticipantTwoID','ParticipantTwoType',
        'LastMessageID','LastUpdated'
    ];

    public function messages() {
        return $this->hasMany(Message::class, 'ConversationID');
    }

    public function lastMessage() {
        return $this->belongsTo(Message::class, 'LastMessageID');
    }
}
