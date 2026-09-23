/*
|--------------------------------------------------------------------------
| Newsletter sheet styles (shared web preview + Dompdf PDF)
|--------------------------------------------------------------------------
| Written for Dompdf compatibility: tables and inline-block layouts, solid
| fills, em-based sizing so the density overrides scale the whole document.
| `text-transform` is avoided — labels are written uppercase in the HTML.
*/

.nlsheet {
    width: 100%;
    background: #ffffff;
    color: #1f2430;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 13px;
    line-height: 1.55;
}

.nlsheet * { box-sizing: border-box; }

.nlsheet .ns-page {
    padding: 0 0.1em 0.3em;
}

.nlsheet .breaks {
    page-break-before: always;
}

/* ---------------- Masthead ---------------- */

.nlsheet .ns-masthead {
    text-align: center;
    border-bottom: 0.28em solid #f5b800;
    padding: 0 0.4em 1em;
    margin-bottom: 1.1em;
}

.nlsheet .ns-mast-logo {
    display: block;
    text-align: center;
    margin-bottom: 0.5em;
}

.nlsheet .ns-mast-logo img {
    display: inline-block;
    vertical-align: middle;
}

.nlsheet .ns-mast-seal {
    display: inline-block;
    margin-top: 0.2em;
}

.nlsheet .ns-eyebrow {
    font-size: 0.62em;
    letter-spacing: 0.16em;
    color: #966f00;
    font-weight: 700;
    margin: 0 0 0.4em;
}

.nlsheet .ns-title {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 1.9em;
    color: #141414;
    letter-spacing: 0.02em;
    margin: 0.05em 0 0.15em;
    line-height: 1.15;
}

.nlsheet .ns-subtitle {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-style: italic;
    color: #0f5c3a;
    font-size: 1.02em;
    margin: 0 0 0.5em;
}

.nlsheet .ns-date {
    font-size: 0.66em;
    color: #6b6b6b;
    letter-spacing: 0.08em;
    margin: 0;
}

/* ---------------- Intro / section titles ---------------- */

.nlsheet .ns-intro {
    background: #f7f1e1;
    border-left: 0.32em solid #f5b800;
    padding: 0.85em 1.05em;
    margin: 0 0 1.2em;
    font-size: 0.92em;
    color: #2b2b2b;
}

.nlsheet .ns-section-title {
    font-size: 0.66em;
    letter-spacing: 0.2em;
    color: #0f5c3a;
    font-weight: 700;
    margin: 0 0 0.75em;
    border-bottom: 0.12em solid #ded9c8;
    padding-bottom: 0.4em;
}

/* ---------------- Page 1 overview cards ---------------- */

.nlsheet .ns-overview {
    width: 100%;
    border-collapse: collapse;
}

.nlsheet .ns-card {
    border: 1px solid #e2ddcc;
    background: #ffffff;
    padding: 0.85em 0.9em 0.7em;
    vertical-align: top;
    width: 33.33%;
}

.nlsheet .ns-card-top {
    margin-bottom: 0.5em;
}

.nlsheet .ns-rank {
    font-size: 1.05em;
    font-weight: 700;
    color: #c79500;
}

.nlsheet .ns-card-org {
    font-weight: 700;
    font-size: 0.95em;
    color: #141414;
    line-height: 1.25;
}

.nlsheet .ns-card-type {
    font-size: 0.66em;
    color: #5c6470;
    letter-spacing: 0.06em;
    margin-top: 0.25em;
}

/* ---------------- Chips ---------------- */

.nlsheet .ns-chip {
    display: inline-block;
    padding: 0.14em 0.65em 0.12em;
    font-size: 0.58em;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-radius: 0.3em;
    vertical-align: middle;
}

