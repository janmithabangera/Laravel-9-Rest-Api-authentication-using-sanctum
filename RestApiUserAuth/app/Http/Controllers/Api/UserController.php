<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\API\BaseController;
use App\Http\Requests\Api\RegisterUserRequest;

class UserController extends BaseController
{
    public function register(RegisterUserRequest $request)
    {
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        User::create($input);
        return $this->sendResponse([], 'User register successfully.');
    }


    public function login(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('auth_token')->plainTextToken;
            $success['user'] =  $user;
            if (!isset($user->role)) {
                $success['roles'] = [Config::get('constants.roles.editor'), Config::get('constants.roles.writer')];
            }
            return $this->sendResponse($success, 'User login successfully.');
        }
        return $this->sendError('Unauthorised.', ['error' => 'Incorrect credentials'],401);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'User logged out.');
    }
}
