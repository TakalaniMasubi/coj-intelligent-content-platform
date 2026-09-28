<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>CoJ-MIP — Municipal Intelligence</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/coj-initials.jpg') }}">
        <style>
            :root {
                --ink: #101418;
                --ink-soft: #4b5563;
                --paper: #f6f7f8;
                --card: #ffffff;
                --line: #e3e6e8;
                --gold: #f5b800;
                --gold-deep: #c79500;
                --ver-ok: #137333;
                --ver-warn: #9a6b00;
                --ver-null: #6b7280;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
                background: var(--paper);
                color: var(--ink);
                line-height: 1.5;
            }
            .topbar {
                background: #0e1115;
                color: #fff;
                border-bottom: 4px solid var(--gold);
            }
            .topbar-inner {
                max-width: 1000px;
                margin: 0 auto;
                padding: 18px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                flex-wrap: wrap;
            }
            .brand { display: flex; align-items: center; gap: 12px; }
            .brand-mark {
                height: 54px; padding: 6px 12px; border-radius: 10px;
                background: #fff; display: flex; align-items: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.28);
            }
            .brand-mark img { height: 42px; width: auto; display: block; }
            .brand-name { font-size: 18px; font-weight: 800; letter-spacing: 0.02em; }
            .brand-sub { font-size: 12px; color: #b7bcc4; }
            .nav { display: flex; gap: 6px; flex-wrap: wrap; }
            .nav a {
                color: #d7dbe1; text-decoration: none; font-size: 13px; font-weight: 600;
                padding: 8px 13px; border-radius: 8px;
            }
            .nav a:hover, .nav a.active { background: rgba(245, 184, 0, 0.14); color: #fff; }
            .nav a.active { box-shadow: inset 0 -2px 0 var(--gold); }
            .nav .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold); display: inline-block; }
            .main { max-width: 1000px; margin: 0 auto; padding: 32px 20px 64px; }
            .search-panel {
                background: var(--card); border: 1px solid var(--line);
                border-radius: 14px; padding: 22px;
                box-shadow: 0 6px 22px rgba(16, 20, 24, 0.06);
            }
            .search-label { font-size: 13px; font-weight: 600; color: var(--ink-soft); margin-bottom: 8px; }
            .search-row { display: flex; gap: 10px; }
            #query {
                flex: 1; font-size: 16px; padding: 13px 16px;
                border: 1px solid var(--line); border-radius: 10px;
                background: #fbfcfd; color: var(--ink);
            }
            #query:focus { outline: 2px solid var(--gold); border-color: var(--gold-deep); }
            #submit {
                background: var(--gold); border: none; border-radius: 10px;
                padding: 13px 22px; font-size: 15px; font-weight: 700;
                color: #0e1115; cursor: pointer;
            }
            #submit:hover { background: var(--gold-deep); }
            #submit:disabled { opacity: 0.55; cursor: wait; }
            #selectBtn {
                background: #0e1115; color: #fff; border: none; border-radius: 10px;
                padding: 13px 22px; font-size: 15px; font-weight: 700;
                cursor: pointer; white-space: nowrap;
            }
            #selectBtn:hover { background: #1c232b; }
            #selectBtn:disabled { opacity: 0.55; cursor: wait; }
            .select-block {
                background: var(--card); border: 1px solid var(--gold-deep);
                border-radius: 14px; padding: 22px; margin-top: 22px;
                box-shadow: 0 6px 22px rgba(16, 20, 24, 0.06);
            }
            .select-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 6px; }
            .select-title { font-size: 19px; font-weight: 800; margin: 0; }
            .select-sub { font-size: 13px; color: var(--ink-soft); margin: 2px 0 14px; }
            .policy {
                background: #0e1115; color: #d7dbe1; border-radius: 10px;
                padding: 14px 16px; font-size: 13px; margin: 4px 0 16px;
            }
            .policy strong { color: #fff; }
            .policy ul { margin: 8px 0 0; padding-left: 18px; }
            .policy li { margin-bottom: 3px; }
            .selection-card {
                border: 1px solid var(--line); border-radius: 12px;
                padding: 16px 18px; margin-bottom: 12px;
                background: #fdfdfe;
            }
            .selection-rank {
                background: var(--gold); color: #0e1115; border-radius: 8px;
                width: 34px; height: 34px; display: flex; align-items: center;
                justify-content: center; font-weight: 800; font-size: 16px; flex: 0 0 auto;
            }
            .selection-head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
            .selection-name { font-size: 17px; font-weight: 800; margin: 0; }
            .scores { display: flex; align-items: center; gap: 10px; margin-left: auto; flex-wrap: wrap; }
            .score-orig { font-size: 15px; font-weight: 600; }
            .score-orig.s-struck { color: var(--ink-soft); text-decoration: line-through; }
            .score-was { font-size: 12px; color: var(--ink-soft); }
            .score-arrow { color: var(--ink-soft); font-weight: 700; }
            .score-adjust { font-size: 18px; font-weight: 800; }
            .penalty-chip {
                background: #fbe6e3; border: 1px solid #f5b8b0; color: #a13a2e;
                border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 600;
            }
            .penalty-chip.zero { background: #e7f6ec; border-color: #b7e0c4; color: var(--ver-ok); }
            .after { color: var(--ink-soft); font-size: 12px; }
            #select-results { display: none; }
            #select-results.show { display: block; }
            .no-select { background: #e7f6ec; border: 1px solid #b7e0c4; }
            .example-label { font-size: 12px; color: var(--ink-soft); margin-top: 16px; margin-bottom: 8px; }
            .examples { display: flex; gap: 8px; flex-wrap: wrap; }
            .chip {
                background: #f0f1f3; border: 1px solid var(--line);
                border-radius: 999px; padding: 7px 13px; font-size: 13px;
                color: var(--ink-soft); cursor: pointer;
            }
            .chip:hover { background: #fff3cf; border-color: var(--gold-deep); color: var(--ink); }
            .status-line {
                min-height: 24px; font-size: 14px; color: var(--ink-soft);
                margin-top: 16px; display: flex; align-items: center; gap: 10px;
            }
            .spinner {
                width: 16px; height: 16px; border-radius: 50%;
                border: 2px solid var(--line); border-top-color: var(--gold-deep);
                animation: spin 0.7s linear infinite; display: none;
            }
            .body-loading .spinner { display: inline-block; }
            @keyframes spin { to { transform: rotate(360deg); } }
            .meta { font-size: 13px; color: var(--ink-soft); margin: 0 0 14px; }
            .message {
                background: #fbf1ce; border: 1px solid #e8d27a;
                border-radius: 10px; padding: 14px 16px; font-size: 14px;
            }
            .card {
                background: var(--card); border: 1px solid var(--line);
                border-radius: 12px; padding: 18px 20px; margin-bottom: 14px;
                box-shadow: 0 3px 12px rgba(16, 20, 24, 0.05);
            }
            .card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
            .org-name { font-size: 18px; font-weight: 800; margin: 0; }
            .rank-pill {
                background: #0e1115; color: #fff; border-radius: 8px;
                padding: 4px 10px; font-size: 12px; font-weight: 700; margin-left: 8px;
            }
            .score-block { text-align: right; }
            .score-num { font-size: 22px; font-weight: 800; }
            .score-bar { width: 140px; height: 7px; background: #eceef0; border-radius: 999px; margin-top: 6px; overflow: hidden; }
            .score-fill { height: 100%; background: linear-gradient(90deg, var(--gold-deep), var(--gold)); }
            .badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
            .badge { border-radius: 999px; padding: 4px 11px; font-size: 12px; font-weight: 600; }
            .badge-type { background: #eef1f4; color: #374151; }
            .badge-verified { background: #e7f6ec; color: var(--ver-ok); }
            .badge-pending { background: #fdf2d7; color: var(--ver-warn); }
            .badge-unverified { background: #f1f2f4; color: var(--ver-null); }
            .section-title { font-size: 12px; font-weight: 700; letter-spacing: 0.04em; color: var(--ink-soft); margin: 14px 0 6px; text-transform: uppercase; }
            .reasons { margin: 0; padding-left: 18px; font-size: 14px; color: var(--ink); }
            .reasons li { margin-bottom: 3px; }
            .terms { display: flex; gap: 6px; flex-wrap: wrap; }
            .term {
                background: #fff3cf; border: 1px solid #ecd78c;
                color: #5c4500; border-radius: 6px; padding: 2px 9px;
                font-size: 12px; font-weight: 600;
            }
            .sources { display: flex; gap: 6px; flex-wrap: wrap; }
            .source {
                font-size: 12px; color: #1a56db; text-decoration: none;
                background: #eef3ff; border: 1px solid #d4dfff;
                border-radius: 6px; padding: 2px 9px; word-break: break-all;
            }
            .source:hover { background: #dfe8ff; }
            #results-empty { display: none; }
            #results-empty.show { display: block; }
            #results { display: none; }
            #results.show { display: block; }
            #error { display: none; }
            #error.show { display: block; }
            .footer { text-align: center; font-size: 12px; color: #9aa1a9; padding: 8px 0 32px; }
        </style>
    </head>
    <body>
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-mark"><img src="{{ asset('images/coj-logo.png') }}" alt="City of Johannesburg logo"></div>
                    <div>
                        <div class="brand-name">COJ-MIP</div>
                        <div class="brand-sub">Municipal Intelligence</div>
                    </div>
                </div>
                <nav class="nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('intelligence.search') }}" class="active"><span class="dot" aria-hidden="true"></span>Intelligence Search</a>
                    <a href="{{ route('selection.history') }}">Selection History</a>
                    <a href="{{ route('newsletter.studio') }}">Newsletter</a>
                </nav>
            </div>
        </header>

        <main class="main">
            <section class="search-panel">
                <div class="search-label">Ask the municipal intelligence repository...</div>
                <div class="search-row">
                    <input id="query" type="text" autocomplete="off" placeholder="e.g. Which organisations are involved in waste management?" value="">
                    <button id="submit" type="button">Search</button>
                    <button id="selectBtn" type="button" title="Run the deterministic top-three selection policy for this query">Select Top 3</button>
                </div>
                <div class="example-label">Try an example</div>
                <div class="examples" id="examples">
                    <button class="chip" type="button">Which organisations are involved in waste management?</button>
                    <button class="chip" type="button">Which organisations are related to water services?</button>
                    <button class="chip" type="button">Which organisations are involved in transport?</button>
                    <button class="chip" type="button">Which organisations support housing?</button>
                    <button class="chip" type="button">Which organisations are involved in economic development?</button>
                    <button class="chip" type="button">Which organisations are involved in public safety?</button>
                </div>
            </section>

            <section id="body" class="status-line">
                <span class="spinner" aria-hidden="true"></span>
                <span id="statusText">Enter a question above to search the municipal intelligence repository.</span>
            </section>

            <div id="select-results"></div>

            <div id="error" class="message" style="background:#fbe6e3;border-color:#f5b8b0;"></div>

            <div id="results"></div>

            <div id="results-empty">
                <div class="message"></div>
            </div>

            <footer class="footer">CoJ-MIP · Deterministic municipal intelligence · Repository content is shown with its verification status.</footer>
        </main>

        <script>
            (function () {
                const input = document.getElementById('query');
                const submit = document.getElementById('submit');
                const selectBtn = document.getElementById('selectBtn');
                const body = document.getElementById('body');
                const statusText = document.getElementById('statusText');
                const resultsEl = document.getElementById('results');
                const emptyEl = document.getElementById('results-empty');
                const selectEl = document.getElementById('select-results');
                const errorEl = document.getElementById('error');

                document.getElementById('examples').addEventListener('click', function (event) {
                    const chip = event.target.closest('.chip');
                    if (!chip) { return; }
                    input.value = chip.textContent.trim();
                    search();
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') { search(); }
                });

                submit.addEventListener('click', search);
                selectBtn.addEventListener('click', selectTop3);

                async function search() {
                    const query = input.value.trim();
                    resultsEl.classList.remove('show');
                    resultsEl.innerHTML = '';
                    emptyEl.classList.remove('show');
                    selectEl.classList.remove('show');
                    selectEl.innerHTML = '';
                    errorEl.classList.remove('show');

                    if (!query) {
                        statusText.textContent = 'Enter a question above to search the municipal intelligence repository.';
                        return;
                    }

                    body.classList.add('body-loading');
                    statusText.textContent = 'Searching the municipal intelligence repository...';
                    submit.disabled = true;

                    try {
                        const response = await fetch('/api/v1/intelligence/search', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ query: query }),
                        });

                        const payload = await response.json();

                        if (!response.ok || payload.success !== true) {
                            const detail = payload.errors && payload.errors.query && payload.errors.query[0]
                                ? payload.errors.query[0]
                                : (payload.message || 'The search request could not be completed.');
                            statusText.textContent = '';
                            errorEl.textContent = detail;
                            errorEl.classList.add('show');
                            return;
                        }

                        render(payload.data, query);
                    } catch (fetchError) {
                        statusText.textContent = '';
                        errorEl.textContent = 'Could not reach the intelligence service. Please try again.';
                        errorEl.classList.add('show');
                    } finally {
                        body.classList.remove('body-loading');
                        submit.disabled = false;
                    }
                }

                async function selectTop3() {
                    const query = input.value.trim();
                    selectEl.classList.remove('show');
                    selectEl.innerHTML = '';
                    errorEl.classList.remove('show');

                    if (!query) {
                        statusText.textContent = 'Enter a question above before running the top-three selection.';
                        return;
                    }

                    body.classList.add('body-loading');
                    statusText.textContent = 'Running the top-three selection policy...';
                    selectBtn.disabled = true;

                    try {
                        const response = await fetch('/api/v1/intelligence/select', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ query: query }),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            const detail = payload.errors && payload.errors.query && payload.errors.query[0]
                                ? payload.errors.query[0]
                                : (payload.message || 'The selection request could not be completed.');
                            statusText.textContent = '';
                            errorEl.textContent = detail;
                            errorEl.classList.add('show');
                            return;
                        }

                        if (payload.success === true && payload.data) {
                            renderSelection(payload.data);
                        } else {
                            renderInsufficient(query, payload);
                        }
                    } catch (fetchError) {
                        statusText.textContent = '';
                        errorEl.textContent = 'Could not reach the intelligence service. Please try again.';
                        errorEl.classList.add('show');
                    } finally {
                        body.classList.remove('body-loading');
                        selectBtn.disabled = false;
                    }
                }

                function render(data, originalQuery) {
                    statusText.textContent = originalQuery + ' — ' + data.result_count + ' result' + (data.result_count === 1 ? '' : 's') + ' found.';

                    if (data.result_count === 0) {
                        emptyEl.querySelector('.message').textContent = data.message ||
                            'No sufficiently relevant information was found in the repository for this query.';
                        emptyEl.classList.add('show');
                        return;
                    }

                    const container = document.createElement('div');

                    data.results.forEach(function (result) {
                        container.appendChild(resultCard(result));
                    });

                    resultsEl.appendChild(container);
                    resultsEl.classList.add('show');
                }

                function resultCard(result) {
                    const card = el('div', null, 'card');

                    const head = el('div', null, 'card-head');
                    const left = el('div');
                    const nameRow = el('div');
                    const nameSpan = el('span', result.organisation_name, 'org-name');
                    nameRow.appendChild(nameSpan);
                    const rankPill = el('span', '#' + result.rank, 'rank-pill');
                    nameRow.appendChild(rankPill);
                    left.appendChild(nameRow);

                    const badges = el('div', null, 'badges');
                    badges.appendChild(el('span', typeLabel(result.organisation_type), 'badge badge-type'));
                    badges.appendChild(el('span', verificationLabel(result.verification_status), 'badge ' + verificationClass(result.verification_status)));
                    left.appendChild(badges);

                    const scoreBlock = el('div', null, 'score-block');
                    scoreBlock.appendChild(el('div', result.score, 'score-num'));
                    const bar = el('div', null, 'score-bar');
                    const fill = el('div', null, 'score-fill');
                    fill.style.width = result.score + '%';
                    bar.appendChild(fill);
                    scoreBlock.appendChild(bar);

                    head.appendChild(left);
                    head.appendChild(scoreBlock);
                    card.appendChild(head);

                    card.appendChild(el('div', 'Why this matched', 'section-title'));
                    const ul = document.createElement('ul');
                    ul.className = 'reasons';
                    result.reasons.forEach(function (reason) { ul.appendChild(el('li', reason)); });
                    card.appendChild(ul);

                    card.appendChild(el('div', 'Matching terms', 'section-title'));
                    const terms = el('div', null, 'terms');
                    result.matching_terms.forEach(function (term) { terms.appendChild(el('span', term, 'term')); });
                    card.appendChild(terms);

                    if (result.sources.length > 0) {
                        card.appendChild(el('div', 'Sources', 'section-title'));
                        const sources = el('div', null, 'sources');
                        result.sources.forEach(function (url) {
                            const a = document.createElement('a');
                            a.href = url;
                            a.target = '_blank';
                            a.rel = 'noopener noreferrer';
                            a.className = 'source';
                            a.textContent = url.replace(/^https?:\/\//, '');
                            sources.appendChild(a);
                        });
                        card.appendChild(sources);
                    }

                    return card;
                }

                function typeLabel(type) {
                    return type === 'entity' ? 'Entity' : type === 'directorate' ? 'Directorate' : 'Office';
                }

                function verificationLabel(status) {
                    return status.toUpperCase();
                }

                function verificationClass(status) {
                    return status === 'verified' ? 'badge-verified' : (status === 'pending' ? 'badge-pending' : 'badge-unverified');
                }

                function el(tag, text, className) {
                    const node = document.createElement(tag);
                    if (text !== null && text !== undefined) { node.textContent = String(text); }
                    if (className) { node.className = className; }
                    return node;
                }

                // When launched from the dashboard ("?q=..." and optionally
                // "select=1") this page performs the existing search — and the
                // existing top-three selection — automatically, reusing the
                // same workflow as typing and clicking on this page.
                (function boot() {
                    const params = new URLSearchParams(window.location.search);
                    const q = (params.get('q') || '').trim();

                    if (!q) { return; }

                    input.value = q;
                    search().then(function () {
                        if (params.get('select') === '1') {
                            selectTop3();
                        }
                    });
                })();

                function renderSelection(data) {
                    statusText.textContent = data.original_query + ' — top three selected.';

                    const block = el('div', null, 'select-block');

                    const head = el('div', null, 'select-head');
                    head.appendChild(el('h2', 'TOP 3 MUNICIPAL ORGANISATIONS', 'select-title'));
                    block.appendChild(head);
                    block.appendChild(el('div', data.selected_count + ' of ' + data.required + ' organisations recommended for ' +
                        data.original_query + '.', 'select-sub'));

                    const policy = el('div', null, 'policy');
                    policy.appendChild(el('strong', 'Selection policy'));
                    const plist = document.createElement('ul');
                    plist.appendChild(el('li', 'Exactly 3 organisations are chosen from the deterministic municipal ranking — never random, never the first three array entries.'));
                    plist.appendChild(el('li', 'Candidates must reach the relevance floor (original score ≥ 20) to be eligible.'));
                    plist.appendChild(el('li', 'Ranking order is preserved; each organisation keeps its original relevance score and verification status.'));
                    plist.appendChild(el('li', 'A repetition adjustment removes up to 4 points per prior selection inside the 30-day window, capped at 25, so repeated organisations are treated fairly — but an irrelevant organisation is never added just to avoid repetition.'));
                    policy.appendChild(plist);
                    block.appendChild(policy);

                    data.selections.forEach(function (selection) {
                        block.appendChild(selectionCard(selection));
                    });

                    selectEl.appendChild(block);
                    selectEl.classList.add('show');
                }

                function selectionCard(selection) {
                    const card = el('div', null, 'selection-card');

                    const head = el('div', null, 'selection-head');
                    head.appendChild(el('div', '#' + selection.rank, 'selection-rank'));

                    const left = el('div');
                    const nameRow = el('div');
                    nameRow.appendChild(el('span', selection.organisation_name, 'selection-name'));
                    left.appendChild(nameRow);

                    const badges = el('div', null, 'badges');
                    badges.appendChild(el('span', verificationLabel(selection.verification_status), 'badge ' + verificationClass(selection.verification_status)));
                    if (selection.final_selection) { badges.appendChild(el('span', 'Selected', 'badge badge-type')); }
                    left.appendChild(badges);

                    head.appendChild(left);

                    const scores = el('div', null, 'scores');
                    const penalty = selection.repetition_penalty || 0;
                    if (penalty === 0) {
                        const plain = el('div', null, 'score-orig');
                        plain.appendChild(el('span', String(selection.adjusted_score), 'score-adjust'));
                        scores.appendChild(plain);
                        scores.appendChild(el('span', 'relevance score (no repetition adjustment)', 'after'));
                    } else {
                        const orig = el('div', null, 'score-orig s-struck');
                        orig.appendChild(el('span', String(selection.original_score), ''));
                        scores.appendChild(orig);
                        scores.appendChild(el('span', '→', 'score-arrow'));
                        const adj = el('div', null, 'score-orig');
                        adj.appendChild(el('span', String(selection.adjusted_score), 'score-adjust'));
                        scores.appendChild(adj);
                    }
                    scores.appendChild(el('span', 'penalty −' + penalty, 'penalty-chip' + (penalty === 0 ? ' zero' : '')));
                    head.appendChild(scores);

                    card.appendChild(head);

                    card.appendChild(el('div', 'Why selected', 'section-title'));
                    const reasons = document.createElement('ul');
                    reasons.className = 'reasons';
                    selection.reasons.forEach(function (reason) { reasons.appendChild(el('li', reason)); });
                    card.appendChild(reasons);

                    card.appendChild(el('div', 'Matching terms', 'section-title'));
                    const terms = el('div', null, 'terms');
                    selection.matching_terms.forEach(function (term) { terms.appendChild(el('span', term, 'term')); });
                    card.appendChild(terms);

                    return card;
                }

                function renderInsufficient(query, payload) {
                    const required = payload.required || 3;
                    const available = payload.available || 0;

                    statusText.textContent = query + ' — insufficient results.';

                    const block = el('div', null, 'select-block no-select');
                    block.appendChild(el('h2', 'SELECTION NOT RUN — INSUFFICIENT RESULTS', 'select-title'));
                    block.appendChild(el('div', 'The selection policy requires exactly ' + required +
                        ' eligible organisations, but only ' + available + ' candidate' + (available === 1 ? '' : 's') +
                        ' reached the relevance floor for this query.', 'select-sub'));
                    block.appendChild(el('div', (payload.message || 'No candidate organisations were eligible for selection.'), 'message'));

                    if (payload.results && payload.results.length > 0) {
                        block.appendChild(el('div', 'Candidates reviewed (not selected)', 'section-title'));
                        const rows = el('div');
                        payload.results.forEach(function (candidate) {
                            rows.appendChild(selectionCard(candidate));
                        });
                        block.appendChild(rows);
                    }

                    selectEl.appendChild(block);
                    selectEl.classList.add('show');
                }
            })();
        </script>
    </body>
</html>