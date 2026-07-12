<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardReadiness;
use App\Filament\Pages\GeneratedLetters;
use App\Filament\Pages\GmailInbox;
use App\Filament\Pages\MyProfile;
use App\Filament\Widgets\PusmsStatsOverview;
use App\Filament\Widgets\ScholarshipCoverageChart;
use App\Filament\Widgets\ScholarshipGrowthChart;
use App\Filament\Widgets\ScholarshipSemesterSpendChart;
use App\Filament\Widgets\ScholarshipSpendChart;
use App\Filament\Widgets\StudentMovementChart;
use App\Filament\Widgets\StudentsByLevelChart;
use App\Filament\Widgets\StudentsByProgrammeChart;
use App\Filament\Widgets\StudentsByRegionChart;
use App\Filament\Widgets\StudentsBySchoolChart;
use App\Models\InternalMessage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentIcon;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    private function notificationBellMarkup(): string
    {
        $unread = auth()->check()
            ? InternalMessage::query()->where('recipient_id', auth()->id())->whereNull('read_at')->count()
            : 0;

        return <<<HTML
            <a href="/admin/team-messages" class="pusms-header-bell" title="Team messages">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 0 0-5-6.71V3a2 2 0 1 0-4 0v1.29A7 7 0 0 0 5 11v5l-2 2v1h18v-1l-2-2Z"/>
                </svg>
                <strong>{$unread}</strong>
            </a>
            <style>
                .pusms-header-bell {
                    position: relative;
                    width: 48px;
                    height: 48px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    margin-inline: .35rem;
                    border: 1px solid #dbe3ee;
                    border-radius: 14px;
                    background: #f8fafc;
                    color: #020617;
                    text-decoration: none;
                    box-shadow: 0 4px 16px rgba(15, 23, 42, .08);
                }

                .pusms-header-bell svg {
                    width: 24px;
                    height: 24px;
                }

                .pusms-header-bell strong {
                    position: absolute;
                    top: 7px;
                    right: 7px;
                    min-width: 18px;
                    height: 18px;
                    padding-inline: 4px;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: #111827;
                    color: #ffffff;
                    font-size: 11px;
                    border: 2px solid #ffffff;
                }

                html.dark .pusms-header-bell {
                    background: #161b22;
                    border-color: #30363d;
                    color: #f8fafc;
                }
            </style>
        HTML;
    }

    private function assistantMarkup(): string
    {
        if (! request()->is([
            'admin/send-email',
            'admin/send-sms',
            'admin/message-history',
            'admin/team-messages',
            'admin/gmail-settings',
            'admin/gmail-inbox',
        ])) {
            return '';
        }

        $csrf = csrf_token();

        return <<<HTML
            <div class="pusms-ai-assistant" id="pusmsAiAssistant">
                <button type="button" class="pusms-ai-toggle" id="pusmsAiToggle">Send with Me</button>
                <div class="pusms-ai-panel" id="pusmsAiPanel" hidden>
                    <div class="pusms-ai-head">
                        <strong>PUSMS AI</strong>
                        <button type="button" id="pusmsAiClose">x</button>
                    </div>
                    <div class="pusms-ai-body" id="pusmsAiBody">
                        <div class="pusms-ai-msg">What do you want to send today?</div>
                    </div>
                    <form id="pusmsAiForm" class="pusms-ai-form">
                        <textarea id="pusmsAiInput" placeholder="Describe the email or SMS you want..." rows="3"></textarea>
                        <button type="submit">Generate Message</button>
                    </form>
                </div>
            </div>
            <style>
                .pusms-ai-assistant {
                    position: fixed;
                    right: 22px;
                    bottom: 22px;
                    z-index: 61;
                }

                .pusms-ai-toggle {
                    min-width: 112px;
                    height: 56px;
                    padding-inline: 16px;
                    border-radius: 999px;
                    border: 1px solid #082f63;
                    background: #082f63;
                    color: #ffffff;
                    font-weight: 900;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, .22);
                }

                .pusms-ai-panel {
                    position: absolute;
                    right: 0;
                    bottom: 68px;
                    width: min(380px, calc(100vw - 32px));
                    border: 1px solid #cbd5e1;
                    border-radius: 10px;
                    overflow: hidden;
                    background: #ffffff;
                    box-shadow: 0 16px 40px rgba(15, 23, 42, .22);
                }

                .pusms-ai-head {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 12px;
                    border-bottom: 1px solid #cbd5e1;
                    color: #082f63;
                }

                .pusms-ai-head button {
                    border: 0;
                    background: transparent;
                    font-weight: 900;
                    cursor: pointer;
                }

                .pusms-ai-body {
                    max-height: 320px;
                    overflow: auto;
                    padding: 12px;
                    display: grid;
                    gap: 10px;
                }

                .pusms-ai-msg {
                    border: 1px solid #dbe3ee;
                    border-radius: 8px;
                    padding: 9px;
                    background: #f8fafc;
                    white-space: pre-wrap;
                }

                .pusms-ai-msg.user {
                    background: #eff6ff;
                    border-color: #bfdbfe;
                }

                .pusms-ai-form {
                    display: grid;
                    gap: 8px;
                    padding: 12px;
                    border-top: 1px solid #cbd5e1;
                }

                .pusms-ai-form textarea {
                    width: 100%;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    padding: 8px;
                }

                .pusms-ai-form button {
                    border: 0;
                    border-radius: 8px;
                    padding: 9px 12px;
                    background: #005eea;
                    color: #ffffff;
                    font-weight: 800;
                }
            </style>
            <script>
                (() => {
                    const toggle = document.getElementById('pusmsAiToggle');
                    const panel = document.getElementById('pusmsAiPanel');
                    const close = document.getElementById('pusmsAiClose');
                    const form = document.getElementById('pusmsAiForm');
                    const input = document.getElementById('pusmsAiInput');
                    const body = document.getElementById('pusmsAiBody');
                    if (!toggle || !panel || !form || toggle.dataset.ready) return;
                    toggle.dataset.ready = '1';
                    toggle.addEventListener('click', () => panel.hidden = !panel.hidden);
                    close?.addEventListener('click', () => panel.hidden = true);
                    const add = (text, cls = '') => {
                        const div = document.createElement('div');
                        div.className = 'pusms-ai-msg ' + cls;
                        div.textContent = text;
                        body.appendChild(div);
                        body.scrollTop = body.scrollHeight;
                    };
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        const message = input.value.trim();
                        if (!message) return;
                        input.value = '';
                        add(message, 'user');
                        add('Thinking...');
                        const pending = body.lastElementChild;
                        try {
                            const response = await fetch('/admin/ai-assistant', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{$csrf}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ message, page: window.location.pathname })
                            });
                            const json = await response.json();
                            pending.textContent = json.answer || 'I could not find an answer.';
                            if (json.generated_message) {
                                const useButton = document.createElement('button');
                                useButton.type = 'button';
                                useButton.textContent = 'Use in form';
                                useButton.style.cssText = 'display:inline-block;margin-top:8px;border:0;border-radius:8px;padding:8px 10px;background:#005eea;color:#fff;font-weight:800;';
                                useButton.addEventListener('click', () => {
                                    const messageField = document.getElementById('pusms-message-field');
                                    const subjectField = document.getElementById('pusms-subject-field');
                                    if (messageField) {
                                        messageField.value = json.generated_message;
                                        messageField.dispatchEvent(new Event('input', { bubbles: true }));
                                        messageField.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                    if (subjectField && json.subject) {
                                        subjectField.value = json.subject;
                                        subjectField.dispatchEvent(new Event('input', { bubbles: true }));
                                        subjectField.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                });
                                pending.appendChild(document.createTextNode('\\n'));
                                pending.appendChild(useButton);
                            }
                        } catch (error) {
                            pending.textContent = 'The assistant could not respond. Please try again.';
                        }
                    });
                })();
            </script>
        HTML;
    }

    public function panel(Panel $panel): Panel
    {
        FilamentIcon::register([
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => 'heroicon-o-list-bullet',
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => 'heroicon-o-list-bullet',
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => 'heroicon-o-list-bullet',
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => 'heroicon-o-list-bullet',
            PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => 'heroicon-o-list-bullet',
            PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => 'heroicon-o-list-bullet',
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(MyProfile::class, isSimple: false)
            ->sidebarCollapsibleOnDesktop()
            ->darkMode()
            ->brandName('PUSMS')
            ->brandLogo(asset('images/pentvars-3d.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('images/pentvars-3d.png'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'warning' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        :root {
                            --pusms-navy: #082f63;
                            --pusms-border: #cbd5e1;
                            --pusms-gold: #d69e2e;
                        }

                        html:not(.dark) body,
                        html:not(.dark) .fi-body {
                            background: #ffffff !important;
                        }

                        html:not(.dark) .fi-sidebar,
                        html:not(.dark) .fi-topbar nav,
                        html:not(.dark) .fi-section,
                        html:not(.dark) .fi-ta,
                        html:not(.dark) .fi-wi-stats-overview-stat,
                        html:not(.dark) .fi-fo-field-wrp,
                        html:not(.dark) .fi-modal-window {
                            background-color: #ffffff !important;
                            border-color: var(--pusms-border) !important;
                        }

                        html.dark body,
                        html.dark .fi-body,
                        html.dark .fi-main,
                        html.dark .fi-layout,
                        html.dark .fi-sidebar,
                        html.dark .fi-topbar nav,
                        html.dark .fi-section,
                        html.dark .fi-ta,
                        html.dark .fi-wi-stats-overview-stat,
                        html.dark .fi-fo-field-wrp,
                        html.dark .fi-modal-window {
                            background-color: #0d1117 !important;
                            color: #e5e7eb !important;
                            border-color: #30363d !important;
                        }

                        html.dark .fi-input,
                        html.dark input,
                        html.dark textarea,
                        html.dark select,
                        html.dark .fi-select-input,
                        html.dark .fi-dropdown-panel {
                            background-color: #161b22 !important;
                            color: #f3f4f6 !important;
                            border-color: #30363d !important;
                        }

                        html.dark .fi-ta-row,
                        html.dark .fi-ta-header-cell,
                        html.dark .fi-sidebar-item a,
                        html.dark .fi-sidebar-group-button {
                            color: #e5e7eb !important;
                        }

                        .fi-sidebar,
                        .fi-section,
                        .fi-ta,
                        .fi-modal-window {
                            border-width: 1px !important;
                            box-shadow: none !important;
                        }

                        .fi-sidebar-header {
                            min-height: 5rem;
                            border-bottom: 1px solid var(--pusms-border);
                        }

                        .pusms-sidebar-wordmark {
                            display: block;
                            margin: .25rem 1rem 1rem;
                            color: #082f63;
                            font-weight: 900;
                            letter-spacing: .02em;
                        }

                        html.dark .pusms-sidebar-wordmark {
                            color: #f3f4f6;
                        }

                        .fi-logo img {
                            width: 190px;
                            max-width: 100%;
                            height: auto !important;
                            object-fit: contain;
                        }

                        .fi-topbar .fi-logo {
                            display: none !important;
                        }

                        .fi-sidebar-item-active a,
                        .fi-sidebar-item a:hover {
                            background-color: #eff6ff !important;
                            border: 1px solid #bfdbfe !important;
                        }

                        .fi-btn {
                            box-shadow: none !important;
                        }

                        .pusms-topbar-brand {
                            display: inline-flex;
                            align-items: center;
                            gap: .7rem;
                            min-width: 0;
                            padding-inline: .5rem 1rem;
                            border-right: 1px solid #cbd5e1;
                            background: #ffffff;
                        }

                        html.dark .pusms-topbar-brand {
                            background: #0d1117;
                            border-color: #30363d;
                        }

                        .pusms-topbar-brand img {
                            width: 86px;
                            height: 72px;
                            object-fit: contain;
                            flex: none;
                        }

                        .pusms-topbar-brand span {
                            color: #0f172a;
                            font-weight: 800;
                            font-size: .95rem;
                            line-height: 1.15;
                            white-space: normal;
                            max-width: 620px;
                        }

                        .pusms-topbar-brand .pusms-short-name {
                            display: none;
                        }

                        .fi-main,
                        .fi-page,
                        .fi-ta-ctn {
                            max-width: none !important;
                        }

                        .fi-header {
                            display: grid !important;
                            grid-template-columns: minmax(0, 1fr) auto !important;
                            align-items: start !important;
                            column-gap: 1rem !important;
                            row-gap: .5rem !important;
                        }

                        .fi-header-actions-ctn {
                            display: grid !important;
                            grid-template-columns: auto !important;
                            grid-auto-rows: min-content !important;
                            row-gap: .55rem !important;
                            justify-items: end !important;
                            align-items: start !important;
                            min-width: auto !important;
                            justify-content: flex-end !important;
                        }

                        .fi-header-actions-ctn > :not(.pusms-page-title-logo) {
                            grid-row: 2 !important;
                        }

                        .pusms-page-title-logo {
                            width: 86px;
                            height: 72px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 0 0 auto;
                            grid-row: 1 !important;
                            justify-self: end !important;
                        }

                        .pusms-page-title-logo img {
                            width: 100%;
                            height: 100%;
                            object-fit: contain;
                        }

                        .fi-topbar-open-sidebar-btn svg,
                        .fi-topbar-close-sidebar-btn svg,
                        .fi-topbar-open-collapse-sidebar-btn svg,
                        .fi-topbar-close-collapse-sidebar-btn svg {
                            width: 2rem !important;
                            height: 2rem !important;
                            color: #020617 !important;
                        }

                        html.dark .fi-topbar-open-sidebar-btn svg,
                        html.dark .fi-topbar-close-sidebar-btn svg,
                        html.dark .fi-topbar-open-collapse-sidebar-btn svg,
                        html.dark .fi-topbar-close-collapse-sidebar-btn svg {
                            color: #f8fafc !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) {
                            width: 7rem !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
                            width: 7rem !important;
                            padding-inline: .45rem !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav-groups {
                            display: grid !important;
                            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                            gap: .45rem !important;
                            align-items: start !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group,
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-items {
                            display: contents !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item,
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn {
                            width: 2.65rem !important;
                            height: 2.65rem !important;
                            min-width: 2.65rem !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item a,
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            padding: 0 !important;
                            border-radius: .5rem !important;
                        }

                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-label,
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-label,
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-btn,
                        .fi-sidebar:not(.fi-sidebar-open) .pusms-sidebar-wordmark {
                            display: none !important;
                        }

                        .fi-ta-content,
                        .fi-ta-table {
                            width: 100% !important;
                        }

                        .fi-ta-content {
                            overflow-x: auto !important;
                        }

                        .fi-ta-table {
                            min-width: 100% !important;
                            width: max-content !important;
                            table-layout: auto !important;
                        }

                        .fi-ta-cell,
                        .fi-ta-header-cell {
                            white-space: nowrap !important;
                            overflow-wrap: normal !important;
                            word-break: normal !important;
                            vertical-align: top !important;
                        }

                        .fi-ta-cell *,
                        .fi-ta-header-cell * {
                            white-space: nowrap !important;
                        }

                        @media (max-width: 720px) {
                            .pusms-topbar-brand {
                                gap: .35rem;
                                padding-inline: .35rem .55rem;
                            }

                            .pusms-topbar-brand img {
                                width: 54px;
                                height: 48px;
                            }

                            .pusms-topbar-brand .pusms-full-name {
                                display: none;
                            }

                            .pusms-topbar-brand .pusms-short-name {
                                display: inline;
                                font-size: .95rem;
                                max-width: none;
                                white-space: nowrap;
                            }

                            .fi-global-search-field {
                                min-width: 160px !important;
                                max-width: 220px !important;
                            }

                            .fi-header-actions-ctn {
                                min-width: 0;
                                width: auto;
                                justify-content: flex-end !important;
                                margin-top: 0;
                            }

                            .pusms-page-title-logo {
                                width: 54px;
                                height: 48px;
                            }
                        }
                    </style>
                HTML),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_BEFORE,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <div class="pusms-topbar-brand">
                        <img src="/images/pentvars-3d.png" alt="Pentecost University">
                        <span class="pusms-full-name">Pentecost University Scholarship Management System (PUSMS)</span>
                        <span class="pusms-short-name">PUSMS</span>
                    </div>
                HTML),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_AFTER,
                fn (): HtmlString => new HtmlString((session()->has('impersonated_by_user_id')
                    ? <<<'HTML'
                        <div style="padding:8px 16px; border-bottom:1px solid #f59e0b; background:#fffbeb; color:#92400e; font-weight:800; display:flex; justify-content:center; gap:12px;">
                            <span>You are logged in as another user.</span>
                            <a href="/admin/impersonation/stop" style="color:#005eea; text-decoration:underline;">Return to Super Admin</a>
                        </div>
                    HTML
                    : '').$this->assistantMarkup()),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): HtmlString => new HtmlString($this->notificationBellMarkup()),
            )
            ->renderHook(
                PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <div class="pusms-page-title-logo" aria-hidden="true">
                        <img src="/images/pentvars-3d.png" alt="">
                    </div>
                HTML),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_LOGO_AFTER,
                fn (): HtmlString => new HtmlString('<span class="pusms-sidebar-wordmark">PUSMS</span>'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                GeneratedLetters::class,
                GmailInbox::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PusmsStatsOverview::class,
                DashboardReadiness::class,
                ScholarshipGrowthChart::class,
                StudentsByProgrammeChart::class,
                StudentsByLevelChart::class,
                StudentsBySchoolChart::class,
                StudentsByRegionChart::class,
                AccountWidget::class,
                ScholarshipSpendChart::class,
                ScholarshipSemesterSpendChart::class,
                ScholarshipCoverageChart::class,
                StudentMovementChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
