<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
         ]);


        //return $token = $user->createToken('token-auth')->plainTextToken;
        return $user->createToken('remember_token', ['server:update'])->plainTextToken;
        $respon = [
            'status' => 'success',
            'msg' => 'Success',
            'errors' => null,
            'content' => [
                'status_code' => 200,
                'remember_token' => $user,
                'token_type' => 'Bearer',
            ]
            ];
        return response()
            ->json($respon, 200);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        $respon = [
            'status' => 'success',
            'msg' => 'Login successfully',
            'errors' => null,
            'content' => [
                'status_code' => 200,
                'remember_token' => $token,
                'token_type' => 'Bearer',
            ]
            ];
        return response()
            ->json($respon, 200);
    }

    // //$respon = [
    //     'status' => 'success',
    //     'msg' => 'Login successfully',
    //     'errors' => null,
    //     'content' => [
    //         'status_code' => 200,
    //         'access_token' => $tokenResult,
    //         'token_type' => 'Bearer',
    //     ]
    // ];
    // method for user logout and delete token
    public function logout()
    {
        $user = User::where('tokens')->firstOrFail();
        $user->tokens()->delete();
        //$user()->token()where ('id', $token) -> delete ();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }
}
