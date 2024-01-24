<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class WelcomeController extends Controller
{
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
