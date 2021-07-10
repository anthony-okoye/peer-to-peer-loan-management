<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as GuzzleClient;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration loanrequest.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'mobile' => 'required|numeric|digits:11',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }
    
    public static function generate_string($length=12) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $length);
    }
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $date = new DateTime();
        $http_method = 'post';
        $path = '/v1/ewallets/ewallet_f769e45522b2f19a4f9f38a5cb632f52/contacts';
        $salt = static::generate_string();
        $timestamp = $date->getTimestamp();
        $body = '{
            "first_name" : '.$data['name'].',
            "phone_number" : '.$data['mobile'].',
            "email" : '.$data['email'].',
            "contact_type" : "personal"
        }';
        $body_string = json_encode($body);
        echo $body_string;
        
        $access_key = '17AFE8F7F9ACAA9F741B';
        $secret_key = '44acd516aca90d01b68ee7cad357ac84bdb188a9432db57bf94fe62d48d4fb8659d38639597118a2';
        $sig_string = $http_method.$path.$salt.$timestamp.$access_key.$secret_key.$body;
        $hash_sig_string = hash_hmac("sha256", $sig_string, $secret_key);
        $signature = base64_encode($hash_sig_string);
        $headers = [ 
        'Content-Type' => 'application/json',
        //'secret_key' => $secret_key,    
        'access_key' => $access_key,
        'signature' => $signature,    
        'salt' => $salt,
        'timestamp' => $timestamp,   
        ];
        $headerz = json_encode($headers);
        echo $headerz;
        $contact_type = 'personal';
        
        echo $body;
        //$client = new GuzzleClient(['base_uri' =>'https://sandboxapi.rapyd.net']);
        $client = new GuzzleClient([        
        'headers' => $headers,
        //'body' => $body,
        ]);    
        $url = 'https://sandboxapi.rapyd.net/v1/ewallets/ewallet_f769e45522b2f19a4f9f38a5cb632f52/contacts';
        //$response = $detail->getStatusCode();
        try {
        $response = $client->request('POST',$url,[
                'allow_redirects' => true,
                'timeout' => 2000,
                'http_errors' => true,
            ],['body' => $body]);
            $body = $response->status();
            echo $body;
           // $arr_body = json_decode($body);
            //print_r($arr_body);
        }catch (RequestException $e) {

        // Catch all 4XX errors 
    
        // To catch exactly error 400 use 
        if ($e->hasResponse()){
        if ($e->getResponse()->getStatusCode() == '400') {
                echo "Got response 400";
        }
        }

        // You can check for whatever error status code you need 
    
        }
        //$response = $r->getStatusCode();
        //$response = $r->getBody()->getContents();
        //echo $response;
        $user = User::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        //$user->wallet_unique_id = HomeController::generate_random_string() . $user->id;
        $user->save();
        //$this->createWallet();
        return $user;
    }
    
    /*public function createWallet(array $data) {
        $create = HomeController::wallet_headers()->post('https://sandboxapi.rapyd.net/v1/ewallets/ewallet_f769e45522b2f19a4f9f38a5cb632f52/contacts', [
            'first_name' => $data['name'],
            'phone_number' => $data['mobile'],
            'email' => $data['email'],
            "contact_type" => "personal",
        ]);
    } */
}
