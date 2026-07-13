<x-filament-panels::page>
    <style>
        .pusms-chat-shell {
            height: calc(100vh - 190px);
            min-height: 620px;
            display: grid;
            grid-template-columns: minmax(300px, 380px) minmax(0, 1fr);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .pusms-chat-list {
            border-right: 1px solid #d9e2ef;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .pusms-chat-list-head,
        .pusms-chat-head,
        .pusms-chat-compose {
            padding: 12px;
            border-bottom: 1px solid #d9e2ef;
            background: #fff;
        }

        .pusms-chat-list-head h3,
        .pusms-chat-head h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
        }

        .pusms-chat-search {
            width: 100%;
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
        }

        .pusms-new-chat {
            margin-top: 10px;
            border: 1px solid #d9e2ef;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .pusms-new-chat-row {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #eef2f7;
            background: #fff;
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 10px;
            text-align: left;
            cursor: pointer;
        }

        .pusms-new-chat-row:hover {
            background: #eaf3ff;
        }

        .pusms-chat-contacts {
            overflow-y: auto;
        }

        .pusms-chat-contact {
            width: 100%;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 12px;
            border: 0;
            border-bottom: 1px solid #e2e8f0;
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .pusms-chat-contact.is-active,
        .pusms-chat-contact:hover {
            background: #eaf3ff;
        }

        .pusms-chat-avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0b3b75;
            color: #fff;
            font-weight: 900;
            font-size: 12px;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .pusms-chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pusms-chat-contact-name {
            font-weight: 900;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pusms-chat-contact-last,
        .pusms-chat-status {
            color: #64748b;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pusms-chat-badge {
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #16a34a;
            color: #fff;
            font-size: 12px;
            font-weight: 900;
        }

        .pusms-chat-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #f6f8fb;
        }

        .pusms-chat-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .pusms-chat-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pusms-chat-action {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            padding: 8px 10px;
            font-weight: 800;
        }

        .pusms-chat-action.danger {
            border-color: #fecaca;
            color: #b91c1c;
        }

        .pusms-chat-thread {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            background:
                linear-gradient(rgba(255, 255, 255, .86), rgba(255, 255, 255, .86)),
                repeating-linear-gradient(45deg, #e2e8f0 0, #e2e8f0 1px, transparent 1px, transparent 18px);
        }

        .pusms-chat-day {
            width: fit-content;
            margin: 0 auto 14px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }

        .pusms-chat-message {
            max-width: min(620px, 78%);
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .pusms-chat-message.is-mine {
            margin-left: auto;
            align-items: flex-end;
        }

        .pusms-chat-bubble {
            padding: 10px 12px;
            border-radius: 10px;
            border-top-left-radius: 2px;
            background: #ffffff;
            border: 1px solid #d9e2ef;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        }

        .pusms-chat-message.is-mine .pusms-chat-bubble {
            border-top-left-radius: 10px;
            border-top-right-radius: 2px;
            background: #d9fdd3;
            border-color: #bee8b8;
        }

        .pusms-chat-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .pusms-chat-delete {
            color: #b91c1c;
            font-weight: 800;
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        .pusms-chat-attachment {
            display: inline-flex;
            margin-top: 8px;
            color: #005eea;
            font-weight: 900;
        }

        .pusms-chat-compose {
            border-top: 1px solid #d9e2ef;
            border-bottom: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: end;
            background: #fff;
        }

        .pusms-chat-compose textarea {
            width: 100%;
            min-height: 52px;
            max-height: 140px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            resize: vertical;
            background: #fff;
        }

        .pusms-chat-compose-side {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .pusms-chat-file input {
            width: 160px;
            font-size: 12px;
        }

        .pusms-chat-send {
            border: 0;
            border-radius: 999px;
            background: #005eea;
            color: #fff;
            min-width: 52px;
            height: 52px;
            padding: 0 18px;
            font-weight: 900;
        }

        .pusms-chat-empty {
            height: 100%;
            display: grid;
            place-items: center;
            color: #64748b;
            font-weight: 800;
            text-align: center;
            padding: 24px;
        }

        @media (max-width: 900px) {
            .pusms-chat-shell {
                grid-template-columns: 1fr;
                height: auto;
            }

            .pusms-chat-list {
                max-height: 320px;
                border-right: 0;
                border-bottom: 1px solid #d9e2ef;
            }

            .pusms-chat-main {
                min-height: 620px;
            }
        }
    </style>

    <div class="pusms-chat-shell" wire:poll.3s="refreshChat">
        <aside class="pusms-chat-list">
            <div class="pusms-chat-list-head">
                <h3>Chats</h3>
                <input class="pusms-chat-search" type="search" wire:model.live.debounce.400ms="newChatSearch" placeholder="Enter username, name, or email to start chat">

                @if ($this->newChatResults->isNotEmpty())
                    <div class="pusms-new-chat">
                        @foreach ($this->newChatResults as $user)
                            <button type="button" wire:click="startConversation({{ $user['id'] }})" class="pusms-new-chat-row">
                                <span class="pusms-chat-avatar" style="width:36px;height:36px;">
                                    @if ($user['avatar'])
                                        <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}">
                                    @else
                                        {{ $user['initials'] }}
                                    @endif
                                </span>
                                <span style="min-width:0;">
                                    <span class="pusms-chat-contact-name">{{ $user['name'] }}</span>
                                    <span class="pusms-chat-contact-last">{{ $user['subtitle'] }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <input class="pusms-chat-search" type="search" wire:model.live.debounce.400ms="search" placeholder="Search existing chats">
            </div>

            <div class="pusms-chat-contacts">
                @forelse ($this->contacts as $contact)
                    <button
                        type="button"
                        wire:click="selectConversation('{{ $contact['id'] }}')"
                        class="pusms-chat-contact {{ $this->selectedConversation === $contact['id'] ? 'is-active' : '' }}"
                    >
                        <span class="pusms-chat-avatar">
                            @if ($contact['avatar'])
                                <img src="{{ $contact['avatar'] }}" alt="{{ $contact['name'] }}">
                            @else
                                {{ $contact['initials'] }}
                            @endif
                        </span>
                        <span style="min-width:0;">
                            <span class="pusms-chat-contact-name">{{ $contact['name'] }}</span>
                            <span class="pusms-chat-contact-last">
                                {{ $contact['last']?->body ? str($contact['last']->body)->squish()->limit(42) : $contact['subtitle'] }}
                            </span>
                        </span>
                        @if ($contact['unread'] > 0)
                            <span class="pusms-chat-badge">{{ $contact['unread'] }}</span>
                        @endif
                    </button>
                @empty
                    <div style="padding:18px; color:#64748b; font-weight:800;">
                        No chats yet. Enter a username above to start one.
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="pusms-chat-main">
            <header class="pusms-chat-head">
                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <span class="pusms-chat-avatar">
                        @if ($this->selectedContact['avatar'])
                            <img src="{{ $this->selectedContact['avatar'] }}" alt="{{ $this->selectedContact['name'] }}">
                        @else
                            {{ $this->selectedContact['initials'] }}
                        @endif
                    </span>
                    <div style="min-width:0;">
                        <h3>{{ $this->selectedContact['name'] }}</h3>
                        <div class="pusms-chat-status">{{ $this->selectedContact['subtitle'] }}</div>
                    </div>
                </div>
                <div class="pusms-chat-actions">
                    <button type="button" wire:click="clearChat" wire:confirm="Clear this chat from your view?" class="pusms-chat-action danger">Clear chat</button>
                </div>
            </header>

            <main class="pusms-chat-thread" id="pusmsChatThread">
                <div class="pusms-chat-day">Live chat updates every few seconds</div>

                @if ($this->selectedConversation === '')
                    <div class="pusms-chat-empty">
                        Enter a username, name, or email on the left to start chatting. Existing chats will appear there after messages are sent or received.
                    </div>
                @else
                    @forelse ($this->conversationMessages as $message)
                    @php($isMine = $message->sender_id === auth()->id())
                    <article class="pusms-chat-message {{ $isMine ? 'is-mine' : '' }}">
                        <div class="pusms-chat-bubble">
                            @if ($this->selectedConversation === 'all')
                                <div style="font-size:12px; font-weight:900; color:#526b88; margin-bottom:4px;">
                                    {{ $message->sender?->name }}
                                    @if ($isMine && $message->broadcast_group_id)
                                        to all users
                                    @endif
                                </div>
                            @endif
                            <div style="white-space:pre-wrap;">{{ $message->body }}</div>
                            @if ($message->attachment_path)
                                <a class="pusms-chat-attachment" href="{{ $this->attachmentUrl($message) }}" target="_blank">
                                    {{ $message->attachment_original_name ?: 'Open attachment' }}
                                </a>
                            @endif
                        </div>
                        <div class="pusms-chat-meta">
                            <span>{{ $message->created_at?->format('M j, g:i A') }}</span>
                            <button type="button" wire:click="deleteMessage({{ $message->id }})" wire:confirm="Delete this message from your view?" class="pusms-chat-delete">Delete</button>
                        </div>
                    </article>
                    @empty
                        <div class="pusms-chat-empty">
                            No messages in this chat yet. Type below to send the first message.
                        </div>
                    @endforelse
                @endif
            </main>

            <form wire:submit="sendMessage" class="pusms-chat-compose">
                <textarea wire:model.live="messageText" placeholder="Type a message"></textarea>
                <div class="pusms-chat-compose-side">
                    <label class="pusms-chat-file">
                        <input type="file" wire:model="attachment">
                    </label>
                    <button type="submit" class="pusms-chat-send">Send</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            const thread = document.getElementById('pusmsChatThread');
            if (thread) {
                thread.scrollTop = thread.scrollHeight;
            }
        });
    </script>
</x-filament-panels::page>
