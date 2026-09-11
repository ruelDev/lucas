/* eslint-disable react/prop-types */
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_META = {
    403: { label: 'Forbidden', title: 'Access Denied' },
    404: { label: 'Not Found', title: 'Page Not Found' },
    419: { label: 'Page Expired', title: 'Session Expired' },
    429: { label: 'Too Many Requests', title: 'Slow Down' },
    500: { label: 'Server Error', title: 'Something Went Wrong' },
    503: { label: 'Service Unavailable', title: 'Under Maintenance' },
};

export default function ErrorPage({ status, message, debug }) {
    const meta = STATUS_META[status] ?? { label: 'Error', title: 'An Error Occurred' };
    const isDev = Boolean(debug);

    return (
        <>
            <Head title={`${status} — ${meta.label}`} />

            <div style={s.root}>
                {/* Background layers */}
                <div style={s.bgGrid} aria-hidden />
                <div style={s.blobOrange} aria-hidden />
                <div style={s.blobBlue} aria-hidden />
                <div style={s.blobCenter} aria-hidden />
                <div style={s.scanline} aria-hidden />
                <div style={s.cornerTL} aria-hidden />
                <div style={s.cornerBR} aria-hidden />

                <main style={s.card}>
                    {isDev ? (
                        <DevError status={status} meta={meta} message={message} debug={debug} />
                    ) : (
                        <ProdError status={status} meta={meta} message={message} />
                    )}
                </main>
            </div>

            <style>{keyframes}</style>
        </>
    );
}

/* ── Production View ──────────────────────────────────────── */
function ProdError({ status, meta, message }) {
    const [refreshing, setRefreshing] = useState(false);

    const handleRefresh = () => {
        setRefreshing(true);
        if (navigator.onLine) window.location.reload();
        else alert('You appear to be offline.');
    };

    return (
        <div style={s.prodWrap}>
            {/* Logo */}
            <div style={s.bankHeader}>
                <img src="/assets/images/bmi-logo-2.png" alt="Bank of Makati" style={s.logo} />
            </div>

            {/* Error code */}
            <div style={s.errorCode} className="bmi-code">
                {status}
            </div>

            {/* Status badge */}
            <div style={s.badge}>
                <span style={s.badgeDot} className="bmi-blink" />
                {status} · {meta.label}
            </div>

            <h1 style={s.title}>{meta.title}</h1>
            <p style={s.msg}>{message}</p>

            {/* Contact */}
            {status == 500 && (
                <div style={s.contactBox}>
                    <div style={s.contactItem}>
                        <strong style={s.contactLabel}>📞 Local:</strong>
                        <a href="tel:2617" style={s.contactLink}>
                            2617
                        </a>
                    </div>
                    <div style={s.contactItem}>
                        <strong style={s.contactLabel}>📱 Mobile:</strong>
                        <a href="tel:+639189517032" style={s.contactLink}>
                            09189517032
                        </a>
                    </div>
                </div>
            )}

            {/* Actions */}
            <div style={s.btnRow}>
                <button onClick={handleRefresh} disabled={refreshing} style={s.btnPrimary}>
                    {refreshing ? '↺ Refreshing…' : '↺ Try Again'}
                </button>
                <button onClick={() => router.visit('/')} style={s.btnSecondary}>
                    ← Go Home
                </button>
            </div>
        </div>
    );
}

/* ── Development View ─────────────────────────────────────── */
function DevError({ status, meta, message, debug }) {
    const [traceOpen, setTraceOpen] = useState(true);

    return (
        <div style={s.devWrap}>
            {/* Dev header bar */}
            <div style={s.devHeader}>
                <div style={s.bankHeaderDev}>
                    <img src="/assets/images/bmi-logo-2.png" alt="Bank of Makati" style={s.logoDev} />
                </div>
                <div style={s.devHeaderRow}>
                    <span style={s.devBadge}>{status}</span>
                    <span style={s.devClass}>{debug.exception}</span>
                    <span style={s.devEnvBadge}>⚙ DEV MODE</span>
                </div>
            </div>

            {/* Exception message */}
            <div style={s.devMsgBlock}>
                <p style={s.devMsg}>{debug.message || message}</p>
                <p style={s.devLocation}>
                    <span style={s.devFile}>{debug.file}</span>
                    <span style={s.devLine}>:{debug.line}</span>
                </p>
            </div>

            {/* Stack trace */}
            <div style={s.traceSection}>
                <button onClick={() => setTraceOpen((o) => !o)} style={s.traceToggle}>
                    <span>{traceOpen ? '▾' : '▸'}</span>
                    <span>Stack Trace</span>
                    <span style={s.traceCount}>{debug.trace.length} frames</span>
                </button>

                {traceOpen && (
                    <div style={s.traceList}>
                        {debug.trace.map((frame, i) => (
                            <TraceFrame key={`${frame.file}:${frame.line}`} frame={frame} index={i} />
                        ))}
                    </div>
                )}
            </div>

            {/* Footer */}
            <div style={s.devFooter}>
                <button onClick={() => router.visit('/')} style={s.btnPrimary}>
                    ← Go Home
                </button>
                <button onClick={() => window.location.reload()} style={s.btnSecondary}>
                    ↺ Retry
                </button>
            </div>
        </div>
    );
}

