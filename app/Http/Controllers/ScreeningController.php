<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\Sms;

class ScreeningController extends Controller
{

    public function getGeneralList(Request $request)
    {
        $users  = User::orderby('lastname', 'asc')->get();

        return view('generalscreening',['users' => $users]);
    }


    public function getList(Request $request)
    {
        $users  = User::orderby('status', 'asc')->get();

        return view('screening',['users' => $users]);
    }

    public function postList(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'status' => 'required'
        ]);

        $user_id = $request->input('user_id');
        $status = $request->input('status');

        $user = User::find($user_id);

        $user->status = ($status == "APPROVE") ? 1 : 0;
        $user->save();

        $sms = new Sms;

        if($status == 'APPROVE')
        {
            $status_msg = "approved";

            // Send SMS
            $message = "You have been successfully screened. Your voting code is: ".$user->code;
            $sms->sendMessage($user->phone, $message);

            // Send Email
            /*
            Mail::to($user->email)->send(new UserRegistered($user));
            */

            session()->flash('smsg', $user->firstname . ' '.$user->lastname .' has been successfully ' .$status_msg);

        }else{
            $status_msg = "rejected";
            
            // Send SMS
            $message = "You have been denied as a valid member to vote. See committee";
            $sms->sendMessage($user->phone, $message);

            
            // Send Email
            /*
            Mail::to($user->email)->send(new UserRegistered($user));
            */

            session()->flash('emsg', $user->firstname . ' '.$user->lastname .' has been ' .$status_msg);
        }

        return redirect()->route('screen.getList');
    }
}
