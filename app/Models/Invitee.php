<?php

namespace App\Models;

use App\Helpers\IpHelper;
use App\Traits\HasMeta;
use App\Traits\HasUuid;
use App\Models\Contact;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Invitee extends Model
{
    use HasMeta, HasUuid, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    protected $table = 'meeting_invitees';
    protected static $logName = 'meeting';
    protected static $logFillable = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = [ 'updated_at'];
    protected $with = ['contact'];

    // Relations
    public function contact() : BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function meeting() : BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    // Booted
    public static function booted()
    {
    }

    protected static function ensureUpdatable() : void
    {
    }

    // Actions

    public function logInvitee() : self
    {
        $this->is_attendee = 1;
        
        $meta = $this->meta;
        $logs = $meta['logs'] ?? [];
        array_push($logs, array(
            'start' => now(),
            'ip' => IpHelper::getClientIp()
        ));

        $meta['logs'] = $logs;
        $this->meta = $meta;
        $this->save();

        return $this;
    }

    public function logError() : self
    {
        if (! request('error')) {
            return $this;
        }

        $this->is_attendee = 0;
        
        $meta = $this->meta;
        $logs = $meta['logs'] ?? [];
        array_push($logs, array(
            'error' => request('error'),
            'ip' => IpHelper::getClientIp()
        ));

        $meta['logs'] = $logs;
        $this->meta = $meta;
        $this->save();

        return $this;
    }

    // Filters

    public function scopeIsAttendee(Builder $query) : void
    {
        $query->whereIsAttendee(1);
    }
}
