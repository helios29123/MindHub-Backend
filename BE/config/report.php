<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report & Learning Analytics Thresholds
    |--------------------------------------------------------------------------
    |
    | Configurable business thresholds for reports, learner risk, inactive learners,
    | and learning activity heatmap levels.
    |
    */
    'inactive_learner_days' => (int) env('REPORT_INACTIVE_LEARNER_DAYS', 14),
    'learner_risk_enrollment_age_days' => (int) env('REPORT_LEARNER_RISK_ENROLLMENT_AGE_DAYS', 14),
    'learner_risk_progress_threshold' => (float) env('REPORT_LEARNER_RISK_PROGRESS_THRESHOLD', 30.0),
    'learner_risk_inactive_days' => (int) env('REPORT_LEARNER_RISK_INACTIVE_DAYS', 7),
    'heatmap_level_1_seconds' => (int) env('REPORT_HEATMAP_LEVEL_1_SECONDS', 900),
    'heatmap_level_2_seconds' => (int) env('REPORT_HEATMAP_LEVEL_2_SECONDS', 2700),
];