function TraceFrame({ frame, index }) {
    const isVendor = frame.file?.includes('/vendor/') || frame.file === '[internal]';
    return (
        <div
            style={{
                ...s.frame,
                opacity: isVendor ? 0.4 : 1,
                borderLeft: index === 0 ? '3px solid #f26531' : '3px solid transparent',
                background: index === 0 ? 'rgba(242,101,49,0.05)' : 'transparent',
            }}
        >
            <span style={s.frameIndex}>{index}</span>
            <div style={s.frameBody}>
                <span style={s.frameFunc}>{frame.function}</span>
                {frame.args && <span style={s.frameArgs}>({frame.args})</span>}
                <span style={s.frameFile}>
                    {frame.file}
                    {frame.line ? `:${frame.line}` : ''}
                </span>
            </div>
        </div>
    );
}

/* ── Keyframe CSS ─────────────────────────────────────────── */
const keyframes = `
  @keyframes blobDrift {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(40px,40px) scale(1.1); }
  }
  @keyframes blobDriftR {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(-40px,-40px) scale(1.1); }
  }
  @keyframes blobPulse {
    0%,100% { opacity:0.4; transform:translate(-50%,-50%) scale(1); }
    50%     { opacity:0.9; transform:translate(-50%,-50%) scale(1.25); }
  }
  @keyframes cardIn {
    from { opacity:0; transform:translateY(28px) scale(0.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
  }
  @keyframes codeIn {
    from { opacity:0; transform:translateY(-20px); }
    to   { opacity:1; transform:translateY(0); }
  }
  @keyframes glitch {
    0%,82%,100% { transform:translate(0); }
    84%         { transform:translate(-2px,-1px); }
    86%         { transform:translate(2px,1px); }
    88%         { transform:translate(-1px,2px); }
    90%         { transform:translate(1px,-1px); }
  }
  .bmi-code {
    animation: codeIn 0.8s 0.3s cubic-bezier(0.16,1,0.3,1) both,
               glitch 5s 1s infinite;
  }
  @keyframes blink {
    0%,100% { opacity:1; }
    50%     { opacity:0.15; }
  }
  .bmi-blink { animation: blink 1.4s ease-in-out infinite; }
`;

/* ── Styles ───────────────────────────────────────────────── */
const BMI_ORANGE = '#f26531';
const BMI_BLUE = '#193cb8';
const SURFACE = '#09090f';
const BORDER = 'rgba(255,255,255,0.07)';
const TEXT = '#f1f5f9';
const MUTED = '#94a3b8';

