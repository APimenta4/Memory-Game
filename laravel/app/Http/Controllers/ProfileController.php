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

    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();

        // Validate incoming data
        $validatedData = $request->validated();

        //dd($validatedData);

        // Hash the password if it's provided
        if (isset($validatedData['password']) && $validatedData['password']) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

         // Update the user profile
         if (isset($validatedData['photo_filename'])) {
            $user->photo_filename = $validatedData['photo_filename'];
        }

        // Update user only with the provided fields (validated data)
        $user->update($validatedData);

        $user->save();

        // Return updated user information as a resource
        return new UserResource($user); // Return updated user, not the request's user
    }

}
