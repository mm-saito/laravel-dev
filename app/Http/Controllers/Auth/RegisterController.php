<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Profile;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
    protected $redirectTo = '/users';

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
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'dept_id' => ['required', 'integer'],
            'job_id' => ['required', 'integer'],
            // 'is_admin' => ['required', 'boolean'],
        ]);
    }

    /*
     * オーバーライドして部署と職種を渡す
     *
     */
    public function showRegistrationForm()
    {
        $jobs = User::getArraySelectBox('App\Job');
        $depts = User::getArraySelectBox('App\Dept');
        return view('auth.register', ['depts' => $depts, 'jobs' => $jobs]);;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user =  User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'dept_id' => $data['dept_id'],
            'job_id' => $data['job_id'],
            'is_admin' => 0,
        ]);

        // profileに空レコード追加
        Profile::create(['user_id' => $user->id,'name' => '名無し']);
        return $user;
    }
}
