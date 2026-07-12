<x-filament-panels::page>
    <style>
        .cc-tabs,
        .cc-actions,
        .cc-row-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .cc-tab,
        .cc-button {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 12px;
            font-weight: 800;
            background: #fff;
            color: #082f63;
        }

        .cc-tab.active,
        .cc-button.primary {
            background: #005eea;
            border-color: #005eea;
            color: #fff;
        }

        .cc-button.danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }

        .cc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .cc-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            padding: 14px;
        }

        .cc-card h3 {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 900;
        }

        .cc-field label {
            display: block;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .cc-field input,
        .cc-field select,
        .cc-field textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
        }

        .cc-table-wrap {
            overflow-x: auto;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        .cc-table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
        }

        .cc-table th,
        .cc-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 9px;
            text-align: left;
            vertical-align: top;
        }

        .cc-pill {
            display: inline-flex;
            border-radius: 999px;
            padding: 3px 8px;
            background: #eff6ff;
            color: #005eea;
            font-weight: 800;
            font-size: 12px;
        }

        .cc-notice {
            border: 1px solid #f59e0b;
            background: #fffbeb;
            padding: 12px;
            border-radius: 8px;
            color: #78350f;
            font-weight: 700;
        }

        @media (max-width: 760px) {
            .cc-table {
                min-width: 760px;
            }
        }
    </style>

    <div class="space-y-6">
        <div class="cc-notice">
            This feature uses WhatsApp click-to-chat links. It does not automatically send messages, confirm delivery, or confirm that the recipient has read the message.
        </div>

        <div class="cc-tabs">
            @foreach (['compose' => 'Compose Message', 'templates' => 'Saved Templates', 'campaigns' => 'Message Campaigns', 'history' => 'Message History'] as $key => $label)
                <button type="button" wire:click="$set('activeTab', '{{ $key }}')" class="cc-tab {{ $activeTab === $key ? 'active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($activeTab === 'compose')
            <div class="cc-card">
                <h3>Compose WhatsApp Message</h3>
                <div class="cc-grid">
                    <div class="cc-field">
                        <label>Campaign name</label>
                        <input type="text" wire:model.live="campaignName">
                    </div>
                    <div class="cc-field">
                        <label>Recipient category</label>
                        <select wire:model.live="recipientType">
                            <option value="students">Students</option>
                            <option value="sponsors">Sponsors</option>
                            <option value="committee">Scholarship Committee Members</option>
                            <option value="staff">Staff</option>
                            <option value="all">All Contacts</option>
                        </select>
                    </div>
                    <div class="cc-field">
                        <label>Saved template</label>
                        <select wire:model.live="templateId">
                            <option value="">Select a template</option>
                            @foreach ($this->templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cc-field">
                        <label>Subject</label>
                        <input type="text" wire:model.live="subject">
                    </div>
                </div>

                <div class="cc-card" style="margin-top:12px;">
                    <h3>Recipient Filters</h3>
                    <div class="cc-grid">
                        <div class="cc-field"><label>Name / keyword</label><input type="text" wire:model.live.debounce.500ms="filters.search"></div>
                        <div class="cc-field"><label>Student ID</label><input type="text" wire:model.live.debounce.500ms="filters.student_id"></div>
                        <div class="cc-field">
                            <label>Scholarship type</label>
                            <select wire:model.live="filters.scholarship_type">
                                <option value="">Any</option>
                                <option value="pu_bursary">PU Bursary</option>
                                <option value="area">Area Scholarship</option>
                                <option value="copcef">COPCEF</option>
                                <option value="sponsor">Institution / Sponsor Scholarship</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="cc-field"><label>Scholarship status</label><input type="text" wire:model.live.debounce.500ms="filters.scholarship_status"></div>
                        <div class="cc-field">
                            <label>Programme</label>
                            <select wire:model.live="filters.programme_id"><option value="">Any</option>@foreach ($this->selectOptions['programmes'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                        </div>
                        <div class="cc-field">
                            <label>Faculty</label>
                            <select wire:model.live="filters.faculty_id"><option value="">Any</option>@foreach ($this->selectOptions['faculties'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                        </div>
                        <div class="cc-field">
                            <label>Department</label>
                            <select wire:model.live="filters.department_id"><option value="">Any</option>@foreach ($this->selectOptions['departments'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                        </div>
                        <div class="cc-field"><label>Country</label><input type="text" wire:model.live.debounce.500ms="filters.country"></div>
                        <div class="cc-field"><label>Region</label><input type="text" wire:model.live.debounce.500ms="filters.region"></div>
                        <div class="cc-field"><label>District</label><input type="text" wire:model.live.debounce.500ms="filters.district"></div>
                        <div class="cc-field"><label>Email</label><input type="text" wire:model.live.debounce.500ms="filters.email"></div>
                        <div class="cc-field"><label>Phone</label><input type="text" wire:model.live.debounce.500ms="filters.phone"></div>
                    </div>
                </div>

                <div class="cc-grid" style="margin-top:12px; align-items:start;">
                    <div class="cc-field" style="grid-column:1 / -1;">
                        <label>Message body</label>
                        <textarea rows="8" wire:model.live="messageBody"></textarea>
                        <div style="margin-top:6px; color:#526b88; font-weight:700;">Characters: {{ strlen($messageBody) }} | Selected recipients: {{ $this->selectedCount }}</div>
                    </div>
                </div>

                <details style="margin-top:12px;">
                    <summary style="cursor:pointer; font-weight:900; color:#005eea;">View placeholders</summary>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;">
                        @foreach ($this->placeholders as $placeholder)
                            <span class="cc-pill">{{ '{{' . $placeholder . '}}' }}</span>
                        @endforeach
                    </div>
                </details>

                <div class="cc-card" style="margin-top:12px;">
                    <strong>Preview</strong>
                    <div style="white-space:pre-wrap; margin-top:6px;">{{ $this->previewMessage }}</div>
                </div>

                <label style="display:flex; gap:8px; align-items:center; margin-top:12px; font-weight:800;">
                    <input type="checkbox" wire:model.live="selectAllMatching"> Select all recipients matching the current filter
                </label>

                @if (! $selectAllMatching)
                    <div class="cc-table-wrap" style="margin-top:12px;">
                        <table class="cc-table">
                            <thead><tr><th>Select</th><th>Name</th><th>Type</th><th>Phone</th><th>Email</th></tr></thead>
                            <tbody>
                            @foreach ($this->recipientRows as $recipient)
                                <tr>
                                    <td><input type="checkbox" wire:model.live="selectedRecipientKeys" value="{{ $recipient['key'] }}"></td>
                                    <td>{{ $recipient['name'] }}</td>
                                    <td>{{ str($recipient['type'])->headline() }}</td>
                                    <td>{{ $recipient['phone'] ?: '-' }}</td>
                                    <td>{{ $recipient['email'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="cc-actions" style="margin-top:14px;">
                    <button type="button" wire:click="createCampaign" wire:confirm="WhatsApp messages will not be sent automatically. The system will open each recipient's WhatsApp chat with the message prepared. You must press Send inside WhatsApp." class="cc-button primary">Generate WhatsApp Messages</button>
                    <button type="button" wire:click="saveDraft" class="cc-button">Save Draft</button>
                    <button type="button" wire:click="$set('activeTab', 'templates')" class="cc-button">Save as Template</button>
                    <button type="button" onclick="history.back()" class="cc-button danger">Cancel</button>
                </div>
            </div>
        @endif

        @if ($activeTab === 'templates')
            <div class="cc-card">
                <h3>{{ $editingTemplateId ? 'Edit Template' : 'Create Template' }}</h3>
                <div class="cc-grid">
                    <div class="cc-field"><label>Template name</label><input type="text" wire:model="templateName"></div>
                    <div class="cc-field">
                        <label>Recipient type</label>
                        <select wire:model="templateRecipientType"><option value="all">All</option><option value="students">Students</option><option value="sponsors">Sponsors</option><option value="committee">Committee</option><option value="staff">Staff</option></select>
                    </div>
                    <div class="cc-field"><label>Subject</label><input type="text" wire:model="templateSubject"></div>
                    <div class="cc-field" style="grid-column:1 / -1;"><label>Message body</label><textarea rows="6" wire:model="templateBody"></textarea></div>
                </div>
                <div class="cc-actions" style="margin-top:12px;">
                    <button type="button" wire:click="saveTemplate" class="cc-button primary">Save Template</button>
                </div>
            </div>

            <div class="cc-table-wrap">
                <table class="cc-table">
                    <thead><tr><th>Name</th><th>Recipient Type</th><th>Subject</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach ($this->templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>{{ str($template->recipient_type ?? 'all')->headline() }}</td>
                            <td>{{ $template->subject ?: '-' }}</td>
                            <td class="cc-row-actions">
                                <button type="button" wire:click="editTemplate({{ $template->id }})" class="cc-button">Edit</button>
                                <button type="button" wire:click="duplicateTemplate({{ $template->id }})" class="cc-button">Duplicate</button>
                                <button type="button" wire:click="deleteTemplate({{ $template->id }})" wire:confirm="Delete this template?" class="cc-button danger">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($activeTab === 'campaigns')
            @php($campaign = $this->selectedCampaign)
            <div class="cc-grid">
                @foreach ($this->campaigns as $item)
                    <button type="button" wire:click="openCampaign({{ $item->id }})" class="cc-card" style="text-align:left;">
                        <strong>{{ $item->campaign_name }}</strong>
                        <div>{{ $item->recipient_type }} | {{ $item->status }}</div>
                        <div>{{ $item->sent_count }} sent, {{ $item->opened_count }} opened, {{ $item->pending_count }} pending, {{ $item->invalid_recipients }} invalid</div>
                    </button>
                @endforeach
            </div>

            @if ($campaign)
                <div class="cc-card">
                    <h3>{{ $campaign->campaign_name }}</h3>
                    <div class="cc-actions">
                        <span class="cc-pill">Progress: {{ $campaign->sent_count + $campaign->skipped_count }} of {{ $campaign->valid_recipients }}</span>
                        <span class="cc-pill">Pending {{ $campaign->pending_count }}</span>
                        <span class="cc-pill">Opened {{ $campaign->opened_count }}</span>
                        <span class="cc-pill">Sent {{ $campaign->sent_count }}</span>
                        <span class="cc-pill">Invalid {{ $campaign->invalid_recipients }}</span>
                    </div>

                    @php($next = $campaign->recipients->firstWhere('status', 'Pending'))
                    @if ($next && $next->whatsapp_url)
                        <a href="{{ $next->whatsapp_url }}" target="_blank" rel="noopener noreferrer" wire:click="markOpened({{ $next->id }})" class="cc-button primary" style="display:inline-flex; margin-top:12px;">Open Next Pending Recipient</a>
                    @endif
                </div>

                <div class="cc-table-wrap">
                    <table class="cc-table">
                        <thead><tr><th>Recipient</th><th>Category</th><th>Phone</th><th>Message Preview</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        @foreach ($campaign->recipients as $recipient)
                            <tr>
                                <td>{{ $recipient->recipient_name }}</td>
                                <td>{{ str($recipient->recipient_type)->headline() }}</td>
                                <td>{{ $recipient->normalized_phone ?: $recipient->phone_number }}</td>
                                <td>{{ str($recipient->personalized_message)->limit(120) }}</td>
                                <td><span class="cc-pill">{{ $recipient->status }}</span>@if ($recipient->validation_error)<br>{{ $recipient->validation_error }}@endif</td>
                                <td class="cc-row-actions">
                                    @if ($recipient->whatsapp_url)
                                        <a href="{{ $recipient->whatsapp_url }}" target="_blank" rel="noopener noreferrer" wire:click="markOpened({{ $recipient->id }})" class="cc-button primary">Send on WhatsApp</a>
                                        <button type="button" wire:click="markSent({{ $recipient->id }})" class="cc-button">Mark as Sent</button>
                                        <button type="button" wire:click="skipRecipient({{ $recipient->id }})" class="cc-button danger">Skip</button>
                                    @else
                                        Invalid Number
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        @if ($activeTab === 'history')
            <div class="cc-card">
                <h3>Message History</h3>
                <div class="cc-field" style="max-width:260px;">
                    <label>Status filter</label>
                    <select wire:model.live="campaignStatusFilter"><option value="">All</option><option>Draft</option><option>Ready</option><option>In Progress</option><option>Completed</option><option>Cancelled</option></select>
                </div>
            </div>
            <div class="cc-table-wrap">
                <table class="cc-table">
                    <thead><tr><th>Campaign</th><th>Channel</th><th>Recipients</th><th>Counts</th><th>Created By</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    @foreach ($this->campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->campaign_name }}</td>
                            <td>{{ str($campaign->channel)->headline() }}</td>
                            <td>{{ str($campaign->recipient_type)->headline() }}</td>
                            <td>Total {{ $campaign->total_recipients }} | Sent {{ $campaign->sent_count }} | Opened {{ $campaign->opened_count }} | Pending {{ $campaign->pending_count }} | Invalid {{ $campaign->invalid_recipients }}</td>
                            <td>{{ $campaign->creator?->name ?: '-' }}</td>
                            <td>{{ $campaign->created_at?->format('M j, Y g:i A') }}</td>
                            <td>{{ $campaign->status }}</td>
                            <td><button type="button" wire:click="openCampaign({{ $campaign->id }})" class="cc-button">Open</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
