<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CoJ-MIP — Selection History</title>
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
                --bad-soft: #fbe6e3;
                --bad: #b3261e;
                --chip-ink: #374151;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
                background: var(--paper); color: var(--ink); line-height: 1.5; font-size: 15px;
            }
            .topbar { background: #0e1115; color: #fff; border-bottom: 4px solid var(--gold); }
            .topbar-inner {
                max-width: 1000px; margin: 0 auto; padding: 14px 24px;
                display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            }
            .brand { display: flex; align-items: center; gap: 12px; }
            .brand-mark {
                width: 40px; height: 40px; border-radius: 8px; background: var(--gold); color: #0e1115;
                display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px;
            }
            .brand-name { font-size: 16px; font-weight: 800; letter-spacing: 0.02em; }
            .brand-sub { font-size: 12px; color: #b7bcc4; }
            .nav { display: flex; gap: 6px; flex-wrap: wrap; }
            .nav a {
                color: #d7dbe1; text-decoration: none; font-size: 13px; font-weight: 600;
                padding: 8px 13px; border-radius: 8px;
            }
            .nav a:hover { background: rgba(245, 184, 0, 0.14); color: #fff; }
            .main { max-width: 1000px; margin: 0 auto; padding: 26px 24px 72px; }
            .section {
                background: var(--card); border: 1px solid var(--line); border-radius: 14px;
                padding: 22px 24px; box-shadow: 0 4px 16px rgba(16,20,24,0.05);
            }
            .section-head { margin-bottom: 18px; }
            .section-title { font-size: 19px; font-weight: 800; margin: 0 0 4px; }
            .section-sub { font-size: 13px; color: var(--ink-soft); margin: 0; }
            .history-group { border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; background: #fdfdfe; }
            .g-query { font-size: 15px; font-weight: 800; margin: 0; }
            .g-when { font-size: 12.5px; color: var(--ink-faint); margin: 2px 0 12px; }
            table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
            th {
                text-align: left; font-size: 11.5px; font-weight: 800; text-transform: uppercase;
                letter-spacing: 0.04em; color: var(--ink-soft); padding: 6px 8px; border-bottom: 2px solid var(--line);
            }
            td { padding: 8px; border-bottom: 1px solid #eef0f2; vertical-align: middle; }
            tr:last-child td { border-bottom: none; }
            .badge { display: inline-block; border-radius: 999px; padding: 3px 11px; font-size: 12px; font-weight: 700; }
            .badge-ok { background: var(--ok-soft); color: var(--ok); }
            .badge-warn { background: var(--warn-soft); color: var(--warn); }
            .badge-none { background: #eef0f2; color: var(--chip-ink); }
            .struck { color: var(--ink-soft); text-decoration: line-through; }
            .empty { font-size: 14px; color: var(--ink-faint); padding: 24px 0 12px; text-align: center; }
            .back { font-size: 13px; font-weight: 700; color: #1a56db; text-decoration: none; }
            .back:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-mark">CoJ</div>
                    <div>
                        <div class="brand-name">COJ-MIP</div>
                        <div class="brand-sub">Selection History</div>
                    </div>
                </div>
                <nav class="nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('intelligence.search') }}">Intelligence Search</a>
                </nav>
            </div>
        </header>

        <main class="main">
            <div style="margin-bottom: 16px;"><a class="back" href="{{ route('dashboard') }}">← Back to Dashboard</a></div>

            <section class="section">
                <div class="section-head">
                    <h1 class="section-title">Selection History</h1>
                    <p class="section-sub">
                        Complete, audit-ready record of successful top-three selections, persisted by the selection module.
                        @if ($has_history)
                            {{ number_format($total) }} decision{{ $total === 1 ? '' : 's' }} recorded.
                        @endif
                    </p>
                </div>

                @forelse ($groups as $group)
                    <div class="history-group">
                        <p class="g-query">“{{ $group['query'] }}”</p>
                        <p class="g-when">{{ \Illuminate\Support\Carbon::parse($group['selected_at'])->format('j M Y, H:i T') }}</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Organisation</th>
                                    <th>Original</th>
                                    <th>Adjusted</th>
                                    <th>Penalty</th>
                                    <th>Verification</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['selections'] as $selection)
                                    <tr>
                                        <td>{{ $selection['rank'] }}</td>
                                        <td>{{ $selection['organisation_name'] }}</td>
                                        <td class="{{ $selection['repetition_penalty'] > 0 ? 'struck' : '' }}">{{ $selection['original_score'] }}</td>
                                        <td><strong>{{ $selection['adjusted_score'] }}</strong></td>
                                        <td>−{{ $selection['repetition_penalty'] }}</td>
                                        <td>
                                            <span class="badge {{ $selection['verification_status'] === 'verified' ? 'badge-ok' : ($selection['verification_status'] === 'pending' ? 'badge-warn' : 'badge-none') }}">
                                                {{ strtoupper($selection['verification_status']) }}
                                            </span>
                                        </td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($selection['selection_timestamp'])->format('j M Y, H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="empty">No selection decisions have been recorded yet.</div>
                @endforelse
            </section>
        </main>
    </body>
</html>