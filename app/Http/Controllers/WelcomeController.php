<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;
use App\Services\Sms;

class WelcomeController extends Controller
{
    
    public function testEmail(Request $request)
    {
        $sms = new Sms;
        $user = User::find(1);
        $message = "You have been successfully screened. Your voting code is: ".$user->code;

        $sms->sendMessage($user->phone, $message);
        
        /*
        Mail::to('iginla.omotayo@gmail.com')->send(new UserRegistered(User::find(1)));
        Mail::to('hi@jekayode.com')->send(new UserRegistered(User::find(1)));
        */

        dd('sms sent');
    }
    
    public function home(Request $request)
    {
        return view('home');
    }

    public function getRegistration(Request $request)
    {
        return view('registration');
    }

    public function postRegistration(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'phone' => 'required|unique:users,phone',
            'email' => 'required|unique:users,email',
            'location' => 'required'
        ]);

        $data = $request->all();

        $user = new User;

        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->gender = $data['gender'];
        $user->location = $data['location'];
        $user->code = $this->generateRandomString(6);
        $user->save();

        session()->flash('smsg','Congratulations, You have successfully registered, awaiting committee screening to check if you are a valid member of this set');

        return view('registration');
    }

    public function generateRandomString($length = 6) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
