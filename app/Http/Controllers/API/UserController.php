<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use JWTAuth;
use Validator;


class UserController extends Controller
{


    public $successStatus = 200;


    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
      $validator = Validator::make($request->all(), [
        'password' => 'required|string',
        'email' => 'required|string|email'
      ]);
      
      if ($validator->fails()) {
        return response()->json(["status" => "FAIL", "data" => $validator->errors()], 501);
      }
      
      $credentials = request()->only('email', 'password');
      try {
        $token = JWTAuth::attempt($credentials);
        if (!$token) {
          return response()->json(['error' => 'is invalid credentials'], 401);
        }
      } catch (JWTException $e) {
        return response()->json(['error' => 'Something Wrong'], 500);
      }
      return response()->json($token, 200);
    }


    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
      ]);


      if ($validator->fails()) {
          return response()->json(['error'=>$validator->errors()], 401);            
      }


      $input = $request->all();
      $input['password'] = bcrypt($input['password']);
      $user = User::create($input);
      $success['token'] =  $user->createToken('BibleApp')->accessToken;
      $success['name'] =  $user->name;


      return response()->json(['success'=>$success], $this->successStatus);
    }


    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
      $user = JWTAuth::toUser(JWTAuth::getToken());
      return response()->json(['success' => $user], $this->successStatus);
    }
}
