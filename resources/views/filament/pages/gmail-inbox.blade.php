<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Scholarship Gmail Inbox</x-slot>
        <x-slot name="description">Use this inbox to read replies from students, sponsors, and Gmail notifications for the connected scholarship account.</x-slot>

        <div class="space-y-4">
            <p style="color:#475569;">
                PUSMS currently has permission to send email through Gmail. To read full replies inside PUSMS, the Google connection must be upgraded later with Gmail read permission. For now, open the connected scholarship Gmail inbox directly.
            </p>

            <x-filament::button tag="a" href="https://mail.google.com/mail/u/pentvarsscholarship@gmail.com/#inbox" target="_blank" icon="heroicon-m-inbox">
                Open Scholarship Gmail Inbox
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
