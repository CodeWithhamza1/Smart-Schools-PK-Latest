<style>
.ss-ai-launcher,.ss-ai-widget,.ss-ai-widget *{box-sizing:border-box}
.ss-ai-launcher,.ss-ai-widget{--ai-a:#667eea;--ai-b:#764ba2;--ai-c:#06b6d4;--ai-grad:linear-gradient(135deg,#667eea 0%,#764ba2 100%);--ai-bg:#f6f7fc;--ai-panel:#fff;--ai-line:#e7e9f3;--ai-text:#1c2033;--ai-muted:#7a8199;--ai-soft:#f0f2fb;font-family:'Segoe UI',Inter,system-ui,-apple-system,Roboto,Arial,sans-serif}
.ss-ai-widget button{font-family:inherit}

/* ---------- launcher ---------- */
.ss-ai-launcher{position:fixed;right:24px;bottom:24px;z-index:99998;display:flex;align-items:center;justify-content:center;width:60px;height:60px;padding:0;border:0;border-radius:50%;background:var(--ai-grad);color:#fff;cursor:pointer;box-shadow:0 14px 30px rgba(102,126,234,.45),inset 0 1px 0 rgba(255,255,255,.35);transition:transform .2s ease,box-shadow .2s ease}
.ss-ai-launcher:hover{transform:translateY(-3px) scale(1.04);box-shadow:0 18px 38px rgba(102,126,234,.55),inset 0 1px 0 rgba(255,255,255,.35)}
.ss-ai-launcher:focus-visible,.ss-ai-widget button:focus-visible,.ss-ai-widget textarea:focus-visible{outline:2px solid #8b9cff;outline-offset:2px}
.ss-ai-launcher svg{position:relative;width:28px;height:28px}
.ss-ai-launcher-glow{position:absolute;inset:-6px;border-radius:50%;background:conic-gradient(from 0deg,#667eea,#06b6d4,#f093fb,#764ba2,#667eea);filter:blur(10px);opacity:.55;z-index:-1;animation:ssAiSpin 6s linear infinite}
.ss-ai-launcher-tip{position:absolute;right:72px;top:50%;transform:translateY(-50%) translateX(6px);padding:7px 12px;border-radius:10px;background:#1c2033;color:#fff;font-size:12.5px;font-weight:600;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .2s ease,transform .2s ease}
.ss-ai-launcher:hover .ss-ai-launcher-tip{opacity:1;transform:translateY(-50%)}

/* ---------- panel ---------- */
.ss-ai-widget{position:fixed;right:24px;bottom:24px;z-index:99999;display:none;flex-direction:column;width:420px;max-width:calc(100vw - 32px);height:min(680px,calc(100vh - 48px));overflow:hidden;border:1px solid rgba(231,233,243,.9);border-radius:22px;background:var(--ai-panel);color:var(--ai-text);box-shadow:0 30px 80px rgba(28,32,51,.28),0 4px 14px rgba(28,32,51,.08);transition:width .25s ease,height .25s ease}
.ss-ai-widget.open{display:flex;animation:ssAiPop .28s cubic-bezier(.2,.9,.3,1.1)}
.ss-ai-widget.is-wide{width:760px;height:min(760px,calc(100vh - 48px))}

.ss-ai-header{position:relative;display:flex;align-items:center;gap:10px;padding:14px 14px 14px 16px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;overflow:hidden}
.ss-ai-header:before{content:"";position:absolute;right:-40px;top:-60px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.28),transparent 65%);pointer-events:none}
.ss-ai-brand{position:relative;display:flex;align-items:center;gap:11px;min-width:0;flex:1}
.ss-ai-brand h3{margin:0;font-size:15px;font-weight:700;letter-spacing:.01em;color:#fff}
.ss-ai-brand p{display:flex;align-items:center;gap:6px;margin:2px 0 0;font-size:11.5px;color:rgba(255,255,255,.82)}
.ss-ai-dot{width:7px;height:7px;border-radius:50%;background:#4ade80;box-shadow:0 0 0 3px rgba(74,222,128,.25);animation:ssAiPulse 2s ease-in-out infinite}
.ss-ai-actions{position:relative;display:flex;gap:4px}
.ss-ai-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;padding:0;border:0;border-radius:10px;background:rgba(255,255,255,.14);color:#fff;cursor:pointer;transition:background .15s ease}
.ss-ai-icon:hover{background:rgba(255,255,255,.28)}
.ss-ai-icon svg{width:17px;height:17px}

/* orb avatar */
.ss-ai-orb{position:relative;flex:none;width:36px;height:36px;border-radius:50%;background:conic-gradient(from 0deg,#fff,#a5b4fc,#f0abfc,#67e8f9,#fff);animation:ssAiSpin 7s linear infinite}
.ss-ai-orb:after{content:"";position:absolute;inset:3px;border-radius:50%;background:radial-gradient(circle at 30% 25%,rgba(255,255,255,.85),rgba(255,255,255,.1) 55%),linear-gradient(135deg,#667eea,#764ba2)}
.ss-ai-orb.sm{width:28px;height:28px}.ss-ai-orb.sm:after{inset:2.5px}
.ss-ai-orb.lg{width:72px;height:72px;filter:drop-shadow(0 10px 22px rgba(102,126,234,.5))}.ss-ai-orb.lg:after{inset:5px}

/* ---------- body ---------- */
.ss-ai-body{position:relative;flex:1;min-height:0;display:flex}
.ss-ai-chat{flex:1;min-width:0;overflow-y:auto;padding:18px 16px 8px;display:flex;flex-direction:column;gap:14px;background:radial-gradient(120% 60% at 0 0,rgba(102,126,234,.07),transparent 60%),var(--ai-bg);scroll-behavior:smooth}
.ss-ai-chat::-webkit-scrollbar{width:7px}.ss-ai-chat::-webkit-scrollbar-thumb{background:#d4d8ea;border-radius:7px}

/* welcome */
.ss-ai-hero{margin:auto;display:flex;flex-direction:column;align-items:center;gap:6px;padding:10px 4px;text-align:center;animation:ssAiBlock .5s both}
.ss-ai-hero h2{margin:12px 0 0;font-size:21px;font-weight:700;color:var(--ai-text);background:var(--ai-grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.ss-ai-hero p{margin:0;max-width:300px;font-size:13px;line-height:1.55;color:var(--ai-muted)}
.ss-ai-chips{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:14px}
.ss-ai-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border:1px solid var(--ai-line);border-radius:999px;background:#fff;color:#3b4262;font-size:12.5px;font-weight:600;cursor:pointer;box-shadow:0 2px 6px rgba(28,32,51,.04);transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease,color .15s ease}
.ss-ai-chip:hover{transform:translateY(-2px);border-color:#b7c0f5;color:#4a5ad6;box-shadow:0 8px 18px rgba(102,126,234,.18)}

/* messages */
.ss-ai-row{display:flex;gap:9px;align-items:flex-end;animation:ssAiBlock .3s both}
.ss-ai-row.user{justify-content:flex-end}
.ss-ai-col{min-width:0;max-width:86%;display:flex;flex-direction:column;gap:5px}
.ss-ai-row.user .ss-ai-col{align-items:flex-end}
.ss-ai-bubble{padding:11px 14px;border-radius:16px;font-size:13.5px;line-height:1.6;overflow-wrap:anywhere}
.ss-ai-row.user .ss-ai-bubble{background:var(--ai-grad);color:#fff;border-bottom-right-radius:5px;box-shadow:0 8px 18px rgba(102,126,234,.28);white-space:pre-wrap}
.ss-ai-row.ai .ss-ai-bubble{background:#fff;border:1px solid var(--ai-line);border-bottom-left-radius:5px;box-shadow:0 4px 14px rgba(28,32,51,.05)}
.ss-ai-row.error .ss-ai-bubble{background:#fff6ed;border:1px solid #f6d3ab;color:#8a4b00;border-bottom-left-radius:5px}
.ss-ai-bubble p{margin:0}.ss-ai-bubble p+p,.ss-ai-bubble p+ul,.ss-ai-bubble ul+p,.ss-ai-bubble p+ol,.ss-ai-bubble ol+p,.ss-ai-bubble .ss-ai-h+*{margin-top:8px}
.ss-ai-bubble .ss-ai-h{font-weight:700;color:var(--ai-text)}
.ss-ai-bubble ul,.ss-ai-bubble ol{margin:0;padding-left:0;list-style:none}
.ss-ai-bubble ul li{position:relative;padding:3px 0 3px 18px}
.ss-ai-bubble ul li:before{content:"";position:absolute;left:3px;top:11px;width:7px;height:7px;border-radius:50%;background:var(--ai-grad)}
.ss-ai-bubble ol{counter-reset:n}.ss-ai-bubble ol li{position:relative;padding:3px 0 3px 26px;counter-increment:n}
.ss-ai-bubble ol li:before{content:counter(n);position:absolute;left:0;top:4px;width:18px;height:18px;border-radius:6px;background:var(--ai-soft);color:var(--ai-a);font-size:11px;font-weight:700;line-height:18px;text-align:center}
.ss-ai-bubble code{padding:1px 6px;border-radius:6px;background:var(--ai-soft);color:#4a5ad6;font:12px Consolas,monospace}
.ss-ai-money{display:inline-block;padding:0 6px;border-radius:7px;background:#ecfdf3;color:#0b7a43;font-weight:700;font-variant-numeric:tabular-nums}
.ss-ai-table-wrap{overflow-x:auto;margin-top:8px;border:1px solid var(--ai-line);border-radius:10px}
.ss-ai-bubble table{width:100%;border-collapse:collapse;font-size:12.5px}
.ss-ai-bubble th{padding:7px 10px;background:var(--ai-soft);color:#4b5373;font-size:11px;letter-spacing:.04em;text-align:left;text-transform:uppercase;white-space:nowrap}
.ss-ai-bubble td{padding:7px 10px;border-top:1px solid var(--ai-line);white-space:nowrap}
.ss-ai-reveal>*{animation:ssAiBlock .5s both;animation-delay:min(calc(var(--i,0)*90ms),1.6s)}
.ss-ai-meta{display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:0 4px;font-size:11px;color:var(--ai-muted)}
.ss-ai-src{display:inline-flex;align-items:center;gap:5px;padding:2px 9px;border-radius:999px;background:var(--ai-soft);color:#5b66a8;font-weight:600}
.ss-ai-src svg{width:11px;height:11px}
.ss-ai-time{opacity:.85}
.ss-ai-copy{display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border:0;border-radius:7px;background:transparent;color:var(--ai-muted);font-size:11px;cursor:pointer;opacity:0;transition:opacity .15s ease,background .15s ease}
.ss-ai-row:hover .ss-ai-copy,.ss-ai-copy:focus-visible{opacity:1}.ss-ai-copy:hover{background:var(--ai-soft);color:var(--ai-a)}.ss-ai-copy svg{width:12px;height:12px}
.ss-ai-retry{margin-top:9px;padding:6px 13px;border:1px solid #e3bd8a;border-radius:9px;background:#fff;color:#8a4b00;font-size:12px;font-weight:600;cursor:pointer}.ss-ai-retry:hover{background:#fff1de}

/* thinking */
.ss-ai-think .ss-ai-bubble{display:flex;align-items:center;gap:11px;min-width:210px}
.ss-ai-think-text{font-size:13px;font-weight:600;background:linear-gradient(90deg,#8a93b8 0%,#667eea 40%,#764ba2 60%,#8a93b8 100%);background-size:220% 100%;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:ssAiShimmer 1.8s linear infinite}
.ss-ai-dots{display:inline-flex;gap:4px;margin-left:auto}.ss-ai-dots i{width:6px;height:6px;border-radius:50%;background:var(--ai-a);animation:ssAiBounce 1.1s ease-in-out infinite}.ss-ai-dots i:nth-child(2){animation-delay:.15s}.ss-ai-dots i:nth-child(3){animation-delay:.3s}

.ss-ai-jump{position:absolute;left:50%;bottom:10px;z-index:3;display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border:0;border-radius:999px;background:#1c2033;color:#fff;font-size:12px;font-weight:600;cursor:pointer;box-shadow:0 8px 20px rgba(28,32,51,.3);transform:translateX(-50%)}
.ss-ai-jump[hidden]{display:none}.ss-ai-jump svg{width:13px;height:13px}

/* history drawer */
.ss-ai-scrim{position:absolute;inset:0;z-index:4;background:rgba(28,32,51,.35);opacity:0;pointer-events:none;transition:opacity .2s ease}
.ss-ai-drawer{position:absolute;top:0;bottom:0;left:0;z-index:5;display:flex;flex-direction:column;width:min(300px,86%);background:#fff;border-right:1px solid var(--ai-line);box-shadow:12px 0 36px rgba(28,32,51,.16);transform:translateX(-102%);transition:transform .25s cubic-bezier(.3,.8,.3,1)}
.ss-ai-widget.drawer-open .ss-ai-drawer{transform:none}.ss-ai-widget.drawer-open .ss-ai-scrim{opacity:1;pointer-events:auto}
.ss-ai-drawer-head{display:flex;align-items:center;justify-content:space-between;padding:14px 14px 10px;font-size:14px;font-weight:700}
.ss-ai-drawer-head .ss-ai-icon{background:var(--ai-soft);color:var(--ai-muted)}.ss-ai-drawer-head .ss-ai-icon:hover{background:#e4e8fb;color:var(--ai-a)}
.ss-ai-new{display:flex;align-items:center;justify-content:center;gap:8px;margin:0 14px 10px;padding:10px;border:0;border-radius:12px;background:var(--ai-grad);color:#fff;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 8px 18px rgba(102,126,234,.3)}.ss-ai-new svg{width:15px;height:15px}
.ss-ai-sessions{flex:1;overflow-y:auto;padding:4px 10px 10px}
.ss-ai-group{padding:10px 8px 5px;color:var(--ai-muted);font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase}
.ss-ai-session{display:flex;align-items:center;gap:2px;margin-bottom:2px;border-radius:10px}.ss-ai-session:hover{background:var(--ai-soft)}.ss-ai-session.active{background:#eceffd}
.ss-ai-session-pick{flex:1;min-width:0;padding:9px 10px;border:0;background:transparent;color:var(--ai-text);font-size:13px;text-align:left;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ss-ai-session.active .ss-ai-session-pick{font-weight:700;color:#4a5ad6}
.ss-ai-session-del{display:inline-flex;align-items:center;gap:4px;padding:6px 8px;border:0;border-radius:8px;background:transparent;color:var(--ai-muted);font-size:11.5px;font-weight:600;cursor:pointer;opacity:0;transition:opacity .15s ease}
.ss-ai-session:hover .ss-ai-session-del,.ss-ai-session-del.confirm,.ss-ai-session-del:focus-visible{opacity:1}.ss-ai-session-del:hover{color:#dc2626}.ss-ai-session-del.confirm{background:#fee2e2;color:#dc2626}.ss-ai-session-del svg{width:14px;height:14px}
.ss-ai-empty{padding:26px 14px;text-align:center;color:var(--ai-muted);font-size:12.5px}
.ss-ai-drawer-foot{padding:10px 16px 14px;border-top:1px solid var(--ai-line);color:var(--ai-muted);font-size:11px;line-height:1.5}
.ss-ai-kb{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;padding:8px 10px;border-radius:10px;background:var(--ai-soft);color:#4b5373;font-weight:600}
.ss-ai-kb-refresh{flex:none;padding:4px 10px;border:1px solid #c9d0f5;border-radius:8px;background:#fff;color:var(--ai-a);font-size:11px;font-weight:700;cursor:pointer}.ss-ai-kb-refresh:disabled{opacity:.5;cursor:wait}

/* composer */
.ss-ai-composer{padding:10px 14px 12px;border-top:1px solid var(--ai-line);background:#fff}
.ss-ai-field{display:flex;align-items:flex-end;gap:8px;padding:6px 6px 6px 14px;border:1.5px solid var(--ai-line);border-radius:16px;background:#fbfbfe;transition:border-color .15s ease,box-shadow .15s ease,background .15s ease}
.ss-ai-field:focus-within{border-color:#8b9cff;background:#fff;box-shadow:0 0 0 4px rgba(102,126,234,.14)}
.ss-ai-field textarea{display:block;flex:1;min-width:0;height:36px;min-height:36px;max-height:140px;margin:0;padding:8px 0;border:0;outline:0;background:transparent;box-shadow:none;color:var(--ai-text);font-size:14px;line-height:20px;font-family:inherit;resize:none;overflow:hidden;-webkit-appearance:none;appearance:none}
.ss-ai-field textarea::placeholder{color:#9aa1b8}
.ss-ai-send{display:inline-flex;flex:none;align-items:center;justify-content:center;width:38px;height:38px;padding:0;border:0;border-radius:12px;background:var(--ai-grad);color:#fff;cursor:pointer;box-shadow:0 6px 14px rgba(102,126,234,.4);transition:transform .15s ease,opacity .15s ease}
.ss-ai-send:hover:not(:disabled){transform:translateY(-1px) scale(1.05)}.ss-ai-send:disabled{opacity:.4;cursor:not-allowed;box-shadow:none}.ss-ai-send svg{width:17px;height:17px}
.ss-ai-foot{display:flex;justify-content:space-between;gap:8px;margin-top:7px;padding:0 4px;color:#9aa1b8;font-size:10.5px}
.ss-ai-count.limit{color:#dc2626;font-weight:700}

@keyframes ssAiPop{from{opacity:0;transform:translateY(16px) scale(.96)}to{opacity:1;transform:none}}
@keyframes ssAiBlock{from{opacity:0;transform:translateY(8px);filter:blur(3px)}to{opacity:1;transform:none;filter:none}}
@keyframes ssAiSpin{to{transform:rotate(360deg)}}
@keyframes ssAiPulse{0%,100%{box-shadow:0 0 0 3px rgba(74,222,128,.25)}50%{box-shadow:0 0 0 6px rgba(74,222,128,.08)}}
@keyframes ssAiShimmer{from{background-position:220% 0}to{background-position:-220% 0}}
@keyframes ssAiBounce{0%,80%,100%{transform:translateY(0);opacity:.45}40%{transform:translateY(-5px);opacity:1}}

@media(max-width:700px){
  .ss-ai-launcher{right:16px;bottom:16px;width:56px;height:56px}
  .ss-ai-widget,.ss-ai-widget.is-wide{inset:0;right:0;bottom:0;width:100%;max-width:none;height:100%;border:0;border-radius:0}
  .ss-ai-col{max-width:92%}
  .ss-ai-expand{display:none}
  .ss-ai-copy{opacity:1}
}
@media(prefers-reduced-motion:reduce){.ss-ai-launcher *,.ss-ai-widget,.ss-ai-widget *{animation:none!important;transition:none!important}}
</style>

<button class="ss-ai-launcher" id="ssAiLauncher" type="button" aria-label="Open School AI Assistant">
  <span class="ss-ai-launcher-glow"></span>
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l1.9 5.6a3 3 0 0 0 1.9 1.9L21.4 11.4a.7.7 0 0 1 0 1.3l-5.6 1.9a3 3 0 0 0-1.9 1.9L12 22l-1.9-5.5a3 3 0 0 0-1.9-1.9L2.6 12.7a.7.7 0 0 1 0-1.3l5.6-1.9a3 3 0 0 0 1.9-1.9L12 2z"/><path d="M19 2l.7 2a1.2 1.2 0 0 0 .8.8l2 .7-2 .7a1.2 1.2 0 0 0-.8.8L19 9l-.7-2a1.2 1.2 0 0 0-.8-.8l-2-.7 2-.7a1.2 1.2 0 0 0 .8-.8L19 2z" opacity=".75"/></svg>
  <span class="ss-ai-launcher-tip">Ask School AI</span>
</button>

<section class="ss-ai-widget" id="ssAiWidget" role="dialog" aria-label="School AI Assistant" aria-hidden="true">
  <header class="ss-ai-header">
    <div class="ss-ai-brand">
      <span class="ss-ai-orb" aria-hidden="true"></span>
      <div><h3>School AI</h3><p><span class="ss-ai-dot"></span> Online · Powered by Groq</p></div>
    </div>
    <div class="ss-ai-actions">
      <button class="ss-ai-icon ss-ai-open-history" type="button" title="Chat history" aria-label="Chat history"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg></button>
      <button class="ss-ai-icon ss-ai-start" type="button" title="New chat" aria-label="New chat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></button>
      <button class="ss-ai-icon ss-ai-expand" type="button" title="Expand" aria-label="Expand"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></button>
      <button class="ss-ai-icon ss-ai-close" id="ssAiCloseWidget" type="button" title="Close" aria-label="Close School AI Assistant"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    </div>
  </header>

  <div class="ss-ai-body">
    <div class="ss-ai-chat" aria-live="polite"></div>
    <button class="ss-ai-jump" type="button" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg> Latest</button>
    <div class="ss-ai-scrim"></div>
    <aside class="ss-ai-drawer" aria-label="Chat history">
      <div class="ss-ai-drawer-head"><span>Chat history</span><button class="ss-ai-icon ss-ai-drawer-close" type="button" aria-label="Close history"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg></button></div>
      <button class="ss-ai-new" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg> New chat</button>
      <div class="ss-ai-sessions"></div>
      <div class="ss-ai-drawer-foot">
        <div class="ss-ai-kb"><span class="ss-ai-kb-text">Knowledge base: loading…</span><button class="ss-ai-kb-refresh" type="button" title="Re-read the app's menus, pages, code and database">Refresh</button></div>
        Chats are private to you and are deleted automatically after 48 hours. Teach me with “Remember that …”.
      </div>
    </aside>
  </div>

  <form class="ss-ai-composer" autocomplete="off">
    <div class="ss-ai-field">
      <textarea rows="1" maxlength="500" placeholder="Ask anything about your school…" aria-label="Message"></textarea>
      <button class="ss-ai-send" type="submit" aria-label="Send"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>
    </div>
    <div class="ss-ai-foot"><span>AI can make mistakes. Double-check important figures.</span><span class="ss-ai-count">0 / 80</span></div>
  </form>
</section>

<script>
(function(){
  var root=document.getElementById('ssAiWidget');
  if(!root){console.error('[AI Widget] Root element #ssAiWidget was not found.');return;}
  var BASE='<?php echo rtrim(base_url(), '/').'/'; ?>', MAX_WORDS=80, STORE='ss_ai_widget_session_id';
  var launcher=document.getElementById('ssAiLauncher');
  var chat=root.querySelector('.ss-ai-chat'), input=root.querySelector('textarea'), sendBtn=root.querySelector('.ss-ai-send'), counter=root.querySelector('.ss-ai-count'), list=root.querySelector('.ss-ai-sessions'), jump=root.querySelector('.ss-ai-jump'), form=root.querySelector('.ss-ai-composer');
  var current=safeGet(STORE)||'', busy=false, lastQuestion='', booted=false;

  function safeGet(k){try{return localStorage.getItem(k)}catch(e){return null}}
  function safeSet(k,v){try{v===null?localStorage.removeItem(k):localStorage.setItem(k,v)}catch(e){}}
  function url(path){return (window.base_url||BASE)+path}
  function el(tag,cls,text){var n=document.createElement(tag);if(cls)n.className=cls;if(text!==undefined)n.textContent=text;return n}
  var ICON={
    db:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>',
    copy:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>',
    check:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>',
    trash:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>'
  };

  /* ---------- open / close ---------- */
  function openWidget(){
    root.classList.add('open');root.setAttribute('aria-hidden','false');if(launcher)launcher.style.display='none';
    if(!booted){booted=true;if(current){loadHistory(current,true)}else{showWelcome()}loadSessions();loadKnowledge()}
    autosize();
    setTimeout(function(){autosize();input.focus()},220);
  }
  function closeWidget(){root.classList.remove('open','drawer-open');root.setAttribute('aria-hidden','true');if(launcher)launcher.style.display='flex'}
  launcher.addEventListener('click',openWidget);
  root.querySelector('.ss-ai-close').addEventListener('click',closeWidget);
  root.querySelector('.ss-ai-expand').addEventListener('click',function(){root.classList.toggle('is-wide')});
  root.querySelector('.ss-ai-open-history').addEventListener('click',function(){loadSessions();root.classList.add('drawer-open')});
  root.querySelector('.ss-ai-drawer-close').addEventListener('click',function(){root.classList.remove('drawer-open')});
  root.querySelector('.ss-ai-scrim').addEventListener('click',function(){root.classList.remove('drawer-open')});
  root.querySelector('.ss-ai-start').addEventListener('click',startChat);
  root.querySelector('.ss-ai-new').addEventListener('click',function(){startChat();root.classList.remove('drawer-open')});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&root.classList.contains('open')){if(root.classList.contains('drawer-open'))root.classList.remove('drawer-open');else closeWidget()}});

  /* ---------- rich text (DOM only, never innerHTML for model output) ---------- */
  function inline(parent,text){
    var re=/(\*\*[^*]+\*\*|`[^`]+`|PKR\s?-?[\d,]+(?:\.\d+)?)/g,last=0,m,n;
    while((m=re.exec(text))){
      if(m.index>last)parent.appendChild(document.createTextNode(text.slice(last,m.index)));
      var t=m[0];
      if(t.charAt(0)==='*')n=el('strong','',t.slice(2,-2));else if(t.charAt(0)==='`')n=el('code','',t.slice(1,-1));else n=el('span','ss-ai-money',t);
      parent.appendChild(n);last=m.index+t.length;
    }
    if(last<text.length)parent.appendChild(document.createTextNode(text.slice(last)));
  }
  function cells(line){return line.trim().replace(/^\|/,'').replace(/\|$/,'').split('|').map(function(c){return c.trim()})}
  function rich(container,text){
    var lines=String(text||'').replace(/\r/g,'').split('\n'),i=0,list=null,listType='',block=0;
    function push(node){node.style.setProperty('--i',block++);container.appendChild(node)}
    while(i<lines.length){
      var t=lines[i].trim();
      if(!t){list=null;i++;continue}
      if(t.charAt(0)==='|'&&t.indexOf('|',1)>0){
        var rows=[];while(i<lines.length&&lines[i].trim().charAt(0)==='|'){rows.push(lines[i].trim());i++}
        var data=rows.filter(function(r){return !(/^\|?[\s:|-]+\|?$/.test(r)&&r.indexOf('-')>-1)});
        var hasHead=rows.length>1&&/^\|?[\s:|-]*-{2,}[\s:|-]*\|?$/.test(rows[1]);
        var wrap=el('div','ss-ai-table-wrap'),tb=el('table');
        data.forEach(function(r,idx){var tr=el('tr');cells(r).forEach(function(c){var cell=el(hasHead&&idx===0?'th':'td');inline(cell,c);tr.appendChild(cell)});tb.appendChild(tr)});
        wrap.appendChild(tb);list=null;push(wrap);continue;
      }
      var h=t.match(/^#{1,6}\s+(.*)/);
      if(h){list=null;var hd=el('div','ss-ai-h');inline(hd,h[1]);push(hd);i++;continue}
      var b=t.match(/^[-•*]\s+(.*)/),o=t.match(/^\d+[.)]\s+(.*)/);
      if(b||o){
        var type=b?'ul':'ol';
        if(!list||listType!==type){list=el(type);listType=type;push(list)}
        var li=el('li');inline(li,(b||o)[1]);list.appendChild(li);i++;continue;
      }
      list=null;var p=el('p');inline(p,t);push(p);i++;
    }
  }

  /* ---------- message builders ---------- */
  function atBottom(){return chat.scrollHeight-chat.scrollTop-chat.clientHeight<90}
  function toBottom(force){if(force||atBottom())chat.scrollTop=chat.scrollHeight;updateJump()}
  function updateJump(){jump.hidden=atBottom()}
  chat.addEventListener('scroll',updateJump);
  jump.addEventListener('click',function(){chat.scrollTop=chat.scrollHeight});

  function clock(value){var d=value?new Date(String(value).replace(' ','T')):new Date();if(isNaN(d))d=new Date();return d.toLocaleTimeString([], {hour:'numeric',minute:'2-digit'})}
  function sourceLabel(tool){
    return String(tool||'').split(',').map(function(s){s=s.trim();if(!s)return '';
      if(s==='read_only_sql')return 'Custom query';if(s==='get_database_schema')return 'Database lookup';
      if(s==='search_knowledge'||s==='knowledge_base')return 'Knowledge base';if(s==='save_knowledge')return 'Learned something new';if(s==='forget_knowledge')return 'Forgot a fact';
      if(s.indexOf('school_report:')===0){var n=s.slice(14).replace(/_/g,' ');return n.charAt(0).toUpperCase()+n.slice(1)}
      if(s==='school_report')return 'School report';
      var n2=s.replace(/_/g,' ');return n2.charAt(0).toUpperCase()+n2.slice(1)}).filter(Boolean);
  }
  function addUser(text,time){
    var row=el('div','ss-ai-row user'),col=el('div','ss-ai-col');
    col.appendChild(el('div','ss-ai-bubble',text));
    var meta=el('div','ss-ai-meta');meta.appendChild(el('span','ss-ai-time',clock(time)));col.appendChild(meta);
    row.appendChild(col);chat.appendChild(row);toBottom(true);return row;
  }
  function addAi(text,opts){
    opts=opts||{};
    var row=el('div','ss-ai-row ai'),col=el('div','ss-ai-col');
    row.appendChild(el('span','ss-ai-orb sm'));
    var bubble=el('div','ss-ai-bubble'+(opts.animate?' ss-ai-reveal':''));rich(bubble,text);col.appendChild(bubble);
    var meta=el('div','ss-ai-meta');
    sourceLabel(opts.source).forEach(function(label){var s=el('span','ss-ai-src');s.innerHTML=ICON.db;s.appendChild(document.createTextNode(label));meta.appendChild(s)});
    if(opts.offline){var off=el('span','ss-ai-src','Direct report · AI offline');off.title='The AI service is unavailable, so this answer was read straight from the school database.';meta.appendChild(off)}
    meta.appendChild(el('span','ss-ai-time',clock(opts.time)));
    var copy=el('button','ss-ai-copy');copy.type='button';copy.innerHTML=ICON.copy+'<span>Copy</span>';
    copy.addEventListener('click',function(){copyText(text,function(){copy.innerHTML=ICON.check+'<span>Copied</span>';setTimeout(function(){copy.innerHTML=ICON.copy+'<span>Copy</span>'},1600)})});
    meta.appendChild(copy);col.appendChild(meta);row.appendChild(col);chat.appendChild(row);toBottom(true);return row;
  }
  function copyText(text,done){
    function fallback(){var t=document.createElement('textarea');t.value=text;t.style.cssText='position:fixed;opacity:0';document.body.appendChild(t);t.select();try{document.execCommand('copy')}catch(e){}document.body.removeChild(t);done()}
    if(navigator.clipboard&&navigator.clipboard.writeText)navigator.clipboard.writeText(text).then(done,fallback);else fallback();
  }
  var errorCopy={RATE_LIMIT:'You are asking quickly. Please wait a moment before the next question.',SESSION_EXPIRED:'This chat expired, so I started a new one.',NETWORK_ERROR:'I could not reach the server. Check your connection and try again.',AUTH_REQUIRED:'Your login session has ended. Please sign in again.',INVALID_INPUT:'Please type a question first.',SERVER_ERROR:'Something went wrong on our side. Please try again in a moment.',UNKNOWN_ERROR:'Something went wrong. Please try again.'};
  function addError(code,retry,message,retryAfter){
    var row=el('div','ss-ai-row ai error'),col=el('div','ss-ai-col');
    row.appendChild(el('span','ss-ai-orb sm'));
    var text=((code==='AI_UNAVAILABLE'||code==='AI_RATE_LIMIT')&&message)?message:(errorCopy[code]||errorCopy.UNKNOWN_ERROR);
    if(code==='RATE_LIMIT'&&retryAfter){var secs=Math.ceil(retryAfter),wait=secs>=90?Math.ceil(secs/60)+' minutes':secs+' seconds';text='You have reached the question limit for now. Please try again in about '+wait+'. Questions about student counts, fees and similar totals do not count towards the limit.'}
    var bubble=el('div','ss-ai-bubble',text);
    if(retry){var b=el('button','ss-ai-retry','Try again');b.type='button';b.addEventListener('click',function(){row.remove();ask(lastQuestion,{skipEcho:true})});bubble.appendChild(document.createElement('br'));bubble.appendChild(b)}
    col.appendChild(bubble);row.appendChild(col);chat.appendChild(row);toBottom(true);
  }
  function thinking(){
    var row=el('div','ss-ai-row ai ss-ai-think'),col=el('div','ss-ai-col');
    row.appendChild(el('span','ss-ai-orb sm'));
    var bubble=el('div','ss-ai-bubble'),label=el('span','ss-ai-think-text','Thinking…'),dots=el('span','ss-ai-dots');dots.innerHTML='<i></i><i></i><i></i>';
    bubble.appendChild(label);bubble.appendChild(dots);col.appendChild(bubble);row.appendChild(col);chat.appendChild(row);toBottom(true);
    var steps=[[0,'Thinking…'],[1400,'Searching school records…'],[4200,'Crunching the numbers…'],[9000,'Writing your answer…']],timers=steps.map(function(s){return setTimeout(function(){label.textContent=s[1]},s[0])});
    return {stop:function(){timers.forEach(clearTimeout);row.remove()}};
  }
  function showWelcome(){
    chat.innerHTML='';
    var hero=el('div','ss-ai-hero');
    hero.appendChild(el('span','ss-ai-orb lg'));
    hero.appendChild(el('h2','','Hi, how can I help?'));
    hero.appendChild(el('p','','Ask about fees, students, attendance, staff or finances and I will look it up for you.'));
    var chips=el('div','ss-ai-chips');
    ['How much fee was collected today?','Show pending fees for this month','How many students are in each class?','List all staff members','Total income and expenses this month'].forEach(function(q){
      var c=el('button','ss-ai-chip',q);c.type='button';c.addEventListener('click',function(){ask(q)});chips.appendChild(c)});
    hero.appendChild(chips);chat.appendChild(hero);updateJump();
  }

  /* ---------- composer ---------- */
  function words(v){var t=(v||'').trim();return t?t.split(/\s+/).length:0}
  // Grow with the text up to 140px. While the panel is hidden scrollHeight is 0, so never store that.
  function autosize(){
    input.style.height='auto';
    var h=input.scrollHeight;
    if(!h)return;
    input.style.height=Math.max(36,Math.min(h,140))+'px';
    input.style.overflowY=h>140?'auto':'hidden';
  }
  function syncComposer(){
    autosize();
    var n=words(input.value);counter.textContent=n+' / '+MAX_WORDS;counter.classList.toggle('limit',n>=MAX_WORDS);
    sendBtn.disabled=busy||!input.value.trim()||n>MAX_WORDS;
  }
  input.addEventListener('input',syncComposer);
  input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey&&!e.isComposing){e.preventDefault();form.requestSubmit?form.requestSubmit():form.dispatchEvent(new Event('submit',{cancelable:true}))}});
  form.addEventListener('submit',function(e){e.preventDefault();ask(input.value)});
  syncComposer();

  /* ---------- server calls ---------- */
  function post(path,payload){
    return fetch(url(path),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload),credentials:'same-origin'})
      .then(function(r){return r.text().then(function(t){var j;try{j=JSON.parse(t)}catch(e){j={error:true,error_code:(r.status===401||r.status===403)?'AUTH_REQUIRED':'SERVER_ERROR'}}j.http_status=r.status;return j})});
  }
  function ask(q,opts){
    opts=opts||{};q=(q||'').trim();
    if(busy||!q)return;
    if(words(q)>MAX_WORDS){addError('INVALID_INPUT');return}
    lastQuestion=q;
    var hero=chat.querySelector('.ss-ai-hero');if(hero)chat.innerHTML='';
    if(!opts.skipEcho){input.value='';syncComposer();addUser(q)}
    busy=true;syncComposer();var t=thinking();
    post('ai_widget/ask',{session_id:current,message:q}).then(function(j){
      t.stop();
      if(j.error){
        if(j.error_code==='SESSION_EXPIRED'&&!opts.retried){current='';safeSet(STORE,null);busy=false;return ask(q,{skipEcho:true,retried:true})}
        addError(j.error_code,j.error_code==='SERVER_ERROR'||j.error_code==='NETWORK_ERROR',j.message,j.retry_after);return;
      }
      current=j.session_id;safeSet(STORE,current);
      addAi(j.reply,{animate:true,source:j.tool_used,offline:!!j.ai_offline});loadSessions();
    }).catch(function(){t.stop();addError('NETWORK_ERROR',true)}).then(function(){busy=false;syncComposer();input.focus()});
  }

  function startChat(){current='';safeSet(STORE,null);showWelcome();root.classList.remove('drawer-open');loadSessions();input.focus()}
  function loadHistory(id,silent){
    fetch(url('ai_widget/history/'+encodeURIComponent(id)),{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){
      if(j.error){current='';safeSet(STORE,null);showWelcome();if(!silent)addError('SESSION_EXPIRED');return}
      current=id;safeSet(STORE,id);chat.innerHTML='';
      if(!j.messages.length){showWelcome();return}
      j.messages.forEach(function(m){if(m.role==='user')addUser(m.content,m.created_at);else addAi(m.content,{source:m.tool_used,time:m.created_at})});
      toBottom(true);root.classList.remove('drawer-open');loadSessions();
    }).catch(function(){showWelcome();if(!silent)addError('NETWORK_ERROR')});
  }
  /* ---------- knowledge base status ---------- */
  var kbText=root.querySelector('.ss-ai-kb-text'),kbBtn=root.querySelector('.ss-ai-kb-refresh');
  function showKnowledge(k){
    if(!k){kbText.textContent='Knowledge base: unavailable';return}
    var learned=(k.by_source&&((k.by_source.admin||0)+(k.by_source.learned_query||0)))||0;
    kbText.textContent='Knowledge base: '+(k.total||0)+' entries'+(learned?' · '+learned+' learned':'');
    kbText.title=k.built_at?'Last indexed '+k.built_at:'';
  }
  // Opening the widget also refreshes a stale index on the server, so questions never wait for it.
  function loadKnowledge(){fetch(url('ai_widget/knowledge_stats'),{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){showKnowledge(j.error?null:j.knowledge)}).catch(function(){showKnowledge(null)})}
  kbBtn.addEventListener('click',function(){
    kbBtn.disabled=true;kbText.textContent='Re-reading the app…';
    post('ai_widget/rebuild_knowledge',{}).then(function(j){showKnowledge(j.error?null:j.knowledge);if(j.error)kbText.textContent=j.message||'Refresh failed'}).catch(function(){showKnowledge(null)}).then(function(){kbBtn.disabled=false});
  });

  function dayGroup(value){
    var d=new Date(String(value).replace(' ','T'));if(isNaN(d))return 'Earlier';
    var now=new Date(),start=new Date(now.getFullYear(),now.getMonth(),now.getDate()),diff=(start-new Date(d.getFullYear(),d.getMonth(),d.getDate()))/864e5;
    return diff<=0?'Today':(diff===1?'Yesterday':'Earlier');
  }
  function loadSessions(){
    fetch(url('ai_widget/sessions'),{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){
      if(j.error)return;list.innerHTML='';
      if(!j.sessions.length){list.appendChild(el('div','ss-ai-empty','No chats yet. Ask your first question!'));return}
      var group='';
      j.sessions.forEach(function(s){
        var g=dayGroup(s.updated_at);if(g!==group){group=g;list.appendChild(el('div','ss-ai-group',g))}
        var row=el('div','ss-ai-session'+(s.session_id===current?' active':'')),pick=el('button','ss-ai-session-pick',s.title||'New chat');pick.type='button';pick.title=s.title||'';
        pick.addEventListener('click',function(){loadHistory(s.session_id)});
        var del=el('button','ss-ai-session-del');del.type='button';del.setAttribute('aria-label','Delete chat');del.innerHTML=ICON.trash;var armed=false,timer;
        del.addEventListener('click',function(e){
          e.stopPropagation();
          if(!armed){armed=true;del.classList.add('confirm');del.innerHTML=ICON.trash+'<span>Delete?</span>';timer=setTimeout(function(){armed=false;del.classList.remove('confirm');del.innerHTML=ICON.trash},2600);return}
          clearTimeout(timer);
          post('ai_widget/delete_session/'+encodeURIComponent(s.session_id),{}).then(function(){if(s.session_id===current)startChat();else loadSessions()});
        });
        row.appendChild(pick);row.appendChild(del);list.appendChild(row);
      });
    }).catch(function(){});
  }
})();
</script>
