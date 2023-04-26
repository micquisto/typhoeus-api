<?php
namespace Typhoeus\Api\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * [Description AuthController]
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * 
     * @return [type]
     */
    public function register(Request $request) {
        $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8',
                ]);

            $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]);
    }

    /**
     * @param Request $request
     * 
     * @return [type]
     */
    public function me(Request $request)
    {
        return $request->user();
    }
}




