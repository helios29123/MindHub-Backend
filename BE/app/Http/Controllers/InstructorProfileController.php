<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\InstructorAccountUpdateRequest;
use App\Http\Requests\Instructor\InstructorExpertiseUpdateRequest;
use App\Http\Requests\Instructor\InstructorIntroductionUpdateRequest;
use App\Http\Resources\Instructor\InstructorAccountResource;
use App\Http\Resources\Instructor\InstructorProfileCompletionResource;
use App\Http\Resources\Instructor\InstructorProfileResource;
use App\Http\Resources\Instructor\InstructorProfileSectionResource;
use App\Services\Instructor\InstructorProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InstructorProfileController extends Controller
{
    public function __construct(
        private readonly InstructorProfileService $service
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $result = $this->service->getProfile($request->user());

        return ApiResponse::success(
            new InstructorProfileResource($result),
            'Lấy hồ sơ giảng viên thành công.'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'expertise' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'social_links' => 'nullable|array',
            'social_links.website' => 'nullable|string|url|max:255',
            'social_links.facebook' => 'nullable|string|url|max:255',
            'social_links.linkedin' => 'nullable|string|url|max:255',
            'social_links.youtube' => 'nullable|string|url|max:255',
        ]);

        $result = $this->service->updateProfile($request->user(), $validated);

        return ApiResponse::success(
            new InstructorProfileResource($result),
            'Cập nhật hồ sơ giảng viên thành công.'
        );
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'avatar.required' => 'Vui lòng chọn file ảnh đại diện.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải thuộc định dạng: JPG, PNG hoặc WEBP.',
            'avatar.max' => 'Dung lượng ảnh tối đa là 5MB.',
        ]);

        $avatarUrl = $this->service->uploadAvatar($request->user(), $request->file('avatar'));

        return ApiResponse::success([
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
        ], 'Tải ảnh đại diện thành công.');
    }

    public function selectAvatarPreset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preset_id' => 'required|string',
        ], [
            'preset_id.required' => 'Vui lòng chọn ảnh đại diện mẫu.',
        ]);

        $avatarUrl = $this->service->selectAvatarPreset($request->user(), $validated['preset_id']);

        return ApiResponse::success([
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
        ], 'Cập nhật ảnh đại diện mẫu thành công.');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $this->service->deleteAvatar($request->user());

        return ApiResponse::success([
            'avatar' => null,
            'avatar_url' => null,
        ], 'Đã xóa ảnh đại diện.');
    }

    public function getPreferences(Request $request): JsonResponse
    {
        $prefs = $this->service->getPreferences($request->user());

        return ApiResponse::success($prefs, 'Lấy cài đặt thông báo thành công.');
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'sms_alerts' => 'sometimes|boolean',
        ]);

        $prefs = $this->service->updatePreferences($request->user(), $validated);

        return ApiResponse::success($prefs, 'Cập nhật cài đặt thông báo thành công.');
    }

    public function sendPasswordOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có từ 8 ký tự trở lên.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
        ]);

        $data = $this->service->sendPasswordOtp($request->user(), $validated);

        return ApiResponse::success(
            $data,
            'Mã OTP đã được gửi đến email của bạn.'
        );
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'sometimes|required|string|min:8',
            'new_password' => 'sometimes|required|string|min:8',
            'confirm_password' => 'nullable|string',
            'password_confirmation' => 'nullable|string',
            'otp' => 'required|string|size:6',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'otp.required' => 'Vui lòng nhập mã OTP 6 chữ số.',
            'otp.size' => 'Mã OTP phải có đúng 6 chữ số.',
        ]);

        $this->service->changePassword($request->user(), $validated);

        return ApiResponse::success(null, 'Đổi mật khẩu thành công!');
    }

    public function getSessions(Request $request): JsonResponse
    {
        $sessions = $this->service->getSessions($request->user());

        return ApiResponse::success($sessions, 'Lấy danh sách phiên đăng nhập thành công.');
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        $this->service->revokeOtherSessions($request->user());

        return ApiResponse::success(null, 'Đã đăng xuất các phiên đăng nhập khác.');
    }

    public function revokeSession(Request $request, string $id): JsonResponse
    {
        $this->service->revokeSession($request->user(), $id);

        return ApiResponse::success(null, 'Đã thu hồi phiên đăng nhập.');
    }

    public function getPrivacy(Request $request): JsonResponse
    {
        $privacy = $this->service->getPrivacy($request->user());

        return ApiResponse::success($privacy, 'Lấy tùy chọn quyền riêng tư thành công.');
    }

    public function updatePrivacy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_visibility' => 'sometimes|required|string|in:public,students_only,private',
            'show_email' => 'sometimes|required|boolean',
            'show_phone' => 'sometimes|required|boolean',
            'show_social_links' => 'sometimes|required|boolean',
            'allow_messages' => 'sometimes|boolean',
        ], [
            'profile_visibility.in' => 'Chế độ hiển thị không hợp lệ. Phải là public, students_only hoặc private.',
            'show_email.boolean' => 'Trạng thái hiển thị email phải là kiểu true/false.',
            'show_phone.boolean' => 'Trạng thái hiển thị số điện thoại phải là kiểu true/false.',
            'show_social_links.boolean' => 'Trạng thái hiển thị mạng xã hội phải là kiểu true/false.',
        ]);

        $privacy = $this->service->updatePrivacy($request->user(), $validated);

        return ApiResponse::success($privacy, 'Cập nhật tùy chọn quyền riêng tư thành công.');
    }

    public function getAccountStatus(Request $request): JsonResponse
    {
        $status = $this->service->getAccountStatus($request->user());

        return ApiResponse::success($status, 'Lấy trạng thái tài khoản thành công.');
    }

    public function updateAccount(
        InstructorAccountUpdateRequest $request
    ): JsonResponse {
        $user = $this->service->updateAccount(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorAccountResource($user),
            'Cập nhật thông tin tài khoản thành công.'
        );
    }

    public function updateIntroduction(
        InstructorIntroductionUpdateRequest $request
    ): JsonResponse {
        $result = $this->service->updateIntroduction(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorProfileSectionResource($result),
            'Cập nhật giới thiệu giảng viên thành công.'
        );
    }

    public function updateExpertise(
        InstructorExpertiseUpdateRequest $request
    ): JsonResponse {
        $result = $this->service->updateExpertise(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorProfileSectionResource($result),
            'Cập nhật chuyên môn giảng viên thành công.'
        );
    }

    public function completion(Request $request): JsonResponse
    {
        $completion = $this->service->getCompletion($request->user());

        return ApiResponse::success(
            new InstructorProfileCompletionResource($completion),
            'Lấy trạng thái hoàn thiện hồ sơ thành công.'
        );
    }
}