<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;
use Log;
use App\Helpers\ValidationHelper;


class AuthController extends Controller
{

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function registerUser(Request $request)
    {
        
        $errorKey = str()->random(5);
        Log::info("API => AuthAPIController => registerUser", ['error_key' => $errorKey]);

         // Validate register request
        $validation = ValidationHelper::registerValdation($request, $errorKey);

        if ($validation instanceof \Illuminate\Http\JsonResponse) { // if validation error
            return $validation;
        }

        // Database transaction start
        DB::beginTransaction();
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        if($user) {
            DB::commit();
            $token = JWTAuth::fromUser($user);
        }else{
            DB::rollBack();
        }
        if($token) {
           $message = 'User successfully registered'; 
           return response()->json([
               'status' => 200,
               'message' => $message,
               'user' => $user,
               'error' => null
           ]);
        }else{
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong',
                'user' => null,
                'error' => $errorKey
            ]);
        }    
    }

    /**
     * Authenticate a user and generate a JWT token.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user credentials.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the authentication status, message, token if successful, and any errors.
     */

    public function login(Request $request)
    {
        Log::info("API => AuthAPIController => login", ['request' => $request]);
        $errorKey = str()->random(5);

        $validation = ValidationHelper::loginValdation($request, $errorKey);// validate login request

        if ($validation instanceof \Illuminate\Http\JsonResponse) {
            return $validation;
        }
        // get credentials of user 
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid credentials',
                    'user' => null,
                    'error' => $errorKey
                ]);
            }
            // Get the authenticated user.
            $user = auth()->user();

            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            // check if the token is exist then send response
            if($token) {
                $message = 'User successfully logged in';
                JWTAuth::setToken($token)->toUser();
                return response()->json([
                    'status' => 200,
                    'message' => $message,
                    'token' => $token,
                    'error' => null
                ]);
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Something went wrong',
                    'user' => null,
                    'error' => $errorKey
                ]);
            }
            
        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Could not create token',
                'user' => null,
                'error' => $errorKey
            ]);
        }
    }

    /**
     * Retrieve the authenticated user's details using a JWT token.
     *
     * Logs the start of the get user process and generates a random error key.
     * Attempts to authenticate the user by parsing the JWT token.
     * If the token is invalid or the user is not found, returns a JSON response with an error message.
     * On success, returns a JSON response with the user's details.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing the status, message, user details if successful, and any errors.
     */

    public function getUser()
    {
        Log::info("API => AuthAPIController => getUser", [date('Y-m-d H:i:s')]);
        $errorKey = str()->random(5);

        try {
           $user = User::where('id', auth()->user()->id)->first();
           if($user){
               return response()->json([
                   'status' => 200,
                   'message' => 'User details fetched successfully',
                   'data' => $user,
                   'error' => null
               ]);
           }
        } catch (JWTException $e) {
            Log::error($e->getMessage(), ['error_key' => $errorKey]);
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong',
                'user' => null,
                'error' => $errorKey
            ]);
        }
    }
}
