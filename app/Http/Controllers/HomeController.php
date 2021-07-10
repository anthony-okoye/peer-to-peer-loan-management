<?php

namespace App\Http\Controllers;

use App\cashCollections;
use App\loanRequests;
use App\Notifications\DeadlineReminder;
use App\Transactions;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function generate_string($length=12) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $length);
    }
    
    public static function wallet_headers() {
        $date = new DateTime();
        $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'access_key' => '17AFE8F7F9ACAA9F741B',
        'signature' => generate_string(),    
        'salt' => generate_string(),
        'timestamp' => $date->getTimestamp()
        ]);
    }
    
    /**
    *
    *
    * Generate UUID
    */
    public static function generate_random_string() {
        $characters = '0123ABCDEFGHI45jklmnopqrstuvwxyzabcdefghi6789JKLMNOPQRSTUVWXYZ';
        $random_string = '';
        for ($i = 0; $i < 5; $i++) {
            $random_string .= $characters[rand(0, (strlen($characters) - 1))];
        }
        return $random_string;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $Debit = 0;
        $Credit = 0;
        $paid = 0;
        $TotalPayback = 0;
        $names[] = ""; 

        $loans = loanRequests::where([
            ['user_id', Auth::user()->id],
            ['status', 'complete'],
            ['paymentStatus', NULL]
        ])->get();

        $date = new Carbon;

        foreach ($loans as $loan) {
            $payments = $loan->payments();
            foreach ($payments as $pay) {
                $paid += $pay->amount;
            }
            $Debit += $loan->total;

            if ($paid > $Debit) {
                $loan->paymentStatus = 'ended';
                $loan->save();
            }

            if( date('Y-m-d')== date('Y-m-d',(strtotime ( '-1 day' , strtotime ($loan->Getdeadline()->deadline ) ) ))){

                User::find(Auth::user()->id)->notify(new DeadlineReminder());
            }

        }

        $contribution = cashCollections::where([
            ['user_id', Auth::user()->id],
            ['percent', '!=', 'NULL'],
        ])->get();

        foreach ($contribution as $credit) {
            $Credit += ((loanRequests::where('id', $credit->loan_id)->first()->total) * ($credit->percent)) / 100;


            $loanS = loanRequests::where([
                ['id', $credit->loan_id],
                ['status', 'complete'],
                ['paymentStatus', NULL]
            ])->get();

            foreach ($loanS as $Loan) {
                $paybacks = $Loan->isPaid();
                if ($paybacks->count() > 0) {
                    foreach ($paybacks as $pays) {
                        if($pays['to'] == Auth::user()->id)
                        {
                        $TotalPayback += $pays->amount;
                        }
                    }
                }

            }
        }


            foreach ($contribution as $name) {
                $names[] = User::where('id', loanRequests::where('id', $name->loan_id)->first()->user_id)->first()->name;

            }

            //return view('Dashboard.mydashboard', compact('Debit', 'paid', 'loans', 'Credit', 'contribution', 'TotalPayback'));

            return view('Dashboard.mydashboard', compact('Debit', 'paid', 'loans', 'Credit', 'contribution', 'names', 'TotalPayback'));
    }


    Public function Clear($id=null)
    {
        if($id) {
            Auth::user()->notifications()->where('id', $id)->first()->delete();
        }
        else
            Auth::user()->notifications()->delete();

        return redirect()->back();
    }


}