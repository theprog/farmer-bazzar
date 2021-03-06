<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Http\Request;

class AuthenticateController extends Controller {



    public function __construct()
    {
       $this->middleware('jwt.auth', ['except' => ['authenticate', 'register']]);
    }
    
	
    /*
    * Register function
    */
    public function register(Request $request){
        $rules = [
            'name' => 'required',
            'phone_no' => 'required|unique:users',
            'house' => 'required',
            'village' => 'required',
            'post_office' => 'required',
            'post_code' => 'required|numeric',
            'thana' => 'required',
            'district' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ];

        $data = [
            'name' => $request->name,
            'phone_no' => $request->phone_no,
            'house' => $request->house,
            'village' => $request->village,
            'post_office' => $request->post_office,
            'post_code' => $request->post_code,
            'thana' => $request->thana,
            'district' => $request->district,
            'password' => $request->password,
            'password_confirmation' => $request->confirm_password
        ];



        $valid = \Validator::make($data, $rules);

        if($valid->fails()) return response()->json(['errors' => $valid->errors()->all()], 400);
        else {
            $user = new \App\User;
            $user->name = $request->name;
            $user->phone_no = $request->phone_no;
            $user->house = $request->house;
            $user->village = $request->village;
            $user->post_office = $request->post_office;
            $user->post_code = $request->post_code;
            $user->thana = $request->thana;
            $user->district = $request->district;
            $user->password = \Hash::make($request->password);
            $user->save();
            return response()->json(['success' => 'success'], 200);
        }
    }


	public function authenticate(Request $request)
    {
        $credentials = $request->only('phone_no', 'password');
        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    public function userData(Request $request)
    {
       $user = \Auth::user();
       return $user;
    }


    public function changeProfilePicture(Request $request){
        $validator = [
            'file' => 'required|mimes:jpeg,bmp,png'
        ];

        $data = [
            'file' => $request->file('file')
        ];

        $valid = \Validator::make($data, $validator);
        if($valid->fails()) return response()->json(['errors' => $valid->errors()->all()], 400);

        $user = \App\User::find(\Auth::user()->id);

        if (\File::exists($request->file('file')))
        {   
            $image = \Input::file('file');
            $filename = date('Y-m-d-H:i:s') .".". $image->getClientOriginalExtension();
            
            $request->file('file')->move(public_path().'/pictures/', $filename);
            $user->picture = $filename;
            $user->save();
        }

        return $user;
    }
}
