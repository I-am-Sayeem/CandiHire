<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'MessageID';
    public $timestamps = true;

    protected $fillable = [
        'ConversationID','SenderID','SenderType',
        'MessageText','IsRead','AttachmentUrl'
    ];

    public function conversation() {
        return $this->belongsTo(Conversation::class, 'ConversationID');
    }
}
