<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    /**
     * 任務清單
     * 
     * 請求欄位：
     *  string name 名稱
     *  string email
     *  string password 密碼6碼
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'result' => 'ok',
            'ret' => [
                'user' => $user,
                'token' => $token
            ]
        ];
    }

    /**
     * 登入
     * 
     * 請求欄位：
     *  string email
     *  string password 密碼6碼
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw new \RuntimeException('Account or password error!');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'result' => 'ok',
            'ret' => [
                'user' => $user,
                'token' => $token
            ]
        ];
    }
}