const s = {
    /* Background */
    root: {
        minHeight: '100vh',
        background: SURFACE,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontFamily: 'sans-serif',
        padding: '2rem',
        position: 'relative',
        overflow: 'hidden',
    },
    bgGrid: {
        position: 'fixed',
        inset: 0,
        zIndex: 0,
        backgroundImage: `
      linear-gradient(rgba(255,255,255,0.028) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.028) 1px, transparent 1px)
    `,
        backgroundSize: '44px 44px',
        maskImage: 'radial-gradient(ellipse 90% 80% at 50% 50%, black 30%, transparent 100%)',
    },
    blobOrange: {
        position: 'fixed',
        zIndex: 0,
        pointerEvents: 'none',
        width: 500,
        height: 500,
        borderRadius: '50%',
        background: `radial-gradient(circle, rgba(242,101,49,0.18) 0%, transparent 70%)`,
        filter: 'blur(90px)',
        top: -100,
        left: -100,
        animation: 'blobDrift 12s ease-in-out infinite alternate',
    },
    blobBlue: {
        position: 'fixed',
        zIndex: 0,
        pointerEvents: 'none',
        width: 600,
        height: 600,
        borderRadius: '50%',
        background: `radial-gradient(circle, rgba(25,60,184,0.2) 0%, transparent 70%)`,
        filter: 'blur(90px)',
        bottom: -150,
        right: -100,
        animation: 'blobDriftR 15s ease-in-out infinite alternate',
    },
    blobCenter: {
        position: 'fixed',
        zIndex: 0,
        pointerEvents: 'none',
        width: 300,
        height: 300,
        borderRadius: '50%',
        background: `radial-gradient(circle, rgba(242,101,49,0.07) 0%, transparent 70%)`,
        filter: 'blur(60px)',
        top: '50%',
        left: '50%',
        animation: 'blobPulse 8s ease-in-out infinite',
    },
    scanline: {
        position: 'fixed',
        inset: 0,
        zIndex: 0,
        pointerEvents: 'none',
        background: 'repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.04) 2px, rgba(0,0,0,0.04) 4px)',
    },
    cornerTL: {
        position: 'fixed',
        zIndex: 0,
        pointerEvents: 'none',
        top: 24,
        left: 24,
        width: 160,
        height: 160,
        borderTop: `1px solid ${BMI_ORANGE}`,
        borderLeft: `1px solid ${BMI_ORANGE}`,
        borderRadius: 4,
        opacity: 0.35,
    },
    cornerBR: {
        position: 'fixed',
        zIndex: 0,
        pointerEvents: 'none',
        bottom: 24,
        right: 24,
        width: 160,
        height: 160,
        borderBottom: `1px solid ${BMI_BLUE}`,
        borderRight: `1px solid ${BMI_BLUE}`,
        borderRadius: 4,
        opacity: 0.35,
    },

    /* Card */
    card: {
        position: 'relative',
        zIndex: 10,
        width: '100%',
        maxWidth: 680,
        background: 'rgba(15,15,26,0.75)',
        borderRadius: 20,
        border: `1px solid ${BORDER}`,
        backdropFilter: 'blur(24px)',
        boxShadow: `0 0 0 1px rgba(255,255,255,0.03), 0 32px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.06)`,
        overflow: 'hidden',
        animation: 'cardIn 0.7s cubic-bezier(0.16,1,0.3,1) both',
    },

    /* Prod */
    prodWrap: { padding: '3rem 2.5rem', textAlign: 'center' },
    bankHeader: {
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: '2rem',
        paddingBottom: '1.5rem',
        borderBottom: `1px solid ${BORDER}`,
    },
    logo: {
        height: 80,
        filter: 'drop-shadow(0 4px 16px rgba(242,101,49,0.3))',
    },
    errorCode: {
        fontFamily: 'sans-serif',
        fontSize: '7rem',
        fontWeight: 900,
        lineHeight: 1,
        marginBottom: '0.5rem',
        background: `linear-gradient(135deg, ${BMI_ORANGE} 0%, #ff9a6c 40%, ${BMI_BLUE} 100%)`,
        WebkitBackgroundClip: 'text',
        WebkitTextFillColor: 'transparent',
        backgroundClip: 'text',
        filter: 'drop-shadow(0 0 30px rgba(242,101,49,0.25))',
    },
    badge: {
        display: 'inline-flex',
        alignItems: 'center',
        gap: '0.4rem',
        background: 'rgba(242,101,49,0.12)',
        border: '1px solid rgba(242,101,49,0.3)',
        color: BMI_ORANGE,
        padding: '0.3rem 1rem',
        borderRadius: 999,
        fontSize: '0.72rem',
        fontWeight: 600,
        letterSpacing: '0.1em',
        textTransform: 'uppercase',
        marginBottom: '1.25rem',
    },
    badgeDot: {
        width: 6,
        height: 6,
        background: BMI_ORANGE,
        borderRadius: '50%',
        display: 'inline-block',
    },
    title: {
        fontFamily: 'sans-serif',
        fontSize: '1.75rem',
        fontWeight: 800,
        color: TEXT,
        marginBottom: '0.75rem',
        letterSpacing: '-0.02em',
    },
    msg: {
        fontSize: '1rem',
        color: MUTED,
        lineHeight: 1.7,
        maxWidth: 440,
        margin: '0 auto 1.75rem',
    },
    contactBox: {
        background: 'rgba(25,60,184,0.1)',
        border: '1px solid rgba(25,60,184,0.25)',
        borderRadius: 12,
        padding: '1.25rem 1.5rem',
        marginBottom: '2rem',
        display: 'flex',
        gap: '1rem',
        justifyContent: 'center',
        flexWrap: 'wrap',
    },
    contactItem: { display: 'flex', alignItems: 'center', gap: '0.4rem', fontSize: '0.95rem', color: MUTED },
    contactLabel: { color: TEXT },
    contactLink: { color: '#93c5fd', textDecoration: 'none', fontWeight: 600 },
    btnRow: { display: 'flex', gap: '0.75rem', justifyContent: 'center', flexWrap: 'wrap' },
    btnPrimary: {
        padding: '0.7rem 1.6rem',
        borderRadius: 10,
        fontSize: '0.9rem',
        fontWeight: 600,
        cursor: 'pointer',
        border: 'none',
        color: '#fff',
        background: `linear-gradient(135deg, ${BMI_ORANGE}, #d4521f)`,
        boxShadow: '0 4px 20px rgba(242,101,49,0.35)',
        fontFamily: 'sans-serif',
        transition: 'all 0.2s',
    },
    btnSecondary: {
        padding: '0.7rem 1.6rem',
        borderRadius: 10,
        fontSize: '0.9rem',
        fontWeight: 600,
        cursor: 'pointer',
        border: `1px solid ${BORDER}`,
        background: 'transparent',
        color: MUTED,
        fontFamily: 'sans-serif',
        transition: 'all 0.2s',
    },

    /* Dev */
    devWrap: { display: 'flex', flexDirection: 'column' },
    devHeader: {
        background: '#0a0a12',
        borderBottom: `2px solid ${BMI_ORANGE}`,
    },
    bankHeaderDev: {
        display: 'flex',
        justifyContent: 'center',
        padding: '1.25rem',
        borderBottom: `1px solid ${BORDER}`,
    },
    logoDev: { height: 52, filter: 'drop-shadow(0 2px 8px rgba(242,101,49,0.25))' },
    devHeaderRow: {
        display: 'flex',
        alignItems: 'center',
        gap: '1rem',
        padding: '0.75rem 1.5rem',
        flexWrap: 'wrap',
    },
    devBadge: {
        padding: '0.25rem 0.75rem',
        borderRadius: 6,
        fontSize: '0.8rem',
        fontWeight: 700,
        color: '#fff',
        background: BMI_ORANGE,
    },
    devClass: { color: TEXT, fontSize: '0.88rem', flex: 1, wordBreak: 'break-all' },
    devEnvBadge: {
        fontSize: '0.7rem',
        fontWeight: 700,
        letterSpacing: '0.1em',
        padding: '0.25rem 0.6rem',
        borderRadius: 4,
        background: 'rgba(34,197,94,0.15)',
        color: '#22c55e',
    },
    devMsgBlock: { padding: '1.5rem', borderBottom: `1px solid ${BORDER}` },
    devMsg: { color: TEXT, fontSize: '1.05rem', lineHeight: 1.5, margin: '0 0 0.75rem', fontWeight: 500 },
    devLocation: { margin: 0, fontSize: '0.8rem' },
    devFile: { color: '#7dd3fc' },
    devLine: { color: '#f472b6', fontWeight: 700 },
    traceSection: { borderBottom: `1px solid ${BORDER}` },
    traceToggle: {
        width: '100%',
        padding: '1rem 1.5rem',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        color: MUTED,
        fontSize: '0.85rem',
        fontWeight: 600,
        display: 'flex',
        alignItems: 'center',
        gap: '0.5rem',
        textAlign: 'left',
        letterSpacing: '0.04em',
        fontFamily: 'sans-serif',
    },
    traceCount: {
        marginLeft: 'auto',
        background: 'rgba(255,255,255,0.08)',
        padding: '0.15rem 0.5rem',
        borderRadius: 4,
        fontSize: '0.7rem',
    },
    traceList: { maxHeight: 320, overflowY: 'auto', borderTop: `1px solid ${BORDER}` },
    frame: {
        display: 'flex',
        gap: '1rem',
        padding: '0.65rem 1.5rem',
        borderBottom: `1px solid rgba(255,255,255,0.03)`,
        alignItems: 'flex-start',
    },
    frameIndex: { color: '#334155', fontSize: '0.7rem', fontWeight: 700, minWidth: 20, paddingTop: 2, textAlign: 'right' },
    frameBody: { display: 'flex', flexDirection: 'column', gap: '0.15rem', minWidth: 0 },
    frameFunc: { color: '#7dd3fc', fontSize: '0.82rem', fontWeight: 600, wordBreak: 'break-all' },
    frameArgs: { color: '#64748b', fontSize: '0.75rem' },
    frameFile: { color: '#475569', fontSize: '0.73rem', wordBreak: 'break-all' },
    devFooter: { padding: '1.25rem 1.5rem', display: 'flex', gap: '1rem' },
};
