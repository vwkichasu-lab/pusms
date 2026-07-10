<?php

namespace Tests\Feature;

use App\Jobs\SendCommunicationRecipient;
use App\Models\Communication;
use App\Models\CommunicationRecipient;
use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\SmsMessage;
use App\Services\Notifications\Exceptions\NotificationConfigurationException;
use App\Services\Notifications\Exceptions\NotificationValidationException;
use App\Services\Notifications\Exceptions\TransientNotificationException;
use App\Services\Notifications\Senders\FakeEmailSender;
use App\Services\Notifications\Senders\FakeSmsSender;
use App\Services\Notifications\Senders\HubtelSmsSender;
use App\Services\Notifications\Senders\LaravelSmtpEmailSender;
use App\Services\Notifications\Support\SimpleTemplateRenderer;
use App\Services\TemplateVariableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        FakeEmailSender::reset();
        FakeSmsSender::reset();
        config()->set('notifications.sms.default_country_code', '233');
    }

    public function test_successful_email_sending_uses_fake_provider_without_network(): void
    {
        config()->set('notifications.email.provider', 'fake');

        $result = app(EmailSender::class)->send(new EmailMessage(
            to: 'student@example.com',
            subject: 'Scholarship update',
            text: 'Your scholarship record is ready.',
            idempotencyKey: 'email-1',
        ));

        $this->assertTrue($result->success);
        $this->assertSame('fake-email', $result->provider);
        $this->assertCount(1, FakeEmailSender::$sent);
    }

    public function test_successful_sms_sending_uses_hubtel_http_adapter(): void
    {
        config()->set('services.hubtel.client_id', 'client-id');
        config()->set('services.hubtel.client_secret', 'client-secret');
        config()->set('services.hubtel.sender_id', 'PUSMS');
        config()->set('services.hubtel.base_url', 'https://smsc.hubtel.test/send');

        Http::fake([
            'smsc.hubtel.test/*' => Http::response(['messageId' => 'hubtel-123'], 200),
        ]);

        $result = app(HubtelSmsSender::class)->send(new SmsMessage(
            to: '0244123456',
            message: 'PUSMS test SMS.',
            idempotencyKey: 'sms-1',
        ));

        $this->assertTrue($result->success);
        $this->assertSame('hubtel', $result->provider);
        $this->assertSame('hubtel-123', $result->providerMessageId);

        Http::assertSent(fn ($request): bool => $request['To'] === '233244123456');
    }

    public function test_invalid_email_address_is_rejected(): void
    {
        $this->expectException(NotificationValidationException::class);

        app(FakeEmailSender::class)->send(new EmailMessage(
            to: 'not-an-email',
            subject: 'Bad email',
            text: 'Message',
        ));
    }

    public function test_invalid_phone_number_is_rejected(): void
    {
        $this->expectException(NotificationValidationException::class);

        app(FakeSmsSender::class)->send(new SmsMessage(
            to: '123',
            message: 'Message',
        ));
    }

    public function test_missing_smtp_configuration_is_reported_clearly(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', null);

        $this->expectException(NotificationConfigurationException::class);

        app(LaravelSmtpEmailSender::class)->send(new EmailMessage(
            to: 'student@example.com',
            subject: 'Scholarship update',
            text: 'Message',
        ));
    }

    public function test_hubtel_server_error_maps_to_transient_failure(): void
    {
        config()->set('services.hubtel.client_id', 'client-id');
        config()->set('services.hubtel.client_secret', 'client-secret');
        config()->set('services.hubtel.sender_id', 'PUSMS');
        config()->set('services.hubtel.base_url', 'https://smsc.hubtel.test/send');

        Http::fake([
            'smsc.hubtel.test/*' => Http::response(['error' => 'temporary'], 500),
        ]);

        $this->expectException(TransientNotificationException::class);

        app(HubtelSmsSender::class)->send(new SmsMessage(
            to: '+233244123456',
            message: 'Message',
        ));
    }

    public function test_template_renderer_escapes_html_values(): void
    {
        $renderer = app(SimpleTemplateRenderer::class);

        $html = $renderer->renderHtml('Dear {{student_name}}', [
            'student_name' => '<script>alert(1)</script>',
        ]);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_fake_provider_preserves_idempotency_key_without_real_delivery(): void
    {
        $result = app(FakeEmailSender::class)->send(new EmailMessage(
            to: 'student@example.com',
            subject: 'Scholarship update',
            text: 'Hello',
            idempotencyKey: 'communication-recipient:1:email',
        ));

        $this->assertTrue($result->success);
        $this->assertSame('communication-recipient:1:email', $result->idempotencyKey);
    }

    public function test_sent_communication_recipient_is_not_sent_again(): void
    {
        $communication = Communication::create([
            'subject' => 'Already sent',
            'message' => 'Message',
            'communication_type' => 'email',
            'status' => 'completed',
        ]);

        $recipient = CommunicationRecipient::create([
            'communication_id' => $communication->id,
            'channel' => 'email',
            'destination' => 'student@example.com',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        (new SendCommunicationRecipient($recipient->id))->handle(
            app(FakeEmailSender::class),
            app(FakeSmsSender::class),
            app(TemplateVariableService::class),
        );

        $this->assertCount(0, FakeEmailSender::$sent);
    }
}