.nlsheet .ns-chip-green { background: #0f5c3a; color: #ffffff; }
.nlsheet .ns-chip-amber { background: #9a6b00; color: #ffffff; }
.nlsheet .ns-chip-grey { background: #6f7378; color: #ffffff; }
.nlsheet .ns-chip-gold { background: #f5b800; color: #3a2e00; }
.nlsheet .ns-chip-blue { background: #1d4e89; color: #ffffff; }
.nlsheet .ns-chip-red { background: #b03a2e; color: #ffffff; }

/* ---------------- Feature block ---------------- */

.nlsheet .ns-feature {
    margin-bottom: 1.05em;
    border-bottom: 1px solid #e2ddcc;
    padding-bottom: 0.7em;
}

.nlsheet .ns-feature:last-child {
    border-bottom: none;
}

.nlsheet .ns-kicker {
    font-size: 0.6em;
    letter-spacing: 0.18em;
    color: #b03a2e;
    font-weight: 700;
    margin: 0 0 0.35em;
}

.nlsheet .ns-feature-head {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0.4em;
}

.nlsheet .ns-fh-logo {
    width: 4.4em;
    vertical-align: middle;
    padding-right: 0.8em;
}

.nlsheet .ns-fh-body {
    vertical-align: middle;
}

.nlsheet .ns-orgname {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 1.3em;
    font-weight: 700;
    color: #161b22;
    margin: 0 0 0.15em;
    line-height: 1.2;
}

.nlsheet .ns-meta {
    font-size: 0.7em;
    color: #4b5563;
    margin: 0;
}

.nlsheet .ns-dek {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-style: italic;
    color: #0f5c3a;
    font-size: 0.96em;
    margin: 0.1em 0 0.55em;
}

.nlsheet .ns-body p {
    margin: 0 0 0.65em;
    font-size: 0.88em;
    color: #2b2b2b;
    text-align: justify;
}

.nlsheet .ns-noarticle {
    font-style: italic;
    font-size: 0.8em;
    color: #6b6b6b;
    background: #f4f4f1;
    padding: 0.55em 0.9em;
}

.nlsheet .ns-quote {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-style: italic;
    font-size: 0.98em;
    color: #0f5c3a;
    border-left: 0.32em solid #f5b800;
    background: #fbf8f1;
    padding: 0.6em 0.95em;
    margin: 0.55em 0;
    line-height: 1.5;
}

.nlsheet .ns-why {
    border-left: 0.28em solid #0f5c3a;
    background: #f3f6f2;
    padding: 0.55em 0.85em;
    margin: 0.6em 0;
    font-size: 0.78em;
    color: #2b2b2b;
}

.nlsheet .ns-why ul {
    margin: 0.2em 0 0;
    padding-left: 1.2em;
}

.nlsheet .ns-why li { margin-bottom: 0.15em; }

.nlsheet .ns-facts {
    border-top: 0.22em solid #e2ddcc;
    padding: 0.5em 0 0;
    margin-top: 0.7em;
    font-size: 0.78em;
    color: #3a4048;
}

/* ---------------- Articles ---------------- */

.nlsheet .ns-articles {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.6em;
}

.nlsheet .ns-article td {
    border-top: 1px dashed #d8d3c2;
    padding: 0.55em 0.1em;
    vertical-align: top;
}

.nlsheet .ns-article-title {
    font-weight: 700;
    font-size: 0.92em;
    color: #141414;
    line-height: 1.3;
    margin: 0 0 0.2em;
}

.nlsheet .ns-article-src {
    font-size: 0.66em;
    color: #6b6b6b;
    letter-spacing: 0.06em;
    margin: 0 0 0.3em;
}

.nlsheet .ns-article-sum {
    font-size: 0.82em;
    color: #333333;
    margin: 0 0 0.3em;
}

.nlsheet .ns-article-link {
    font-size: 0.7em;
    color: #1d4e89;
}

.nlsheet .ns-article-photo {
    width: 7.6em;
    padding-left: 0.8em;
    vertical-align: top;
}

/* ---------------- Media assets ---------------- */

.nlsheet .ns-logo-img,
.nlsheet .ns-seal-img {
    display: inline-block;
}

.nlsheet .ns-monogram {
    display: inline-block;
    width: 2.7em;
    height: 2.7em;
    line-height: 2.7em;
    text-align: center;
    border-radius: 50%;
    color: #ffffff;
    font-weight: 700;
    font-size: 1.05em;
    letter-spacing: 0.04em;
    vertical-align: middle;
    border: 0.12em solid rgba(0, 0, 0, 0.12);
}

.nlsheet .ns-panel {
    display: inline-block;
    width: 100%;
    color: #ffffff;
    text-align: center;
    font-weight: 700;
    letter-spacing: 0.1em;
    font-size: 0.78em;
    padding: 0.85em 0.4em;
    border-radius: 0.4em;
    vertical-align: middle;
}

.nlsheet .ns-panel-sm {
    display: inline-block;
    width: 100%;
    color: #ffffff;
    text-align: center;
    font-weight: 700;
    font-size: 0.66em;
    padding: 0.55em 0.3em;
    border-radius: 0.3em;
    vertical-align: middle;
}

/* ---------------- Page footer strip ---------------- */

.nlsheet .ns-pageno {
    border-top: 0.12em solid #e2ddcc;
    margin-top: 0.7em;
    padding-top: 0.45em;
    font-size: 0.62em;
    color: #6b6b6b;
    text-align: center;
    letter-spacing: 0.08em;
}

.nlsheet .ns-footer {
    border-top: 0.22em solid #e2ddcc;
    margin-top: 0.9em;
    padding: 0.65em 0.2em 0;
    font-size: 0.64em;
    color: #6b6b6b;
    line-height: 1.6;
    text-align: center;
}

.nlsheet .ns-seal-row {
    text-align: center;
    margin: 0.6em 0 0.2em;
}

/* ---------------- Density overrides ---------------- */

.nlsheet.nsd1 { font-size: 12.1px; line-height: 1.5; }
.nlsheet.nsd2 { font-size: 11.3px; line-height: 1.46; }
.nlsheet.nsd3 { font-size: 10.5px; line-height: 1.42; }
.nlsheet.nsd4 { font-size: 9.8px; line-height: 1.38; }

.nlsheet.nsd1 .ns-feature { margin-bottom: 0.9em; }
.nlsheet.nsd2 .ns-feature { margin-bottom: 0.7em; }
.nlsheet.nsd3 .ns-feature { margin-bottom: 0.5em; }
.nlsheet.nsd4 .ns-feature { margin-bottom: 0.4em; }