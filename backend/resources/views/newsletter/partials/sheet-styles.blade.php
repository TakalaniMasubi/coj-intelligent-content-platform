<style type="text/css">
    /* ---------- Newsletter sheet: shared web + PDF (Dompdf-safe) ---------- */
    .nlsheet {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #1d1d1b;
        font-size: 11.5px;
        line-height: 1.5;
    }
    .nlsheet table { border-collapse: collapse; }
    .nlsheet img { display: block; }

    .nlsheet .ns-page { padding: 18px 20px 14px; }

    /* Web preview: visible page boundaries --------------------------------- */
    .ns-preview { background: #ece7de; padding: 26px 16px 30px; border-radius: 10px; }
    .ns-preview .nlsheet { background: #ffffff; }
    .ns-preview .nlsheet .ns-page {
        box-shadow: 0 8px 26px rgba(20, 20, 20, 0.18);
        border-top: 8px solid #d7d0c2;
        border-radius: 4px;
        margin-bottom: 26px;
        min-height: 980px;
    }
    .ns-preview .nlsheet .ns-page-1 { border-top-color: transparent; }
    .ns-folio {
        font-size: 10px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        text-align: right;
        color: #8a8478;
        padding: 2px 0 10px;
        border-bottom: 1px solid #e5dfd3;
        margin-bottom: 12px;
    }

    /* PDF page breaks ------------------------------------------------------- */
    .nlsheet .breaks { page-break-after: always; }

    /* Masthead --------------------------------------------------------------- */
    .ns-masthead { border-bottom: 3px double #141414; padding: 6px 0 10px; margin-bottom: 12px; }
    .ns-mast-top { width: 100%; }
    .ns-mast-logo { width: 26%; vertical-align: middle; }
    .ns-mast-seal { width: 12%; vertical-align: middle; text-align: right; }
    .ns-mast-main { vertical-align: middle; text-align: center; padding: 0 12px; }
    .ns-eyebrow {
        font-size: 8.5px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #c8102e;
        font-weight: 700;
        margin: 0 0 4px;
    }
    .ns-title {
        font-size: 25px;
        line-height: 1.15;
        margin: 0 0 3px;
        letter-spacing: -0.01em;
        color: #141414;
    }
    .ns-subtitle { font-size: 11px; color: #55524c; margin: 0 0 5px; }
    .ns-date { font-size: 9.5px; color: #8a8478; margin: 0; letter-spacing: 0.04em; }

    /* Running header (pages 2-3) -------------------------------------------- */
    .ns-rhead {
        width: 100%;
        margin-bottom: 12px;
        border-bottom: 2px solid #141414;
    }
    .ns-rhead td { padding: 0 4px 6px; }
    .ns-rhead-side { font-size: 8.5px; letter-spacing: 0.14em; text-transform: uppercase; color: #8a8478; width: 24%; }
    .ns-rhead-title { font-size: 11px; font-weight: 700; color: #141414; text-align: center; text-transform: uppercase; letter-spacing: 0.12em; }

    /* Introduction ----------------------------------------------------------- */
    .ns-intro {
        font-size: 12.5px;
        line-height: 1.55;
        color: #3a3833;
        padding: 10px 14px;
        margin: 0 0 14px;
        border-left: 4px solid #c8102e;
        background: #faf8f4;
    }

    /* Feature block ---------------------------------------------------------- */
    .ns-feature { }
    .ns-kicker {
        font-size: 9px;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        font-weight: 700;
        color: #c8102e;
        margin: 0 0 8px;
    }
    .ns-org-head { width: 100%; margin-bottom: 8px; }
    .ns-org-logo { width: 12%; vertical-align: middle; }
    .ns-org-body { vertical-align: middle; padding-left: 12px; }
    .ns-orgname { font-size: 19px; line-height: 1.2; margin: 0 0 2px; color: #141414; }
    .ns-orgmeta { font-size: 10px; color: #6d6860; margin: 0; letter-spacing: 0.02em; }
    .ns-dek { font-size: 12px; line-height: 1.5; color: #3a3833; font-style: italic; margin: 6px 0 8px; }
    .ns-dek-center { text-align: center; }
    .ns-body {
        font-size: 11.5px;
        line-height: 1.55;
        color: #2c2a26;
        margin: 0 0 8px;
        padding: 8px 12px;
        background: #faf8f4;
        border-left: 3px solid #d4a017;
    }
    .ns-body p { margin: 0; }
    .ns-facts {
        font-size: 10px;
        color: #6d6860;
        border-top: 1px solid #e5dfd3;
        border-bottom: 1px solid #e5dfd3;
        padding: 5px 0;
        margin: 0 0 10px;
        letter-spacing: 0.03em;
    }
    .ns-facts strong { color: #141414; }

    /* Article ------------------------------------------------------------------ */
    .ns-article { margin-top: 4px; }
    .ns-art-row { width: 100%; }
    .ns-art-body { vertical-align: top; padding-right: 14px; }
    .ns-art-photo { width: 47%; vertical-align: top; }
    .ns-art-hero { text-align: center; margin-bottom: 10px; }
    .ns-art-kicker {
        font-size: 8.5px;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-weight: 700;
        color: #a67c00;
        margin: 0 0 4px;
    }
    .ns-article-title { font-size: 15.5px; line-height: 1.25; margin: 0 0 4px; color: #141414; }
    .ns-article-src { font-size: 9px; color: #8a8478; margin: 0 0 6px; }
    .ns-article-sum { font-size: 11.5px; line-height: 1.55; color: #2c2a26; margin: 0; }
    .ns-article-link { font-size: 9px; color: #6d6860; margin: 8px 0 0 0; word-break: break-all; }
    .ns-art-secondary {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid #e5dfd3;
        font-size: 11px;
        color: #3a3833;
    }
    .ns-art-second-label {
        font-size: 8px;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #c8102e;
        font-weight: 700;
        margin-right: 8px;
    }
    .ns-art-second-title { font-weight: 700; color: #141414; }
    .ns-art-second-src { color: #8a8478; font-size: 9.5px; margin-left: 8px; }

    .ns-noarticle {
        font-size: 11.5px;
        color: #6d6860;
        padding: 14px 16px;
        border: 1px dashed #cfc8ba;
        background: #faf8f4;
        font-style: italic;
    }

    /* Media assets ------------------------------------------------------------- */
    .ns-logo-img { max-width: 100%; }
    .ns-seal-img { max-width: 100%; }
    .ns-img-hero { max-width: 100%; margin: 0 auto; }
    .ns-monogram {
        display: inline-block;
        width: 52px;
        height: 52px;
        line-height: 52px;
        text-align: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 17px;
        letter-spacing: 0.02em;
    }
    .ns-monogram-hero {
        display: block;
        width: 96px;
        height: 96px;
        line-height: 96px;
        text-align: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 30px;
        margin: 0 auto;
    }
    .ns-panel {
        display: inline-block;
        min-height: 130px;
        color: #ffffff;
        text-align: center;
        font-size: 10px;
        letter-spacing: 0.06em;
        vertical-align: middle;
    }
    .ns-panel-hero {
        display: block;
        min-height: 190px;
        color: #ffffff;
        text-align: center;
        line-height: 190px;
        font-size: 12px;
        letter-spacing: 0.08em;
    }
    .ns-panel-sm {
        display: inline-block;
        min-height: 40px;
        color: #ffffff;
        text-align: center;
        font-size: 9px;
    }

    /* Layout specific --------------------------------------------------------- */
    .ns-feature-magazine .ns-mag-name {
        font-size: 26px;
        letter-spacing: -0.01em;
        color: #141414;
        margin: 0 0 2px;
        border-bottom: 2px solid #141414;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }
    .ns-spotlight-head { text-align: center; padding: 10px 0 8px; }
    .ns-spotlight-logo { text-align: center; padding-bottom: 8px; }
    .ns-quote {
        font-size: 12px;
        font-style: italic;
        color: #6d6860;
        text-align: center;
        margin: 12px 0 0;
        border-top: 1px solid #e5dfd3;
        padding-top: 10px;
    }

    /* Page footer --------------------------------------------------------------- */
    .ns-pagefoot {
        width: 100%;
        margin-top: 16px;
        border-top: 2px solid #141414;
        font-size: 8.5px;
        letter-spacing: 0.06em;
        color: #8a8478;
    }
    .ns-pagefoot td { padding: 5px 4px 0; }
    .ns-pagefoot-left { text-align: left; }
    .ns-pagefoot-mid { text-align: center; }
    .ns-pagefoot-right { text-align: right; }

    /* Colophon ----------------------------------------------------------------- */
    .ns-colophon {
        margin-top: 18px;
        padding-top: 12px;
        border-top: 1px solid #e5dfd3;
        text-align: center;
        color: #8a8478;
    }
    .ns-seal-row { text-align: center; padding-bottom: 6px; }
    .ns-colophon-notes { font-size: 12px; font-style: italic; color: #3a3833; margin: 0 0 6px; }
    .ns-colophon-src { font-size: 8.5px; line-height: 1.5; margin: 0; letter-spacing: 0.03em; }

    /* Density: PDF regeneration steps (smaller text) ----------------------------- */
    .nlsheet.nsd1 { font-size: 11px; }
    .nlsheet.nsd1 .ns-title { font-size: 23px; }
    .nlsheet.nsd1 .ns-intro { font-size: 11.5px; }
    .nlsheet.nsd1 .ns-orgname { font-size: 17.5px; }
    .nlsheet.nsd1 .ns-article-title { font-size: 14.5px; }
    .nlsheet.nsd1 .ns-article-sum { font-size: 11px; }
    .nlsheet.nsd1 .ns-body { font-size: 11px; }
    .nlsheet.nsd1 .ns-article-src { font-size: 8.5px; }

    .nlsheet.nsd2 { font-size: 10.5px; }
    .nlsheet.nsd2 .ns-title { font-size: 21px; }
    .nlsheet.nsd2 .ns-intro { font-size: 11px; }
    .nlsheet.nsd2 .ns-orgname { font-size: 16px; }
    .nlsheet.nsd2 .ns-article-title { font-size: 13.5px; }
    .nlsheet.nsd2 .ns-article-sum { font-size: 10.5px; }
    .nlsheet.nsd2 .ns-body { font-size: 10.5px; }
    .nlsheet.nsd2 .ns-article-src { font-size: 8px; }

    .nlsheet.nsd3 { font-size: 10px; }
    .nlsheet.nsd3 .ns-title { font-size: 19px; }
    .nlsheet.nsd3 .ns-intro { font-size: 10.5px; }
    .nlsheet.nsd3 .ns-orgname { font-size: 14.5px; }
    .nlsheet.nsd3 .ns-article-title { font-size: 12.5px; }
    .nlsheet.nsd3 .ns-article-sum { font-size: 10px; }
    .nlsheet.nsd3 .ns-body { font-size: 10px; }
    .nlsheet.nsd3 .ns-article-src { font-size: 7.5px; }

    .nlsheet.nsd4 { font-size: 9.5px; }
    .nlsheet.nsd4 .ns-title { font-size: 17px; }
    .nlsheet.nsd4 .ns-intro { font-size: 10px; }
    .nlsheet.nsd4 .ns-orgname { font-size: 13px; }
    .nlsheet.nsd4 .ns-article-title { font-size: 11.5px; }
    .nlsheet.nsd4 .ns-article-sum { font-size: 9px; }
    .nlsheet.nsd4 .ns-body { font-size: 9.5px; }
    .nlsheet.nsd4 .ns-article-src { font-size: 7px; }

    /* Print cleanup ---------------------------------------------------------------- */
    @media print {
        body { background: #ffffff !important; }
        .ns-preview { background: #ffffff !important; padding: 0 !important; border-radius: 0 !important; }
        .ns-preview .nlsheet .ns-page {
            box-shadow: none !important;
            margin: 0 !important;
            min-height: 0 !important;
        }
    }
</style>