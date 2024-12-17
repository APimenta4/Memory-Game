<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\UserListRequest;

class UserController extends Controller
{

    public function index(UserListRequest $request) 
    {

    

        $users = User::all();



        return UserResource::collection($users);    
    }

    public function store(Request $request)
    {
       
    }


    public function update(Request $request, string $id)
    {
        
    }

    public function destroy(string $id)
    {
     
    }

    public function me(Request $request) {
        return new UserResource($request->user());
    }
}
