<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController;
use Config;

class ProfileController extends BaseController
{
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'firstName' => 'required',
            'lastName' => 'required',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $profileExists = Profile::where('user_id', Auth::user()->id)->first();
        if (!isset($profileExists)) {
            $profile = new Profile();
            $profile->firstName = $request->firstName;
            $profile->lastName = $request->lastName;
            $profile->role = $request->role;
            $profile->user_id = Auth::user()->id;
            $profile->save();

            return $this->sendResponse($profile, 'Profile created successfully.');
        } else {
            return $this->sendError('Profile already exists', $profileExists,403);
        }
    }

    public function show(Request $request)
    {
        $profile = Profile::find($request->route('profile_id'));
        if (isset($profile)) {
            return $this->sendResponse($profile, 'Profile found.');
        }
        return $this->sendError('Profile not found.', [],404);
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'firstName' => 'required',
            'lastName' => 'required',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $profile = Profile::find($request->route('profile_id'));
        if (isset($profile)) {
            $profile->firstName = $request->firstName;
            $profile->lastName = $request->lastName;
            $profile->role = $request->role;
            $profile->save();
            return $this->sendResponse($profile, 'Profile updated successfully.');
        }
        return $this->sendError($profile, 'Profile update failed.');
    }
}
