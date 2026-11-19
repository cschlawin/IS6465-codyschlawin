<?php

use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    public function testValidPreferredTimeIsAccepted(): void
    {
        $this->assertSame('', validatePreferredTime('Morning'));
    }

    public function testMissingPreferredTimeIsRejected(): void
    {
        $this->assertSame('Choose a valid preferred time.', validatePreferredTime(''));
    }

    public function testAlteredPreferredTimeIsRejected(): void
    {
        $this->assertSame('Choose a valid preferred time.', validatePreferredTime('Midnight'));
    }

    public function testMultipleValidTopicsAreAccepted(): void
    {
        $this->assertSame('', validateTopics(['Exam review', 'Study plan']));
    }

    public function testMissingTopicsAreRejected(): void
    {
        $this->assertSame('Select at least one valid topic.', validateTopics([]));
    }

    public function testAlteredTopicIsRejected(): void
    {
        $this->assertSame('Select at least one valid topic.', validateTopics(['Free tutoring']));
    }

    public function testReasonMinimumLengthIsAccepted(): void
    {
        $this->assertSame('', validateReason('1234567890'));
    }

    public function testReasonMaximumLengthIsAccepted(): void
    {
        $this->assertSame('', validateReason(str_repeat('a', 500)));
    }

    public function testReasonTooShortIsRejected(): void
    {
        $this->assertSame(
            'Describe your request using 10 to 500 characters.',
            validateReason('123456789')
        );
    }

    public function testReasonTooLongIsRejected(): void
    {
        $this->assertSame(
            'Describe your request using 10 to 500 characters.',
            validateReason(str_repeat('a', 501))
        );
    }
}
