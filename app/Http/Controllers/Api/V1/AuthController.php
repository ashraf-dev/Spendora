<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'language' => $data['language'] ?? 'en',
        ]);

        $token = $user->createToken('flutter')->accessToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], __('api.auth.registered'), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::guard('web')->attempt($credentials)) {
            return ApiResponse::error(__('api.auth.invalid_credentials'), 422);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();
        Auth::guard('web')->logout();

        $token = $user->createToken('flutter')->accessToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], __('api.auth.login_success'));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        if ($token !== null && method_exists($token, 'revoke')) {
            $token->revoke();
        }

        Auth::guard('api')->forgetUser();

        return ApiResponse::success(null, __('api.auth.logout_success'));
    }

    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            __('api.profile.retrieved')
        );
    }
}
