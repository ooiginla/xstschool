<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function register(Request $request)
    {
        return view('voteregister', );
    }

    public function postregister(Request $request)
    {
        $request->validate([
            'uniquecode' => 'required'
        ]);

        $uniquecode = $request->input('uniquecode');

        $user = User::where('code',$uniquecode)->first();

        // Check User Exist
        if(empty($user)){
            return redirect()->back()->withErrors(['msg' => 'Invalid Code, If you try 3 more times, your IP will be blocked']);
        }

        // Check User has voted
        $vote = Vote::where('user_id', $user->id)->first();

        if(!empty($vote)){
            return redirect()->back()->withErrors(['msg' => 'You have already casted your vote as: '. $user->firstname. " ".$user->lastname]);
        }

        return redirect()->route('vote.vote', ['code' => $user->code]);
    }


    public function vote(Request $request, $code)
    {

        $user = User::where('code',$code)->first();

        if(empty($user)){
            return view('home');
        }

        return view('vote', ['code' => $code,'user' => $user]);
    }

    public function postVote(Request $request, $code)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $data = $request->all();

        $code = $request->input('code');
        $user = User::where('code',$code)->first();

        // Check User Exist
        if(empty($user)){
            return redirect()->back()->withErrors(['msg' => 'Invalid Code, If you try 3 more times, your IP will be blocked']);
        }

        // Check User has voted
        $vote = Vote::where('user_id', $user->id)->first();

        if(!empty($vote)){
            return redirect()->back()->withErrors(['msg' => 'You have already casted your vote as: '. $user->firstname. " ".$user->lastname]);
        }

        $vote = new Vote;
        $vote->ip_address = $request->ip(); 
        $vote->browser = $request->header('User-Agent');
        $vote->user_id = $user->id;
        $vote->code = $code;
        $vote->chairman = $data['chairman'] ?? NULL;
        $vote->vicechairman = $data['vicechairman'] ?? NULL;
        $vote->secretary = $data['secretary'] ?? NULL;
        $vote->ass_secretary = $data['ass_secretary'] ?? NULL;
        $vote->treasurer = $data['treasurer'] ?? NULL;
        $vote->finsec = $data['finsec'] ?? NULL;
        $vote->pro = $data['pro'] ?? NULL;
        $vote->legal = $data['legal'] ?? NULL;
        $vote->welfare = $data['welfare'] ?? NULL;
        $vote->save();

        return redirect()->route('vote.register')->with('smsg', 'Vote Successfully Casted, Thank you!!!');
    }

    public function result(Request $request)
    {
        $chairman = Vote::whereNotNull('chairman')->select('chairman', DB::raw('count(*) as total'))->groupBy('chairman')->get()->toArray();
        $vicechairman = Vote::whereNotNull('vicechairman')->select('vicechairman', DB::raw('count(*) as total'))->groupBy('vicechairman')->get()->toArray();
        $secretary = Vote::whereNotNull('secretary')->select('secretary', DB::raw('count(*) as total'))->groupBy('secretary')->get()->toArray();
        $ass_secretary = Vote::whereNotNull('ass_secretary')->select('ass_secretary', DB::raw('count(*) as total'))->groupBy('ass_secretary')->get()->toArray();
        $treasurer = Vote::whereNotNull('treasurer')->select('treasurer', DB::raw('count(*) as total'))->groupBy('treasurer')->get()->toArray();
        $finsec = Vote::whereNotNull('finsec')->select('finsec', DB::raw('count(*) as total'))->groupBy('finsec')->get()->toArray();
        $pro = Vote::whereNotNull('pro')->select('pro', DB::raw('count(*) as total'))->groupBy('pro')->get()->toArray();
        $legal = Vote::whereNotNull('legal')->select('legal', DB::raw('count(*) as total'))->groupBy('legal')->get()->toArray();
        $welfare = Vote::whereNotNull('welfare')->select('welfare', DB::raw('count(*) as total'))->groupBy('welfare')->get()->toArray();

        return view('result', compact('chairman','vicechairman','secretary','ass_secretary','treasurer',
        'finsec','pro','legal','welfare'));
    }
}
