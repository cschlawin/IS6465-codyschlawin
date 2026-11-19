<?php
function validatePreferredTime(string $preferredTime): string
{
    return in_array($preferredTime, ['Morning', 'Afternoon', 'Evening'], true)
        ? '' : 'Choose a valid preferred time.';
}
function validateTopics(mixed $topics): string
{
    if (!is_array($topics) || $topics === []) return 'Select at least one valid topic.';
    foreach ($topics as $topic) {
        if (!is_string($topic) || !in_array($topic, ['Exam review','Practice problems','Study plan'], true)) {
            return 'Select at least one valid topic.';
        }
    }
    return '';
}
function validateReason(string $reason): string
{
    $reason = trim($reason);
    $length = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);
    return ($length < 10 || $length > 500) ? 'Describe your request using 10 to 500 characters.' : '';
}
