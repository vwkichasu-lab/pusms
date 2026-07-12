<?php

use App\Models\Student;
use App\Services\TemplateVariableService;

it('replaces supported placeholders and removes unknown placeholders', function () {
    $student = new Student([
        'first_name' => 'Victor',
        'last_name' => 'Kichasu',
        'student_id' => 'PUIT/22110014',
        'phone' => '0241234567',
        'email' => 'victor@example.com',
    ]);

    $message = app(TemplateVariableService::class)->render(
        'Hello {{first_name}}, ID {{student_id}} {{unknown_value}}',
        $student,
    );

    expect($message)->toBe('Hello Victor, ID PUIT/22110014 ');
});
