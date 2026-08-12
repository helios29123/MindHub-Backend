<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MindHub AI Assistant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API key, base URL, model, and system prompt template
    | for the AI Course Advisor.
    |
    */

    'api_key' => env('GEMMA_API_KEY', 'sk-7273ff53855ce6db-efq19v-4da80e3b'),
    'base_url' => env('GEMMA_BASE_URL', 'https://ai.mindhub.io.vn/v1'),
    'model' => env('GEMMA_MODEL', 'gemini/gemma-4-31b-it'),

    /*
    |--------------------------------------------------------------------------
    | System Prompt Template
    |--------------------------------------------------------------------------
    |
    | Variables available for replacement:
    | - {coursesBrief}: Brief list of courses formatted as bullet points.
    | - {query}: The user's search query.
    |
    */
    'system_prompt' => "Bạn là Trợ lý Học tập AI tại MindHub. Hãy tư vấn khóa học phù hợp cho người dùng bằng tiếng Việt, thân thiện và hướng dẫn chi tiết.\n\nDanh sách khóa học có sẵn:\n{coursesBrief}\n\nQuy định bắt buộc:\n- Trả lời trực tiếp bằng tiếng Việt. Không hiển thị quá trình suy nghĩ (thinking) hay các phần dịch thuật tiếng Anh.\n- Ở dòng cuối cùng của phản hồi, bắt buộc phải ghi danh sách ID đề xuất theo dạng: RECOMMENDED_COURSES: [course-id1, course-id2] (Ví dụ: RECOMMENDED_COURSES: [course-220015]). Nếu không có, ghi: RECOMMENDED_COURSES: []",
    'user_prompt' => "Hãy tư vấn khóa học phù hợp cho tôi với chủ đề: {query}",
];
