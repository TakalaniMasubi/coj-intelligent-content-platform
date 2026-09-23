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
                max-width: 1200px; margin: 0 auto; padding: 16px 24px;
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
            .brand-name { font-size: 18px; font-weight: 800; letter-spacing: 0.02em; }
            .brand-sub { font-size: 12px; color: #b7bcc4; max-width: 420px; }
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

            .orgcards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 4px; }
            @media (max-width: 900px) { .orgcards { grid-template-columns: 1fr; } }
            .orgcard { border: 1px solid var(--line); border-radius: 14px; padding: 18px 18px 16px; background: #fff; }
            .orgcard .top { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
            .orgcard .rank { font-size: 22px; font-weight: 800; color: var(--gold-deep); }
            .orgcard h3 { margin: 0; font-size: 15.5px; line-height: 1.25; }
            .orgcard .meta { font-size: 12px; color: var(--ink-soft); margin-top: 3px; }
            .orgcard .chip {
                display: inline-block; font-size: 10px; font-weight: 800; letter-spacing: 0.05em;
                border-radius: 6px; padding: 3px 8px; text-transform: uppercase; color: #fff; margin-right: 4px;
            }
            .chip.g { background: var(--ok); }
            .chip.a { background: var(--warn); }
            .chip.x { background: #6f7378; }
            .chip.gold { background: var(--gold); color: #3a2e00; }
            .orgcard .desc { font-size: 13px; color: var(--ink-soft); line-height: 1.55; margin: 8px 0; }
            .orgcard .why { border-left: 3px solid #0f5c3a; background: #f3f6f2; border-radius: 0 8px 8px 0; padding: 9px 12px; font-size: 12px; color: #33403a; margin: 8px 0; }
            .orgcard .why b { font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; }
            .orgcard .why ul { margin: 4px 0 0; padding-left: 16px; }
            .orgcard .arts { margin-top: 10px; }
            .orgcard .art { border-top: 1px dashed var(--line); padding: 9px 0; }
            .orgcard .art .t { font-weight: 700; font-size: 13px; }
            .orgcard .art .s { font-size: 11.5px; color: var(--ink-faint); margin-top: 2px; }
            .orgcard .art .sum { font-size: 12.5px; color: var(--ink-soft); margin-top: 3px; }
            .avl { display: flex; align-items: center; gap: 10px; }
            .avl .ava {
                width: 34px; height: 34px; border-radius: 50%; color: #fff; font-weight: 700; font-size: 13px;
                display: flex; align-items: center; justify-content: center; flex: 0 0 auto;
            }

            .preview { margin-top: 22px; }
            .preview .shell { background: #cbd0d8; border: 1px solid var(--line); border-radius: 14px; padding: 26px 14px; overflow: auto; }
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
                        <div class="brand-name">CoJ-MIP</div>
                        <div class="brand-sub">Newsletter Studio — City of Johannesburg Intelligence Platform</div>
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
                <div class="step"><div class="no">Step 1</div><div class="tt">Choose a topic</div><div class="ds">Pick the topic your newsletter should spotlight.</div></div>
                <div class="step"><div class="no">Step 2</div><div class="tt">Top-3 organisations</div><div class="ds">Reuses the platform's standard relevance pipeline.</div></div>
                <div class="step"><div class="no">Step 3</div><div class="tt">Layout &amp; PDF</div><div class="ds">Switch layouts — no re-selection — then generate a 3-page PDF.</div></div>
            </div>

            @if ($composition === null)
                <div class="panel">
                    <h2><span class="k">Step 1</span>Compose a newsletter</h2>
                    <form method="POST" action="{{ route('newsletter.compose') }}" class="topic-form">
                        @csrf
                        <input type="text" name="topic" placeholder="e.g. waste management" value="{{ old('topic') }}" maxlength="120" required>
                        <button type="submit" class="btn">Compose Newsletter</button>
                    </form>
                    @error('topic')
                        <p style="color:var(--bad);font-size:13px;margin:10px 0 0;">{{ $message }}</p>
                    @enderror
                    <div class="examples">
                        Try: <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();parentNode.parentNode.querySelector('input[name=topic]').value='waste management';">waste management</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();parentNode.parentNode.querySelector('input[name=topic]').value='water services';">water services</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();parentNode.parentNode.querySelector('input[name=topic]').value='economic development';">economic development</a>
                        <a href="{{ route('newsletter.studio') }}" onclick="event.preventDefault();parentNode.parentNode.querySelector('input[name=topic]').value='public safety';">public safety</a>
                    </div>
                    <p style="font-size:12.5px;color:var(--ink-faint);margin:16px 0 0;">
                        Composition is fully deterministic and grounded in the curated CoJ repository.
                        No AI is used and no content is fabricated.
                    </p>
                </div>
            @else
                @if ($composition->isSuccess())
                    <div class="notice ok">
                        <span class="tag">Step 2 — Done</span>
                        <p style="margin:0;">{{ $composition->message }} Switching layouts below re-renders the same
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

                <div class="panel" style="padding-bottom:18px;">
                    <h2><span class="k">Step 3</span>Layout &amp; export</h2>
                    <div class="layoutbar">
                        @foreach ($layouts as $code => $layout)
                            <a class="lp{{ $code === $layoutCode ? ' active' : '' }}"
                               href="{{ route('newsletter.layout', $code) }}"
                               title="{{ $layout['blurb'] }}">
                                {{ $layout['name'] }}
                                <small>{{ $layout['tag'] }}</small>
                            </a>
                        @endforeach
                        <a class="btn btn-gold" href="{{ route('newsletter.pdf') }}">Download PDF (3 pages)</a>
                        <a class="btn btn-ghost" href="{{ route('newsletter.studio') }}">Create another</a>
                        <span class="note">Layout: {{ $currentLayout['name'] ?? '' }} — {{ $currentLayout['blurb'] ?? '' }}</span>
                    </div>
                </div>

                <div class="panel">
                    <h2><span class="k">Step 2</span>Organisations &amp; articles</h2>
                    <div class="orgcards">
                        @foreach ($composition->organisations() as $organisation)
                            <div class="orgcard">
                                <div class="top">
                                    <div class="rank">{{ str_pad((string) $organisation->rank, 2, '0', STR_PAD_LEFT) }}</div>
                                    @include('newsletter.partials.media-asset', [
                                        'asset' => $organisation->logo,
                                        'context' => 'web',
                                        'size' => 36,
                                    ])
                                    <div>
                                        <h3>{{ $organisation->name }}</h3>
                                        <div class="meta">
                                            <span class="chip {{ $organisation->verification === 'verified' ? 'g' : ($organisation->verification === 'unverified' ? 'x' : 'a') }}">
                                                {{ $organisation->verification === 'verified' ? 'Verified' : ($organisation->verification === 'unverified' ? 'Unverified' : 'Pending') }}
                                            </span>
                                            <span class="chip gold">Score {{ $organisation->score }}</span>
                                            {{ ucfirst($organisation->type) }}
                                        </div>
                                    </div>
                                </div>
                                @if ($organisation->description)
                                    <div class="desc">{{ $organisation->description }}</div>
                                @endif
                                @if ($organisation->reasons !== [])
                                    <div class="why"><b>Why selected</b>
                                        <ul>
                                            @foreach ($organisation->reasons as $reason)
                                                <li>{{ $reason }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="arts">
                                    @if (count($organisation->articles) === 0)
                                        <div style="font-style:italic;color:var(--ink-faint);font-size:12.5px;">
                                            No relevant published article is currently available in the curated repository for this organisation.
                                        </div>
                                    @else
                                        @foreach ($organisation->articles as $article)
                                            <div class="art">
                                                <div class="t">{{ $article->title }}</div>
                                                <div class="s">
                                                    {{ $article->sourceName }}
                                                    @if ($article->status === 'verified')
                                                        <span class="chip g">Verified</span>
                                                    @else
                                                        <span class="chip a">Unverified</span>
                                                    @endif
                                                    {{ $article->retrievedAt?->format('d M Y') }}
                                                </div>
                                                @if ($article->summary)
                                                    <div class="sum">{{ $article->summary }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="preview">
                    <h2 style="font-size:17px;margin:0 0 10px;">Live preview — layout <strong>{{ $currentLayout['name'] ?? $layoutCode }}</strong></h2>
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
                </div>
            @endif
        </main>

        <div class="footer">
            <span>CoJ Intelligent Content &amp; Municipal Intelligence Platform — Newsletter Studio</span>
            <span>{{ config('cojmip.ai.provider', 'local') }} intelligence provider · no AI key required</span>
        </div>
    </body>
</html>