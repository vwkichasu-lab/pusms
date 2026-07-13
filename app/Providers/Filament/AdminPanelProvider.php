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
use App\Http\Middleware\LogoutInactiveUser;
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

    private function preLoginLoaderMarkup(): string
    {
        if (! request()->is('admin/login') || auth()->check()) {
            return '';
        }

        return <<<'HTML'
            <div class="pusms-login-loader" id="pusmsLoginLoader" aria-label="Loading Pentecost University Scholarship System">
                <div class="pusms-login-loader-inner">
                    <div class="pusms-typing-mark" aria-label="UNIVERSITY A+">
                        <span class="university">UNIVERSITY</span>
                        <span class="grade">A+</span>
                    </div>
                    <div class="pusms-loader-stage" aria-hidden="true">
                        <div class="pusms-loader-ring"></div>
                        <span class="pusms-loader-spark one"></span>
                        <span class="pusms-loader-spark two"></span>
                        <span class="pusms-loader-spark three"></span>
                        <img src="/images/pentvars-3d.png" alt="">
                    </div>
                    <h1>Scholarship In Pentecost University</h1>
                    <div class="pusms-loader-bar"><span></span></div>
                    <button type="button" class="pusms-loader-start" id="pusmsLoginStart">Get Started</button>
                </div>
            </div>
            <style>
                .pusms-login-loader {
                    position: fixed;
                    inset: 0;
                    z-index: 99999;
                    display: grid;
                    place-items: center;
                    background: #ffffff;
                    color: #020617;
                    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    transition: opacity .45s ease, visibility .45s ease;
                }

                .pusms-login-loader.is-done {
                    opacity: 0;
                    visibility: hidden;
                }

                .pusms-login-loader-inner {
                    width: min(520px, calc(100vw - 48px));
                    display: grid;
                    justify-items: center;
                    gap: 22px;
                    text-align: center;
                }

                .pusms-loader-stage {
                    width: 190px;
                    height: 190px;
                    position: relative;
                    display: grid;
                    place-items: center;
                }

                .pusms-typing-mark {
                    display: inline-flex;
                    align-items: center;
                    gap: .4rem;
                    width: 14ch;
                    overflow: hidden;
                    white-space: nowrap;
                    font-size: clamp(1.15rem, 4vw, 1.7rem);
                    font-weight: 950;
                    letter-spacing: .02em;
                    position: relative;
                    animation: pusmsTypingMark 5.6s steps(14, end) .25s infinite;
                }

                .pusms-typing-mark::after {
                    content: "_";
                    position: absolute;
                    right: -1.1ch;
                    bottom: -.08em;
                    color: #f3b51b;
                    animation: pusmsTypingCaret .75s step-end infinite;
                }

                .pusms-typing-mark .university {
                    color: #053a82;
                }

                .pusms-typing-mark .grade {
                    color: #f3b51b;
                }

                .pusms-loader-stage img {
                    width: 150px;
                    filter: drop-shadow(0 12px 22px rgba(15, 23, 42, .16));
                    animation: pusmsAssemble 2.8s cubic-bezier(.16, 1, .3, 1) forwards;
                    transform-origin: center;
                }

                .pusms-loader-ring {
                    position: absolute;
                    inset: 8px;
                    border: 2px solid #f3b51b;
                    border-radius: 28px;
                    background: #ffffff;
                    box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
                    animation: pusmsRingSettle 2.8s ease forwards;
                }

                .pusms-loader-spark {
                    position: absolute;
                    width: 10px;
                    height: 10px;
                    border-radius: 999px;
                    background: #f3b51b;
                    opacity: 0;
                    animation: pusmsSparkBlink 1.25s ease-in-out infinite;
                }

                .pusms-loader-spark.one { top: 16px; left: 44px; }
                .pusms-loader-spark.two { right: 30px; top: 58px; animation-delay: .25s; }
                .pusms-loader-spark.three { left: 28px; bottom: 44px; animation-delay: .5s; }

                .pusms-login-loader h1 {
                    margin: 0;
                    color: #053a82;
                    font-size: clamp(1.35rem, 4vw, 2rem);
                    line-height: 1.15;
                    font-weight: 900;
                    opacity: 0;
                    transform: translateY(10px);
                    animation: pusmsTitleIn .75s ease 2.2s forwards;
                }

                .pusms-loader-bar {
                    width: min(320px, 80vw);
                    height: 8px;
                    overflow: hidden;
                    border: 1px solid #dbe3ee;
                    border-radius: 999px;
                    background: #f8fafc;
                    opacity: 0;
                    animation: pusmsTitleIn .4s ease 2.45s forwards;
                }

                .pusms-loader-bar span {
                    display: block;
                    height: 100%;
                    width: 0;
                    border-radius: inherit;
                    background: linear-gradient(90deg, #053a82, #f3b51b);
                    animation: pusmsLoadBar 1.2s ease 2.45s forwards;
                }

                .pusms-loader-start {
                    opacity: 0;
                    transform: translateY(10px);
                    animation: pusmsTitleIn .75s ease 3s forwards;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 150px;
                    height: 48px;
                    padding: 0 22px;
                    border: 0;
                    border-radius: 8px;
                    background: #053a82;
                    color: #ffffff;
                    font-weight: 900;
                    cursor: pointer;
                    box-shadow: 0 10px 24px rgba(5, 58, 130, .22);
                }

                .pusms-loader-start:focus-visible {
                    outline: 3px solid #f3b51b;
                    outline-offset: 3px;
                }

                @keyframes pusmsAssemble {
                    0% { opacity: 0; transform: translateY(42px) scale(.62) rotate(-7deg); clip-path: inset(76% 24% 0 24%); }
                    40% { opacity: 1; transform: translateY(10px) scale(.82) rotate(0deg); clip-path: inset(38% 12% 0 12%); }
                    100% { opacity: 1; transform: translateY(0) scale(1) rotate(0deg); clip-path: inset(0 0 0 0); }
                }

                @keyframes pusmsRingSettle {
                    0% { opacity: 0; transform: scale(.72); }
                    100% { opacity: 1; transform: scale(1); }
                }

                @keyframes pusmsSparkBlink {
                    0%, 100% { opacity: 1; transform: scale(1); }
                    50% { opacity: .2; transform: scale(.65); }
                }

                @keyframes pusmsTitleIn {
                    to { opacity: 1; transform: translateY(0); }
                }

                @keyframes pusmsLoadBar {
                    to { width: 100%; }
                }

                @keyframes pusmsTypingMark {
                    0%, 12% { width: 0; }
                    42%, 62% { width: 14ch; }
                    92%, 100% { width: 0; }
                }

                @keyframes pusmsTypingCaret {
                    50% { opacity: 0; }
                }
            </style>
            <script>
                document.getElementById('pusmsLoginStart')?.addEventListener('click', () => {
                    document.getElementById('pusmsLoginLoader')?.classList.add('is-done');
                });
            </script>
        HTML;
    }

    private function assistantMarkup(): string
    {
        return '';
    }

    private function formDraftMarkup(): string
    {
        return <<<'HTML'
            <style>
                .pusms-clear-draft-row {
                    display: flex;
                    justify-content: flex-end;
                    margin-top: 1rem;
                    margin-bottom: 5.5rem;
                    padding-top: .75rem;
                    border-top: 1px solid #e2e8f0;
                }

                @media (min-width: 768px) {
                    .pusms-clear-draft-row {
                        padding-right: 6rem;
                    }
                }

                .pusms-clear-draft {
                    border: 1px solid #cbd5e1;
                    border-radius: .5rem;
                    background: #ffffff;
                    color: #082f63;
                    padding: 8px 12px;
                    font-weight: 800;
                    cursor: pointer;
                }

                html.dark .pusms-clear-draft {
                    background: #161b22;
                    border-color: #30363d;
                    color: #f8fafc;
                }
            </style>
            <script>
                (() => {
                    const ignoredTypes = new Set(['hidden', 'password', 'file', 'submit', 'button', 'reset', 'search']);
                    let isRestoring = false;

                    const forms = () => Array.from(document.querySelectorAll('main form'));

                    const storageKey = (form) => {
                        if (!form.dataset.pusmsDraftKey) {
                            const index = forms().indexOf(form);
                            form.dataset.pusmsDraftKey = 'pusms-form-draft:' + window.location.pathname + ':form-' + index;
                        }

                        return form.dataset.pusmsDraftKey;
                    };

                    const controls = (form) => Array.from(form.querySelectorAll('input, textarea, select'))
                        .filter((control) => {
                            const type = (control.getAttribute('type') || '').toLowerCase();
                            return !ignoredTypes.has(type)
                                && !control.disabled
                                && (control.name || control.id)
                                && !control.closest('[data-no-draft]');
                        });

                    const currentDraft = (form) => {
                        const draft = {};

                        controls(form).forEach((control) => {
                            const key = control.name || control.id;

                            if (control.type === 'checkbox') {
                                draft[key] ??= [];

                                if (control.checked) {
                                    draft[key].push(control.value);
                                }

                                return;
                            }

                            if (control.type === 'radio') {
                                if (control.checked) {
                                    draft[key] = control.value;
                                }

                                return;
                            }

                            if (control.multiple) {
                                draft[key] = Array.from(control.selectedOptions).map((option) => option.value);
                                return;
                            }

                            draft[key] = control.value;
                        });

                        return draft;
                    };

                    const toggleClearButton = (form) => {
                        const button = form.querySelector('[data-pusms-clear-draft]');
                        if (!button) return;

                        const raw = localStorage.getItem(storageKey(form));
                        button.closest('.pusms-clear-draft-row').hidden = !raw || raw === '{}';
                    };

                    const saveDraft = (form) => {
                        if (isRestoring) return;

                        const draft = currentDraft(form);
                        localStorage.setItem(storageKey(form), JSON.stringify(draft));
                        toggleClearButton(form);
                    };

                    const applyValue = (control, value) => {
                        if (control.type === 'checkbox') {
                            control.checked = Array.isArray(value) && value.includes(control.value);
                            return;
                        }

                        if (control.type === 'radio') {
                            control.checked = value === control.value;
                            return;
                        }

                        if (control.multiple && Array.isArray(value)) {
                            Array.from(control.options).forEach((option) => {
                                option.selected = value.includes(option.value);
                            });
                            return;
                        }

                        if (value !== undefined && value !== null) {
                            control.value = value;
                        }
                    };

                    const restoreDraft = (form) => {
                        const raw = localStorage.getItem(storageKey(form));
                        if (!raw) return;

                        let draft = {};
                        try {
                            draft = JSON.parse(raw);
                        } catch (error) {
                            localStorage.removeItem(storageKey(form));
                            return;
                        }

                        isRestoring = true;
                        controls(form).forEach((control) => {
                            const key = control.name || control.id;
                            if (!(key in draft)) return;

                            applyValue(control, draft[key]);
                            control.dispatchEvent(new Event('input', { bubbles: true }));
                            control.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        isRestoring = false;

                        toggleClearButton(form);
                    };

                    const clearFormDraft = (form) => {
                        localStorage.removeItem(storageKey(form));

                        controls(form).forEach((control) => {
                            if (control.type === 'checkbox' || control.type === 'radio') {
                                control.checked = false;
                            } else if (control.multiple) {
                                Array.from(control.options).forEach((option) => option.selected = false);
                            } else {
                                control.value = '';
                            }

                            control.dispatchEvent(new Event('input', { bubbles: true }));
                            control.dispatchEvent(new Event('change', { bubbles: true }));
                        });

                        toggleClearButton(form);
                    };

                    const ensureClearButton = (form) => {
                        if (form.dataset.pusmsDraftClearReady || controls(form).length === 0) return;
                        form.dataset.pusmsDraftClearReady = '1';

                        const row = document.createElement('div');
                        row.className = 'pusms-clear-draft-row';
                        row.hidden = true;

                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'pusms-clear-draft';
                        button.dataset.pusmsClearDraft = '1';
                        button.textContent = 'Clear saved form';
                        button.addEventListener('click', () => clearFormDraft(form));

                        row.appendChild(button);
                        form.appendChild(row);
                        toggleClearButton(form);
                    };

                    const bindDraftSaver = () => {
                        forms().forEach((form) => {
                            ensureClearButton(form);

                            controls(form).forEach((control) => {
                            if (control.dataset.pusmsDraftBound) return;
                            control.dataset.pusmsDraftBound = '1';
                                control.addEventListener('input', () => saveDraft(form));
                                control.addEventListener('change', () => saveDraft(form));
                            });
                        });
                    };

                    const boot = () => {
                        bindDraftSaver();
                        window.setTimeout(() => forms().forEach((form) => restoreDraft(form)), 250);
                    };

                    boot();
                    document.addEventListener('livewire:navigated', boot);
                    window.setInterval(bindDraftSaver, 1500);
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
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): HtmlString => new HtmlString($this->preLoginLoaderMarkup()),
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
                    : '').$this->assistantMarkup().$this->formDraftMarkup()),
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
                LogoutInactiveUser::class,
            ]);
    }
}
