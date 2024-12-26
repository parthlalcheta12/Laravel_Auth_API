<?php

namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Log;

class ValidationHelper {

    public static function registerValdation($request, $errorKey) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email_with_domain|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            
            'email.required' => 'The email field is required.',
            'email.string' => 'The email must be a string.',
            'email.email_with_domain' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 6 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);
        
        $user = User::where('email', $request->email)->first();

        if($user) {
            return response()->json([
                'status' => 400,
                'message' => 'User already exists',
                'user' => null,
                'error' => $errorKey
            ]);
        }
        
        if($validator->fails()){
            $result = json_decode($validator->errors(), true);
            $message = '';
            foreach ($result as $value) {
                $message = implode(', ', $value);
                break;
            }
            Log::error($message, ['error_key' => $errorKey]);
            return response()->json([
                'status' => 400,
                'success' => false, 
                'message' => $message, 
                'data' => null, 
                'error' => $errorKey
            ]);
        }
    }

    public static function loginValdation($request = null, $errorKey = null) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email_with_domain',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'The email field is required.',
            'email.email_with_domain' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 6 characters long.',
        ]);

        $user = User::where('email', $request->email)->first();

        if($validator->fails()){
            $result = json_decode($validator->errors(), true);
            $message = '';
            foreach ($result as $value) {
                $message = implode(', ', $value);
                break;
            }
            Log::error($message, ['error_key' => $errorKey]);
            return response()->json([
                'status' => 400,
                'success' => false, 
                'message' => $message, 
                'data' => null, 
                'error' => $errorKey
            ]);
        }
        
    }
}