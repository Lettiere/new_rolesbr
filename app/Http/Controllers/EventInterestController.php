<?php

namespace App\Http\Controllers;

use App\Models\EventInterest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventInterestController extends Controller
{
    public function toggleGoing($eventoId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $event = Event::findOrFail($eventoId);
        $userId = Auth::id();
        $row = EventInterest::where('evento_id',$event->evento_id)->where('user_id',$userId)->where('type','going')->first();
        if ($row) {
            $row->delete();
        } else {
            EventInterest::create(['evento_id'=>$event->evento_id,'user_id'=>$userId,'type'=>'going']);
        }
        return back();
    }
}
