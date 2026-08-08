<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateLanguageRequest;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UploadAvatarRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            __('api.profile.retrieved')
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return ApiResponse::success(
            new UserResource($user->fresh()),
            __('api.profile.updated')
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'password' => $request->validated('password'),
        ])->save();

        return ApiResponse::success(null, __('api.profile.password_updated'));
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->forceFill(['avatar' => $path])->save();

        return ApiResponse::success(
            new UserResource($user->fresh()),
            __('api.profile.avatar_updated')
        );
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->forceFill(['avatar' => null])->save();
        }

        return ApiResponse::success(
            new UserResource($user->fresh()),
            __('api.profile.avatar_deleted')
        );
    }

    public function updateLanguage(UpdateLanguageRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'language' => $request->validated('language'),
        ])->save();

        app()->setLocale($user->language);

        return ApiResponse::success(
            new UserResource($user->fresh()),
            __('api.profile.language_updated')
        );
    }
}
