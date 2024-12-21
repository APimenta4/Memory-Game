<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\UserResource;


class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function changePhoto(Request $request)  //uploads the photo
    {
        // Validate the file input
        $validatedData = $request->validate([
            'photo' => 'required|image|max:2048', // Max size 2MB
        ]);

        // Store the file in a directory and get its name
        $path = $request->file('photo')->store('photos', 'public');

        // Return the filename (or path) to the client
        return response()->json(['photo' => $path], 201);
    }
    public function update(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'nickname' => 'sometimes|string|max:255|unique:users,nickname,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if (isset($validatedData['password']) && $validatedData['password']) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validatedData['photo_filename'] = basename($path);
        }

        try {
            $user->update($validatedData);
            return new UserResource($user);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $errorMessage = $e->getMessage();

                $errors = [];
                if (str_contains($errorMessage, 'users_email_unique')) {
                    $errors['email'] = ['The email has already been taken.'];
                }
                if (str_contains($errorMessage, 'users_nickname_unique')) {
                    $errors['nickname'] = ['The nickname has already been taken.'];
                }

                return response()->json(['errors' => $errors], 422);
            }

            throw $e;
        }
    }

}
