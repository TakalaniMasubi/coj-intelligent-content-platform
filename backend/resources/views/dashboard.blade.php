<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>CoJ-MIP — Municipal Intelligence Dashboard</title>
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
                --chip-ink: #374151;
                --chip-soft: #eef1f4;
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

            /* ---------- Header ---------- */
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

            .main { max-width: 1200px; margin: 0 auto; padding: 26px 24px 72px; }

            /* ---------- Notice ---------- */
            .notice {
                background: var(--warn-soft); border: 1px solid #ead28a;
                color: #5c4500; border-radius: 12px; padding: 14px 18px;
                display: flex; gap: 14px; align-items: flex-start; margin-bottom: 22px;
            }
            .notice .tag {
                flex: 0 0 auto; background: var(--warn); color: #fff;
                font-size: 11px; font-weight: 800; letter-spacing: 0.06em;
                border-radius: 6px; padding: 4px 9px; text-transform: uppercase;
            }
            .notice p { margin: 2px 0 0; font-size: 13.5px; }

            /* ---------- Hero search ---------- */
            .hero {
                background: var(--card); border: 1px solid var(--line);
                border-radius: 14px; padding: 26px;
                box-shadow: 0 8px 30px rgba(16, 20, 24, 0.07);
                margin-bottom: 22px;
            }
            .hero h1 { font-size: 22px; font-weight: 800; margin: 0 0 6px; }
            .hero .hero-sub { font-size: 14px; color: var(--ink-soft); margin: 0 0 18px; }
            .search-row { display: flex; gap: 10px; flex-wrap: wrap; }
            #q {
                flex: 1 1 320px; font-size: 16px; padding: 14px 16px;
                border: 1px solid var(--line); border-radius: 10px;
                background: #fbfcfd; color: var(--ink);
            }
            #q:focus { outline: 2px solid var(--gold); border-color: var(--gold-deep); }
            .btn {
                border: none; border-radius: 10px; padding: 14px 22px;
                font-size: 15px; font-weight: 700; cursor: pointer;
            }
            .btn-gold { background: var(--gold); color: #0e1115; }
            .btn-gold:hover { background: var(--gold-deep); }
            .btn-dark { background: #0e1115; color: #fff; }
            .btn-dark:hover { background: #1c232b; }
            .chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
            .chip {
                background: #f0f1f3; border: 1px solid var(--line);
                border-radius: 999px; padding: 7px 14px; font-size: 13px;
                color: var(--ink-soft); cursor: pointer;
            }
            .chip:hover { background: #fff3cf; border-color: var(--gold-deep); color: var(--ink); }

            /* ---------- Panels / cards ---------- */
            .section {
                background: var(--card); border: 1px solid var(--line);
                border-radius: 14px; padding: 22px 24px; margin-bottom: 22px;
                box-shadow: 0 4px 16px rgba(16, 20, 24, 0.05);
            }
            .section-head {
                display: flex; align-items: center; justify-content: space-between;
                gap: 14px; flex-wrap: wrap; margin-bottom: 16px;
            }
            .section-title { font-size: 17px; font-weight: 800; margin: 0; }
            .section-sub { font-size: 13px; color: var(--ink-soft); margin: 2px 0 0; }
            .section-link { font-size: 13px; font-weight: 700; color: #1a56db; text-decoration: none; }
            .section-link:hover { text-decoration: underline; }

            /* ---------- Newsletter call-to-action ---------- */
            .newsletter-cta {
                background:
                    linear-gradient(120deg, #0e1115 0%, #18222c 55%, #24303c 100%);
                border-radius: 14px; padding: 20px 22px; margin-bottom: 22px;
                display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
                box-shadow: 0 8px 30px rgba(16, 20, 24, 0.16);
                border: 1px solid #2c3946;
            }
            .newsletter-cta .mark {
                flex: 0 0 auto; width: 52px; height: 52px; border-radius: 12px;
                background: var(--gold); color: #0e1115;
                display: flex; align-items: center; justify-content: center;
                font-size: 22px; font-weight: 800; letter-spacing: 0.02em;
                box-shadow: 0 4px 14px rgba(245, 184, 0, 0.35);
            }
            .newsletter-cta .txt { flex: 1 1 320px; min-width: 240px; color: #eef1f4; }
            .newsletter-cta .txt-title { font-size: 16px; font-weight: 800; margin: 0 0 4px; }
            .newsletter-cta .txt-sub { font-size: 13px; color: #b7c0ca; margin: 0; }
            .newsletter-cta .go { flex: 0 0 auto; }
            .newsletter-cta .go a {
                display: inline-flex; align-items: center; gap: 8px;
                background: var(--gold); color: #0e1115; font-weight: 800; font-size: 14px;
                padding: 11px 18px; border-radius: 10px; text-decoration: none;
            }
            .newsletter-cta .go a:hover { background: #ffd22e; }

            .metric-grid {
                display: grid; gap: 14px;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            .metric {
                background: #fbfcfd; border: 1px solid var(--line); border-radius: 12px;
                padding: 16px 18px;
            }
            .metric .metric-label {
                font-size: 12px; font-weight: 700; letter-spacing: 0.03em;
                color: var(--ink-soft); text-transform: uppercase; margin: 0 0 8px;
            }
            .metric .metric-value { font-size: 30px; font-weight: 800; line-height: 1; }
            .metric .metric-note { font-size: 12px; color: var(--ink-faint); margin: 8px 0 0; }

            .status-pill {
                display: inline-flex; align-items: center; gap: 8px;
                border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 800;
            }
            .status-healthy { background: var(--ok-soft); color: var(--ok); }
            .status-degraded { background: var(--warn-soft); color: var(--warn); }
            .status-error { background: var(--bad-soft); color: var(--bad); }
            .status-pill .dot { width: 9px; height: 9px; border-radius: 50%; background: currentColor; }

            .health-grid {
                display: grid; gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }
            .health-cell { border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; background: #fbfcfd; }
            .health-cell .h-label { font-size: 11px; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.03em; }
            .health-cell .h-value { font-size: 20px; font-weight: 800; margin-top: 4px; }
            .issues { margin: 16px 0 0; padding: 0; list-style: none; }
            .issues li {
                font-size: 13px; color: var(--bad); background: var(--bad-soft);
                border-radius: 8px; padding: 8px 12px; margin-bottom: 6px;
            }

            .grid-2 { display: grid; gap: 22px; grid-template-columns: 1fr 1fr; }
            @media (max-width: 980px) { .grid-2 { grid-template-columns: 1fr; } }

            .breakdown-row { display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid #eef0f2; }
            .breakdown-row:last-child { border-bottom: none; }
            .breakdown-label { flex: 0 0 160px; font-weight: 700; font-size: 14px; }
            .bar { flex: 1; height: 10px; background: #eceef0; border-radius: 999px; overflow: hidden; display: flex; }
            .bar .seg-verified { background: var(--ok); }
            .bar .seg-pending { background: var(--gold); }
            .bar .seg-unverified { background: var(--chip-ink); }
            .breakdown-meta { flex: 0 0 190px; text-align: right; font-size: 12.5px; color: var(--ink-soft); }
            .breakdown-meta strong { color: var(--ink); font-size: 15px; }

            .verification-grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
            .ver-card { border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; background: #fbfcfd; }
            .ver-card .ver-label { font-size: 12px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
            .ver-card .ver-count { font-size: 30px; font-weight: 800; margin: 6px 0; }
            .ver-card .ver-note { font-size: 13px; color: var(--ink-soft); margin: 0; }
            .ver-verified { color: var(--ok); }
            .ver-pending { color: var(--warn); }
            .ver-unverified { color: var(--chip-ink); }

            /* Selection preview */
            .preview-card { border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; margin-bottom: 12px; background: #fdfdfe; }
            .preview-head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
            .preview-rank {
                background: var(--gold); color: #0e1115; border-radius: 8px;
                width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
                font-weight: 800; font-size: 16px; flex: 0 0 auto;
            }
            .preview-name { font-size: 16px; font-weight: 800; margin: 0; }
            .preview-meta { margin-left: auto; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
            .score-orig { font-size: 14px; font-weight: 700; }
            .score-orig .struck { color: var(--ink-soft); text-decoration: line-through; }
            .score-arrow { color: var(--ink-soft); }
            .score-adj { font-size: 17px; font-weight: 800; }
            .penalty { font-size: 12px; font-weight: 700; border-radius: 999px; padding: 3px 10px; }
            .penalty-yes { background: var(--bad-soft); color: var(--bad); }
            .penalty-no { background: var(--ok-soft); color: var(--ok); }
            .why { margin: 12px 0 0; padding-left: 18px; font-size: 13.5px; }
            .why li { margin-bottom: 3px; }
            .preview-terms { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
            .term {
                background: #fff3cf; border: 1px solid #ecd78c; color: #5c4500;
                border-radius: 6px; padding: 2px 9px; font-size: 12px; font-weight: 600;
            }
            .empty { font-size: 14px; color: var(--ink-faint); padding: 18px 0; }

            /* History groups */
            .history-group { border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; background: #fdfdfe; }
            .history-group .g-query { font-size: 15px; font-weight: 800; margin: 0; }
            .history-group .g-when { font-size: 12.5px; color: var(--ink-faint); margin: 2px 0 12px; }
            table.hist { width: 100%; border-collapse: collapse; font-size: 13.5px; }
            table.hist th {
                text-align: left; font-size: 11.5px; font-weight: 800; text-transform: uppercase;
                letter-spacing: 0.04em; color: var(--ink-soft); padding: 6px 8px; border-bottom: 2px solid var(--line);
            }
            table.hist td { padding: 8px; border-bottom: 1px solid #eef0f2; vertical-align: middle; }
            table.hist tr:last-child td { border-bottom: none; }

            .badge { display: inline-block; border-radius: 999px; padding: 3px 11px; font-size: 12px; font-weight: 700; }
            .badge-ok { background: var(--ok-soft); color: var(--ok); }
            .badge-warn { background: var(--warn-soft); color: var(--warn); }
            .badge-none { background: #eef0f2; color: var(--chip-ink); }
            .badge-dark { background: var(--chip-soft); color: var(--chip-ink); }

            .provenance-grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
            .prov-cell { border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; background: #fbfcfd; }
            .prov-cell .p-label { font-size: 11.5px; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.03em; }
            .prov-cell .p-value { font-size: 22px; font-weight: 800; margin-top: 4px; }

            table.articles { width: 100%; border-collapse: collapse; font-size: 13.5px; }
            table.articles th {
                text-align: left; font-size: 11.5px; font-weight: 800; text-transform: uppercase;
                letter-spacing: 0.04em; color: var(--ink-soft); padding: 6px 8px; border-bottom: 2px solid var(--line);
            }
            table.articles td { padding: 9px 8px; border-bottom: 1px solid #eef0f2; vertical-align: top; }
            table.articles tr:last-child td { border-bottom: none; }
            .t-title { font-weight: 700; }
            .t-title a { color: #1a56db; text-decoration: none; }
            .t-title a:hover { text-decoration: underline; }
            .t-sub { font-size: 12px; color: var(--ink-faint); margin-top: 3px; }

            .footer { text-align: center; font-size: 12px; color: #8a919b; padding: 8px 0 40px; }
            .footer .gen { color: var(--ink-faint); }
            @media (max-width: 720px) {
                .main { padding: 18px 14px 56px; }
                .breakdown-meta, .breakdown-label { flex-basis: auto; }
                table.articles, table.hist { display: block; overflow-x: auto; }
            }
        </style>
    </head>
    <body>
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-mark"><img src="{{ asset('images/coj-logo.png') }}" alt="City of Johannesburg logo"></div>
                    <div>
                        <div class="brand-name">COJ-MIP</div>
                        <div class="brand-sub">COJ MUNICIPAL INTELLIGENCE PLATFORM · Evidence-aware municipal information discovery and decision support</div>
                    </div>
                </div>
                <nav class="nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="active"><span class="dot" aria-hidden="true"></span>Dashboard</a>
                    <a href="{{ route('intelligence.search') }}">Intelligence Search</a>
                    <a href="#repository-health">Repository</a>
                    <a href="{{ route('selection.history') }}">Selection History</a>
                    <a href="{{ route('newsletter.studio') }}">Newsletter</a>
                </nav>
            </div>
        </header>

        <main class="main">
            @if ($pending_present)
                <div class="notice" role="status">
                    <span class="tag">Repository verification status</span>
                    <p>Some municipal organisation and source records are pending verification. Selection and search results preserve this status so users can distinguish verified information from information requiring further verification. Pending records are not assumed to be false — they simply lack confirmation yet.</p>
                </div>
            @endif

            <section class="hero" aria-labelledby="hero-title">
                <h1 id="hero-title">What municipal information are you looking for?</h1>
                <p class="hero-sub">Search the curated CoJ repository or run the deterministic top-three organisation selection.</p>
                <form class="search-row" action="{{ route('intelligence.search') }}" method="get">
                    <input id="q" name="q" type="text" autocomplete="off"
                           placeholder="e.g. waste management" aria-label="Intelligence query">
                    <button class="btn btn-gold" type="submit">SEARCH</button>
                    <a class="btn btn-dark" style="text-decoration:none; display:inline-flex; align-items:center;" id="selectTop3Href" href="#">SELECT TOP 3</a>
                </form>
                <div class="chips" id="chips" aria-label="Example queries">
                    <button class="chip" type="button">Waste management</button>
                    <button class="chip" type="button">Water services</button>
                    <button class="chip" type="button">Public transport</button>
                    <button class="chip" type="button">Housing</button>
                    <button class="chip" type="button">Economic development</button>
                    <button class="chip" type="button">Public safety</button>
                </div>
            </section>

            <div class="newsletter-cta" role="group" aria-label="Newsletter studio">
                <div class="mark" aria-hidden="true">NL</div>
                <div class="txt">
                    <p class="txt-title">Newsletter Studio</p>
                    <p class="txt-sub">Compose a professional 3-page municipal newsletter from the curated repository — real organisations, real articles, no AI and no invented content.</p>
                </div>
                <div class="go"><a href="{{ route('newsletter.studio') }}">Open Newsletter Studio →</a></div>
            </div>

            <section class="section" aria-labelledby="metrics-title">
                <div class="section-head">
                    <div>
                        <h2 class="section-title" id="metrics-title">Repository at a glance</h2>
                        <p class="section-sub">Live figures calculated from the current repository volume.</p>
                    </div>
                </div>
                <div class="metric-grid">
                    <div class="metric">
                        <p class="metric-label">Total Organisations</p>
                        <div class="metric-value">{{ number_format($metrics['organisations']) }}</div>
                        <p class="metric-note">Entities, directorates and offices</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Municipal Entities</p>
                        <div class="metric-value">{{ number_format($metrics['entities']) }}</div>
                        <p class="metric-note">Entity-class organisations</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Directorates</p>
                        <div class="metric-value">{{ number_format($metrics['directorates']) }}</div>
                        <p class="metric-note">Directorate-class organisations</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Verified Organisations</p>
                        <div class="metric-value" style="color: var(--ok);">{{ number_format($metrics['verified_organisations']) }}</div>
                        <p class="metric-note">Supporting verification evidence</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Pending Organisations</p>
                        <div class="metric-value" style="color: var(--warn);">{{ number_format($metrics['pending_organisations']) }}</div>
                        <p class="metric-note">Awaiting live verification</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Total Articles</p>
                        <div class="metric-value">{{ number_format($metrics['articles']) }}</div>
                        <p class="metric-note">Repository content records</p>
                    </div>
                    <div class="metric">
                        <p class="metric-label">Trusted Sources</p>
                        <div class="metric-value">{{ number_format($metrics['trusted_sources']) }}</div>
                        <p class="metric-note">Approved source registry</p>
                    </div>
                </div>
            </section>

            <section class="section" id="repository-health" aria-labelledby="health-title">
                <div class="section-head">
                    <div>
                        <h2 class="section-title" id="health-title">Repository Health</h2>
                        <p class="section-sub">Evaluated by the existing repository health service at {{ \Illuminate\Support\Carbon::parse($health['generated_at'])->format('j M Y, H:i') }}.</p>
                    </div>
                    <span class="status-pill {{ $health['status'] === 'healthy' ? 'status-healthy' : ($health['status'] === 'degraded' ? 'status-degraded' : 'status-error') }}">
                        <span class="dot" aria-hidden="true"></span>{{ $health['status_label'] }}
                    </span>
                </div>

                <div class="health-grid">
                    <div class="health-cell">
                        <div class="h-label">Organisations</div>
                        <div class="h-value">{{ number_format($health['organisation_count']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Articles</div>
                        <div class="h-value">{{ number_format($health['article_count']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Sources</div>
                        <div class="h-value">{{ number_format($health['source_count']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Verified orgs</div>
                        <div class="h-value" style="color: var(--ok);">{{ number_format($health['verified_organisations']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Pending orgs</div>
                        <div class="h-value" style="color: var(--warn);">{{ number_format($health['pending_organisations']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Verified sources</div>
                        <div class="h-value" style="color: var(--ok);">{{ number_format($health['verified_sources']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Pending sources</div>
                        <div class="h-value" style="color: var(--warn);">{{ number_format($health['pending_sources']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Unverified articles</div>
                        <div class="h-value" style="color: var(--warn);">{{ number_format($health['unverified_articles']) }}</div>
                    </div>
                    <div class="health-cell">
                        <div class="h-label">Fresh content</div>
                        <div class="h-value">{{ number_format($health['fresh_count']) }}</div>
                    </div>
                </div>

                @if ($health['status'] !== 'healthy')
                    <ul class="issues">
                        <li>The repository is not fully verified. Some organisation and source records are pending verification, and search and selection results preserve this status rather than converting it into fact.</li>
                        @foreach ($health['issues'] as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="grid-2">
                <section class="section" aria-labelledby="breakdown-title">
                    <div class="section-head">
                        <div>
                            <h2 class="section-title" id="breakdown-title">Organisation Breakdown</h2>
                            <p class="section-sub">Grouped by the existing organisation type model.</p>
                        </div>
                    </div>
                    @forelse ($type_breakdown as $type)
                        <div class="breakdown-row">
                            <div class="breakdown-label">{{ $type['label'] }}</div>
                            <div class="bar" aria-hidden="true">
                                <div class="seg-verified" style="width: {{ $type['count'] ? round($type['verified'] / $type['count'] * 100) : 0 }}%;"></div>
                                <div class="seg-pending" style="width: {{ $type['count'] ? round($type['pending'] / $type['count'] * 100) : 0 }}%;"></div>
                                <div class="seg-unverified" style="width: {{ $type['count'] ? round($type['unverified'] / $type['count'] * 100) : 0 }}%;"></div>
                            </div>
                            <div class="breakdown-meta">
                                <strong>{{ number_format($type['count']) }}</strong> ({{ $type['percent'] }}%)
                                <div>OK {{ $type['verified'] }} · Pending {{ $type['pending'] }} · Unv. {{ $type['unverified'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty">No organisation records are present in the repository.</div>
                    @endforelse
                </section>

                <section class="section" aria-labelledby="verification-title">
                    <div class="section-head">
                        <div>
                            <h2 class="section-title" id="verification-title">Verification Overview</h2>
                            <p class="section-sub">Uses the platform's verification status values.</p>
                        </div>
                    </div>
                    <div class="verification-grid">
                        <div class="ver-card">
                            <div class="ver-label">Verified</div>
                            <div class="ver-count ver-verified">{{ number_format($verification_overview['verified']['count']) }}</div>
                            <p class="ver-note">{{ $verification_overview['verified']['note'] }}</p>
                        </div>
                        <div class="ver-card">
                            <div class="ver-label">Pending</div>
                            <div class="ver-count ver-pending">{{ number_format($verification_overview['pending']['count']) }}</div>
                            <p class="ver-note">{{ $verification_overview['pending']['note'] }}</p>
                        </div>
                        <div class="ver-card">
                            <div class="ver-label">Unverified</div>
                            <div class="ver-count ver-unverified">{{ number_format($verification_overview['unverified']['count']) }}</div>
                            <p class="ver-note">{{ $verification_overview['unverified']['note'] }}</p>
                        </div>
                    </div>
                </section>
            </div>

            <section class="section" aria-labelledby="preview-title">
                <div class="section-head">
                    <div>
                        <h2 class="section-title" id="preview-title">Top-3 Explainability Preview</h2>
                        <p class="section-sub">Real output of the deterministic selection policy for "{{ $preview['query'] }}". No random filling, no duplicates; scores, repetition adjustments and reasons come from the actual pipeline.</p>
                    </div>
                    <a class="section-link" href="{{ route('intelligence.search') }}?q={{ urlencode($preview['query']) }}&select=1">Run this selection →</a>
                </div>

                @if ($preview['success'])
                    @foreach ($preview['decisions'] as $decision)
                        <div class="preview-card">
                            <div class="preview-head">
                                <div class="preview-rank">#{{ $decision['rank'] }}</div>
                                <p class="preview-name">{{ $decision['organisation_name'] }}</p>
                                <div class="badge {{ $decision['verification_status'] === 'verified' ? 'badge-ok' : ($decision['verification_status'] === 'pending' ? 'badge-warn' : 'badge-none') }}">
                                    {{ strtoupper($decision['verification_status']) }}
                                </div>
                                <div class="preview-meta">
                                    @if ($decision['repetition_penalty'] > 0)
                                        <span class="score-orig"><span class="struck">{{ $decision['original_score'] }}</span></span>
                                        <span class="score-arrow">→</span>
                                    @endif
                                    <span class="score-adj">{{ $decision['adjusted_score'] }}</span>
                                    <span class="penalty {{ $decision['repetition_penalty'] > 0 ? 'penalty-yes' : 'penalty-no' }}">
                                        penalty −{{ $decision['repetition_penalty'] }}
                                    </span>
                                </div>
                            </div>
                            <ul class="why">
                                @foreach ($decision['reasons'] as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                            <div class="preview-terms">
                                @foreach ($decision['matching_terms'] as $term)
                                    <span class="term">{{ $term }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty">
                        {{ $preview['message'] }}
                        @if (!empty($preview['decisions']))
                            ({{ $preview['available'] }} of {{ $preview['required'] }} eligible candidates available.)
                        @endif
                    </div>
                @endif
            </section>

            <section class="section" aria-labelledby="history-title">
                <div class="section-head">
                    <div>
                        <h2 class="section-title" id="history-title">Recent Selection Decisions</h2>
                        <p class="section-sub">
                            @if ($selection_history['has_history'])
                                {{ number_format($selection_history['total']) }} successful decision{{ $selection_history['total'] === 1 ? '' : 's' }} recorded.
                            @else
                                Persisted, audit-ready top-three decision history.
                            @endif
                        </p>
                    </div>
                    <a class="section-link" href="{{ route('selection.history') }}">View Selection History →</a>
                </div>

                @forelse ($selection_history['groups'] as $group)
                    <div class="history-group">
                        <p class="g-query">“{{ $group['query'] }}”</p>
                        <p class="g-when">{{ \Illuminate\Support\Carbon::parse($group['selected_at'])->format('j M Y, H:i') }}</p>
                        <table class="hist">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Organisation</th>
                                    <th>Score</th>
                                    <th>Penalty</th>
                                    <th>Verification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['selections'] as $selection)
                                    <tr>
                                        <td>{{ $selection['rank'] }}</td>
                                        <td>{{ $selection['organisation_name'] }}</td>
                                        <td>
                                            @if ($selection['repetition_penalty'] > 0)
                                                <span style="color:var(--ink-soft); text-decoration:line-through;">{{ $selection['original_score'] }}</span>
                                                → {{ $selection['adjusted_score'] }}
                                            @else
                                                {{ $selection['adjusted_score'] }}
                                            @endif
                                        </td>
                                        <td>−{{ $selection['repetition_penalty'] }}</td>
                                        <td>
                                            <span class="badge {{ $selection['verification_status'] === 'verified' ? 'badge-ok' : ($selection['verification_status'] === 'pending' ? 'badge-warn' : 'badge-none') }}">
                                                {{ strtoupper($selection['verification_status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="empty">No selection decisions have been recorded yet.</div>
                @endforelse
            </section>

            <div class="grid-2">
                <section class="section" aria-labelledby="articles-title">
                    <div class="section-head">
                        <div>
                            <h2 class="section-title" id="articles-title">Latest Repository Content</h2>
                            <p class="section-sub">Most recently retrieved article records from the curated repository.</p>
                        </div>
                    </div>
                    @forelse ($latest_articles as $article)
                        <div class="preview-card">
                            <div class="preview-head" style="margin-bottom: 6px;">
                                <p class="preview-name" style="font-size: 15px;">
                                    @if ($article['source_url'])
                                        <a style="color:#1a56db; text-decoration:none;" href="{{ $article['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ $article['title'] }}</a>
                                    @else
                                        {{ $article['title'] }}
                                    @endif
                                </p>
                                <div class="preview-meta">
                                    <span class="badge {{ $article['status'] === 'verified' ? 'badge-ok' : 'badge-warn' }}">{{ strtoupper($article['status']) }}</span>
                                </div>
                            </div>
                            <div class="t-sub">
                                @if ($article['organisation_names'])
                                    Organisations: {{ implode(', ', $article['organisation_names']) }}
                                @else
                                    Organisations: —
                                @endif
                                &nbsp;·&nbsp; Source: {{ $article['source_name'] }} ({{ strtoupper($article['tier']) }})
                                &nbsp;·&nbsp;
                                @if ($article['published_at'])
                                    Published {{ $article['published_at']->format('j M Y') }}
                                @elseif ($article['retrieved_at'])
                                    Retrieved {{ $article['retrieved_at']->format('j M Y') }}
                                @else
                                    No date recorded
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty">No article records are present in the repository.</div>
                    @endforelse
                </section>

                <div>
                    <section class="section" aria-labelledby="provenance-title">
                        <div class="section-head">
                            <div>
                                <h2 class="section-title" id="provenance-title">Source / Provenance</h2>
                                <p class="section-sub">The approved source registry and its verification posture.</p>
                            </div>
                        </div>
                        <div class="provenance-grid">
                            <div class="prov-cell">
                                <div class="p-label">Trusted Sources</div>
                                <div class="p-value">{{ number_format($provenance['trusted_sources']) }}</div>
                            </div>
                            <div class="prov-cell">
                                <div class="p-label">Verified Sources</div>
                                <div class="p-value" style="color: var(--ok);">{{ number_format($provenance['verified_sources']) }}</div>
                            </div>
                            <div class="prov-cell">
                                <div class="p-label">Pending Sources</div>
                                <div class="p-value" style="color: var(--warn);">{{ number_format($provenance['pending_sources']) }}</div>
                            </div>
                            <div class="prov-cell">
                                <div class="p-label">Tier 1 (Authoritative)</div>
                                <div class="p-value">{{ number_format($provenance['tiers']['tier1'] ?? 0) }}</div>
                            </div>
                            <div class="prov-cell">
                                <div class="p-label">Tier 2</div>
                                <div class="p-value">{{ number_format($provenance['tiers']['tier2'] ?? 0) }}</div>
                            </div>
                            <div class="prov-cell">
                                <div class="p-label">Tier 3</div>
                                <div class="p-value">{{ number_format($provenance['tiers']['tier3'] ?? 0) }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="section" aria-labelledby="about-title">
                        <div class="section-head">
                            <div>
                                <h2 class="section-title" id="about-title">About CoJ-MIP</h2>
                            </div>
                        </div>
                        <div style="text-align:center; margin-bottom:14px;">
                            <img src="{{ asset('images/coj-initials.jpg') }}" alt="COJ initials logo" style="height:76px; width:76px; border-radius:14px; box-shadow:0 2px 12px rgba(16,20,24,0.12);">
                        </div>
                        <div class="t-sub" style="font-size: 13px; color: var(--ink-soft); line-height: 1.6;">
                            <p style="margin: 0 0 8px;">CoJ-MIP is an evidence-aware municipal information discovery and decision-support application. It is <strong>not an official City of Johannesburg government system</strong>.</p>
                            <p style="margin: 0;">All figures on this page are computed live from the JSON repository and the same deterministic search, selection and health services used by the intelligence API — nothing is hard-coded or fabricated.</p>
                        </div>
                    </section>
                </div>
            </div>

            <footer class="footer">
                CoJ-MIP · Municipal Intelligence Platform · Deterministic, evidence-aware decision support
                <div class="gen">Generated {{ \Illuminate\Support\Carbon::parse($generated_at)->format('j M Y, H:i T') }}</div>
            </footer>
        </main>

        <script>
            (function () {
                const input = document.getElementById('q');
                const chips = document.getElementById('chips');
                const target = document.getElementById('selectTop3Href');

                function href(q) {
                    return '/intelligence?q=' + encodeURIComponent(q);
                }

                function syncHref() {
                    target.href = href(input.value.trim());
                }

                chips.addEventListener('click', function (event) {
                    const chip = event.target.closest('.chip');
                    if (!chip) { return; }
                    input.value = chip.textContent.trim();
                    syncHref();
                });

                input.addEventListener('input', syncHref);
                window.addEventListener('load', syncHref);
            })();
        </script>
    </body>
</html>