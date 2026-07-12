<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">SMS Provider Status</x-slot>
            <x-slot name="description">PUSMS can send real SMS only when a provider API key and sender name are configured in Railway.</x-slot>

            @php($status = $this->providerStatus)

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:12px;">
                    <strong>Active Provider</strong>
                    <p style="margin-top:6px;">{{ str($status['provider'])->headline() }}</p>
                </div>
                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:12px;">
                    <strong>Default Country Code</strong>
                    <p style="margin-top:6px;">+{{ $status['country_code'] }}</p>
                </div>
                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:12px;">
                    <strong>Ready To Send</strong>
                    <p style="margin-top:6px; color:{{ $status['ready'] ? '#047857' : '#b91c1c' }}; font-weight:800;">
                        {{ $status['ready'] ? 'Yes' : 'No, credentials are missing' }}
                    </p>
                </div>
            </div>

            <div style="margin-top:16px; overflow-x:auto;">
                <table style="width:100%; min-width:640px; border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Required Railway Variable</th>
                        <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($status['checks'] as $label => $configured)
                        <tr>
                            <td style="border:1px solid #cbd5e1; padding:8px;">{{ $label }}</td>
                            <td style="border:1px solid #cbd5e1; padding:8px; color:{{ $configured ? '#047857' : '#b91c1c' }}; font-weight:800;">
                                {{ $configured ? 'Configured' : 'Missing' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <form wire:submit="sendTest" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap gap-3">
                <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
                    Send Test SMS
                </x-filament::button>

                <x-filament::button tag="a" color="gray" href="{{ route('filament.admin.pages.send-sms') }}">
                    Go To Send SMS
                </x-filament::button>
            </div>
        </form>

        <x-filament::section>
            <x-slot name="heading">Recommended Setup</x-slot>

            <p style="color:#526b88;">
                Since Hubtel is asking for business certificate approval, use Arkesel first. In Railway, set:
            </p>
            <pre style="margin-top:12px; padding:12px; border:1px solid #cbd5e1; border-radius:8px; overflow:auto;">PUSMS_SMS_PROVIDER=arkesel
PUSMS_SMS_DEFAULT_COUNTRY_CODE=233
ARKESEL_SMS_API_KEY=your_arkesel_api_key
ARKESEL_SMS_SENDER_ID=PUSMS</pre>
            <p style="margin-top:12px; color:#526b88;">
                After those variables are saved and Railway redeploys, use the test box above. No secret value is displayed on this page.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
