<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>CoJ-MIP — Newsletter Studio</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/coj-initials.jpg') }}">
        <style>
            :root {
                --ink: #101418;
                --ink-soft: #4b5563;
                --ink-faint: #8a919b;
                --paper: #eef1f4;
                --card: #ffffff;
                --line: #dfe3e8;
                --gold: #f5b800;
                --gold-deep: #c79500;
                --ok: #137333;
                --ok-soft: #e7f6ec;
                --warn: #9a6b00;
                --warn-soft: #fdf2d7;
                --bad: #b3261e;
                --bad-soft: #fbe6e3;
            }
            * { box-sizing: border-box; }
            html { scroll-behavior: smooth; }
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
                background: var(--paper);
                color: var(--ink);
                line-height: 1.5;
                font-size: 15px;
            }
            a { color: inherit; }

            .topbar { background: #0e1115; color: #fff; border-bottom: 4px solid var(--gold); }
            .topbar-inner {
                max-width: 1200px; margin: 0 auto; padding: 14px 24px;
                display: flex; align-items: center; justify-content: space-between;
                gap: 18px; flex-wrap: wrap;
            }
            .brand { display: flex; align-items: center; gap: 14px; }
            .brand-mark {
                height: 58px; padding: 6px 12px; border-radius: 11px;
                background: #fff; display: flex; align-items: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.28);
            }
            .brand-mark img { height: 46px; width: auto; display: block; }
            .brand-name { font-size: 17px; font-weight: 800; letter-spacing: 0.04em; }
            .brand-sub { font-size: 12px; color: #b7bcc4; max-width: 460px; }
            .nav { display: flex; gap: 6px; flex-wrap: wrap; }
            .nav a {
                display: inline-flex; align-items: center; gap: 7px;
                color: #d7dbe1; text-decoration: none; font-size: 13px; font-weight: 600;
                padding: 8px 13px; border-radius: 8px;
            }
            .nav a:hover, .nav a.active { background: rgba(245, 184, 0, 0.14); color: #fff; }
            .nav a.active { box-shadow: inset 0 -2px 0 var(--gold); }
            .nav .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold); display: inline-block; }

            .main { max-width: 1200px; margin: 0 auto; padding: 30px 24px 80px; }

            .stepbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 26px; }
            .step {
                flex: 1 1 200px; border: 1px solid var(--line); background: var(--card);
                border-radius: 12px; padding: 13px 16px;
            }
            .step .no { font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: var(--gold-deep); text-transform: uppercase; }
            .step .tt { font-weight: 700; margin-top: 2px; font-size: 14px; }
            .step .ds { font-size: 12.5px; color: var(--ink-faint); margin-top: 2px; }

            .panel {
                background: var(--card); border: 1px solid var(--line); border-radius: 14px;
                padding: 22px 24px; margin-bottom: 22px;
            }
            .panel h2 { margin: 0 0 12px; font-size: 17px; letter-spacing: 0.01em; }
            .panel h2 .k { color: var(--gold-deep); font-size: 12px; letter-spacing: 0.1em; margin-right: 8px; }

            .topic-form { display: flex; gap: 12px; flex-wrap: wrap; }
            .topic-form input[type=text] {
                flex: 1 1 320px; padding: 12px 14px; border: 1px solid #cfd5db; border-radius: 10px;
                font-size: 15px;
            }
            .btn {
                display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer;
                font-weight: 700; font-size: 14px; padding: 12px 18px; border-radius: 10px;
                text-decoration: none; background: #0f5c3a; color: #fff;
            }
            .btn:hover { filter: brightness(1.06); }
            .btn-gold { background: var(--gold); color: #3a2e00; }
            .btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }
            .btn-big { font-size: 16px; padding: 15px 26px; border-radius: 12px; box-shadow: 0 6px 18px rgba(199, 149, 0, 0.35); }

            .examples { margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; font-size: 12.5px; color: var(--ink-faint); }
            .examples a {
                background: var(--paper); border: 1px solid var(--line); border-radius: 999px;
                padding: 4px 12px; text-decoration: none; color: var(--ink-soft); font-weight: 600;
            }
            .examples a:hover { border-color: var(--gold-deep); color: var(--ink); }

            .notice { border-radius: 12px; padding: 14px 18px; display: flex; gap: 14px; align-items: flex-start; margin-bottom: 20px; font-size: 13.5px; }
            .notice.ok { background: var(--ok-soft); border: 1px solid #bfe3c9; color: #0c5629; }
            .notice.warn { background: var(--warn-soft); border: 1px solid #ead28a; color: #5c4500; }
            .notice .tag { flex: 0 0 auto; background: var(--warn); color: #fff; font-size: 11px; font-weight: 800; letter-spacing: 0.06em; border-radius: 6px; padding: 4px 9px; text-transform: uppercase; }
            .notice.ok .tag { background: var(--ok); }

            .layoutbar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
            .layoutbar .lp {
                text-decoration: none; border: 1px solid var(--line); border-radius: 10px;
                padding: 10px 14px; background: var(--card); font-size: 13px; font-weight: 700; color: var(--ink);
            }
            .layoutbar .lp small { display: block; font-weight: 500; font-size: 11.5px; color: var(--ink-faint); margin-top: 2px; }
            .layoutbar .lp.active { background: #0f5c3a; border-color: #0f5c3a; color: #fff; }
            .layoutbar .lp.active small { color: #cfe6d8; }
            .layoutbar .lp:hover { border-color: var(--gold-deep); }
            .layoutbar .lp.active:hover { border-color: #0f5c3a; }
            .layoutbar .note { font-size: 12.5px; color: var(--ink-faint); max-width: 300px; }

            /* Step 2 — your selected organisations (publication strip) */
            .nsel-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 4px; }
            @media (max-width: 900px) { .nsel-strip { grid-template-columns: 1fr; } }
            .nsel-org {
                border: 1px solid var(--line); border-radius: 14px; overflow: hidden;
                background: #fff; display: flex; flex-direction: column;
            }
            .nsel-org-head {
                border-left: 5px solid var(--gold-deep); background: #fffdf4;
                padding: 14px 14px 11px; display: flex; gap: 12px; align-items: center;
            }
            .nsel-rank { font-size: 24px; font-weight: 800; color: var(--gold-deep); line-height: 1; }
            .nsel-logo { flex: 0 0 auto; }
            .nsel-org-title h3 { margin: 0; font-size: 15.5px; line-height: 1.25; }
            .nsel-org-title p { margin: 3px 0 0; font-size: 11.5px; color: var(--ink-soft); }
            .nsel-mandate, .nsel-desc {
                margin: 12px 14px 0; font-size: 12.5px; color: var(--ink-soft); line-height: 1.55;
            }
            .nsel-mandate { font-style: italic; }
            .nsel-art {
                margin: auto 14px 0; padding: 12px 0 14px; border-top: 1px dashed var(--line);
            }
            .nsel-art-label { font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold-deep); display: block; margin-bottom: 3px; }
            .nsel-art-title { font-size: 12.5px; font-weight: 600; line-height: 1.45; }
            .nsel-art-none .nsel-art-title { font-style: italic; color: var(--ink-faint); font-weight: 500; }

            .exportbar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }

            .preview { margin-top: 22px; }
            .preview .shell { background: #c2c8d1; border: 1px solid var(--line); border-radius: 14px; padding: 26px 6px; overflow: auto; }
            .preview .zoom { zoom: 0.78; }
            .nlsheet-wrap { width: 986px; margin: 0 auto; background: #fff; box-shadow: 0 18px 44px rgba(16, 20, 24, 0.22); }

            .footer { max-width: 1200px; margin: 0 auto; padding: 0 24px 46px; color: var(--ink-faint); font-size: 12.5px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        </style>
        <style>
            @include('newsletter.partials.sheet-styles')
        </style>
    </head>
    <body>
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-mark"><img src="{{ asset('images/coj-logo.png') }}" alt="City of Johannesburg"></div>
                    <div>
                        <div class="brand-name">CoJ Municipal Intelligence</div>
                        <div class="brand-sub">Newsletter Studio — compose a real, fact-checked municipal newsletter from the curated City repository</div>
                    </div>
                </div>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('intelligence.search') }}">Intelligence Search</a>
                    <a href="{{ route('selection.history') }}">Selection History</a>
                    <a href="{{ route('newsletter.studio') }}" class="active"><span class="dot"></span>Newsletter</a>
                </nav>
            </div>
        </header>

        <main class="main">
            <div class="stepbar">
                <div class="step"><div class="no">Step 1</div><div class="tt">Choose your topic</div><div class="ds">Type a topic and compose the edition.</div></div>
                <div class="step"><div class="no">Step 2</div><div class="tt">Your selected organisations</div><div class="ds">Exactly three organisations are picked by the standard relevance pipeline.</div></div>
                <div class="step"><div class="no">Step 3</div><div class="tt">Choose your newsletter layout</div><div class="ds">Four editorial layouts — switching never re-selects organisations.</div></div>
                <div class="step"><div class="no">Step 4</div><div class="tt">Newsletter preview &amp; PDF</div><div class="ds">Preview all three pages, then generate a 3-page PDF.</div></div>
            </div>

            @if ($composition === null)
                <div class="panel">
                    <h2><span class="k">Step 1</span>Choose your topic</h2>
                    <form method="POST" action="{{ route('newsletter.compose') }}" class="topic-form">
                        @csrf
                        <input type="text" name="topic" placeholder="e.g. waste management" value="{{ old('topic') }}" maxlength="120" required>
                        <button type="submit" class="btn">Generate Newsletter</button>
                    </form>
                    @error('topic')
                        <p style="color:var(--bad);font-size:13px;margin:10px 0 0;">{{ $message }}</p>
                    @enderror
                    <div class="examples">
                        Try: <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();this.parentNode.parentNode.querySelector('input[name=topic]').value='waste management';">waste management</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();this.parentNode.parentNode.querySelector('input[name=topic]').value='water services';">water services</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();this.parentNode.parentNode.querySelector('input[name=topic]').value='economic development';">economic development</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();this.parentNode.parentNode.querySelector('input[name=topic]').value='public safety';">public safety</a>
                    </div>
                    <p style="font-size:12.5px;color:var(--ink-faint);margin:16px 0 0;">
                        Composition is fully deterministic and grounded in the curated CoJ repository.
                        No AI is used and no content is fabricated.
                    </p>
                </div>
            @else
                @if ($composition->isSuccess())
                    <div class="notice ok">
                        <span class="tag">Ready</span>
                        <p style="margin:0;">Composed for the topic &ldquo;{{ $composition->topic }}&rdquo; &mdash;
                            {{ $composition->message }} Switching layouts below re-renders the same
                            composed content — it never re-selects organisations.</p>
                    </div>
                @else
                    <div class="notice warn">
                        <span class="tag">Insufficient results</span>
                        <p style="margin:0;">{{ $composition->message }}</p>
                    </div>
                @endif

                @if ($pdfStatus)
                    <div class="notice {{ $pdfStatus['valid'] ? 'ok' : 'warn' }}">
                        <span class="tag">{{ $pdfStatus['valid'] ? 'PDF validated' : 'PDF status' }}</span>
                        <p style="margin:0;">{{ $pdfStatus['message'] }}
                            @isset($pdfStatus['layout'])
                                &nbsp;(layout: {{ $pdfStatus['layout'] }})
                            @endisset
                        </p>
                    </div>
                @endif

                @if (! $composition->isSuccess())
                    <div class="panel">
                        <h2><span class="k">Step 1</span>Choose your topic</h2>
                        <form method="POST" action="{{ route('newsletter.compose') }}" class="topic-form">
                            @csrf
                            <input type="text" name="topic" placeholder="e.g. waste management" value="{{ $composition->topic }}" maxlength="120" required>
                            <button type="submit" class="btn">Generate Newsletter</button>
                        </form>
                        <p style="font-size:12.5px;color:var(--ink-faint);margin:14px 0 0;">
                            The current topic produced no reliable matches in the curated repository.
                            Try a different topic — the newsletter never invents organisations or articles.
                        </p>
                    </div>
                @else
                    <div class="panel">
                        <h2><span class="k">Step 2</span>Your selected organisations</h2>
                        @include('newsletter.partials.organisation-overview', [
                            'organisations' => $composition->organisations(),
                        ])
                    </div>

                    <div class="panel" style="padding-bottom:18px;">
                        <h2><span class="k">Step 3</span>Choose your newsletter layout</h2>
                        <div class="layoutbar">
                            @foreach ($layouts as $code => $layout)
                                <a class="lp{{ $code === $layoutCode ? ' active' : '' }}"
                                   href="{{ route('newsletter.layout', $code) }}"
                                   title="{{ $layout['blurb'] }}">
                                    {{ $layout['name'] }}
                                    <small>{{ $layout['tag'] }}</small>
                                </a>
                            @endforeach
                            <span class="note">{{ $currentLayout['name'] ?? '' }} — {{ $currentLayout['blurb'] ?? '' }}</span>
                        </div>
                    </div>
                @endif

                <div class="preview">
                    <h2 style="font-size:17px;margin:0 0 10px;"><span style="color:var(--gold-deep);font-size:12px;letter-spacing:.1em;margin-right:8px;">Step 4</span>Newsletter preview — layout <strong>{{ $currentLayout['name'] ?? $layoutCode }}</strong></h2>
                    <div class="shell">
                        <div class="zoom">
                            <div class="nlsheet-wrap">
                                @include('newsletter.sheet', [
                                    'composition' => $composition,
                                    'layout' => $layoutCode,
                                    'context' => 'web',
                                    'density' => 0,
                                    'masthead' => $masthead,
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="exportbar" style="margin-top:20px;">
                        <a class="btn btn-gold btn-big" href="{{ route('newsletter.pdf') }}">Generate &amp; Download PDF</a>
                        <a class="btn btn-ghost" href="{{ route('newsletter.reset') }}">Regenerate Newsletter</a>
                        <span style="font-size:12.5px;color:var(--ink-faint);">
                            The PDF mirrors this preview, exactly three pages, one organisation per page.
                            Your history is only recorded once a PDF is successfully generated.
                        </span>
                    </div>
                </div>
            @endif
        </main>

        <div class="footer">
            <span>CoJ Intelligent Content &amp; Municipal Intelligence Platform — Newsletter Studio</span>
            <span>{{ config('cojmip.ai.provider', 'local') }} intelligence provider · no AI key required</span>
        </div>
    </body>
</html>