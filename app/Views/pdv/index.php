<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV — Conectados</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    
<style>
    .point-success-modal {
        position: fixed;
        inset: 0;
        z-index: 2600;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .66);
        backdrop-filter: blur(5px);
    }
    .point-success-modal.active {
        display: flex;
    }
    .point-success-dialog {
        width: min(440px, 100%);
        padding: 2.5rem 1.5rem;
        border-radius: 24px;
        border: 0;
        background: #ffffff;
        color: #0f172a;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(16, 185, 129, 0.1);
        animation: pointSuccessPop .4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    .point-success-dialog::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 6px;
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .point-success-logo {
        height: 48px;
        margin-bottom: 0.5rem;
        object-fit: contain;
    }
    .point-success-slogan {
        font-size: 0.85rem;
        color: #64748b;
        font-style: italic;
        margin-bottom: 2rem;
        font-weight: 500;
    }
    .point-success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        color: #ffffff;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
    }
    .point-success-icon i {
        width: 40px;
        height: 40px;
    }
    .point-success-dialog h2 {
        margin: 0 0 .5rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        color: #064e3b;
    }
    .point-success-dialog p {
        margin: 0 0 1.5rem;
        color: #475569;
        line-height: 1.5;
        font-size: 1rem;
    }
    .point-success-dialog .btn {
        width: 100%;
        min-height: 50px;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background: #10b981;
        color: #fff;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
        transition: all 0.2s;
    }
    .point-success-dialog .btn:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px -1px rgba(16, 185, 129, 0.3);
    }
    @keyframes pointSuccessPop {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --accent: #2d2dff;
            --primary: #2d2dff;
            --primary-dark: #1f1fd6;
            --success: #10b981;
            --danger: #ef4444;
            --border: #cbd5e1;
            --text-muted: #334155;
            --bg-pdv: #e2e8f0;
            --bg-card: #ffffff;
        }
        body{font-family:'Inter',sans-serif;background:var(--bg-pdv);color:#111;}
        .pdv-layout{display:grid;grid-template-columns:1fr 400px;height:100vh;overflow:hidden;}
        .pdv-left{display:flex;flex-direction:column;overflow:hidden;}
        .pdv-header{padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.2);background:linear-gradient(90deg,#3333ff 0%,var(--primary) 50%,#2323e9 100%);color:#fff;box-shadow:0 18px 36px -34px rgba(17,24,39,.74);}
        .pdv-header h1{font-family:'Outfit',sans-serif;font-size:1.1rem;font-weight:700;}
        .pdv-header a{color:rgba(255,255,255,.9) !important;}
        .pdv-header p{color:rgba(255,255,255,.82) !important;}
        .pdv-header [style*="border-left"]{border-left-color:rgba(255,255,255,.28) !important;}
        .pdv-header-total{background:rgba(255,255,255,.13) !important;border-color:rgba(255,255,255,.28) !important;color:#fff !important;border-radius:999px !important;box-shadow:inset 0 1px 0 rgba(255,255,255,.18);}
        .pdv-header-total span{color:#fff !important;}
        .pdv-produtos{flex:1;overflow-y:auto;padding:1.25rem;background:var(--bg-pdv);}
        .produto-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:1rem;}
        .produto-card{background:var(--bg-card);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:10px;padding:.85rem .85rem 1rem;cursor:pointer;transition:.2s;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px;min-height:280px;box-shadow:0 10px 15px -8px rgba(15,23,42,.08);}
        .produto-card:hover{border-color:#93c5fd;border-left-color:var(--primary);transform:translateY(-2px);box-shadow:0 18px 28px -24px rgba(15,23,42,.55);}
        .produto-thumb{width:100%;height:165px;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;margin:0 auto .15rem;overflow:hidden;border:1px solid rgba(203,213,225,.8);padding:0;}
        .produto-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
        .produto-thumb i{width:30px;height:30px;color:var(--primary);}
        .produto-card h4{font-size:.85rem;font-weight:600;color:inherit;line-height:1.25;min-height:2.1rem;display:flex;align-items:center;justify-content:center;}
        .produto-card .preco{font-size:1.1rem;font-weight:800;color:var(--primary);}
        .pdv-right{background:linear-gradient(180deg,#ffffff 0%,#f8fbff 72%,#eef4ff 100%);border-left:1px solid rgba(0,52,154,.16);display:flex;flex-direction:column;overflow:hidden;box-shadow:-18px 0 44px -38px rgba(0,35,104,.75);}
        .pdv-right-header{padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.18);background:linear-gradient(135deg,#3333ff 0%,#2d2dff 56%,#1f1fd6 100%);color:#fff;display:flex;align-items:center;justify-content:space-between;gap:1rem;box-shadow:0 16px 34px -30px rgba(0,20,70,.7);}
        .pdv-right-title{display:flex;align-items:center;gap:.7rem;min-width:0;}
        .pdv-right-title-icon{width:40px;height:40px;border-radius:14px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.18);}
        .pdv-right-header h2{color:#fff;font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:800;line-height:1.05;margin:0;}
        .pdv-right-subtitle{display:block;margin-top:4px;color:rgba(255,255,255,.72);font-size:.7rem;font-weight:700;letter-spacing:.02em;text-transform:uppercase;}
        .sale-clock{display:flex;align-items:center;gap:.55rem;padding:.2rem 0;border-radius:0;background:transparent;border:0;box-shadow:none;white-space:nowrap;}
        .sale-clock i{width:16px;height:16px;opacity:.9;}
        .sale-clock-time{font-family:'Outfit',sans-serif;font-size:1.02rem;font-weight:900;letter-spacing:.02em;line-height:1;}
        .sale-clock-date{display:block;margin-top:3px;font-size:.58rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.7);}
        .pdv-itens{flex:1;overflow-y:auto;padding:1.1rem 1.15rem;background:radial-gradient(circle at 50% 12%,rgba(0,52,154,.05),transparent 28%);}
        .item-row{display:grid;grid-template-columns:132px minmax(0,1fr);grid-template-areas:"thumb info" "thumb actions";align-items:center;padding:1rem 0;border-bottom:1px solid var(--border);gap:.8rem 1rem;}
        .item-row-main{display:contents;}
        .item-thumb{grid-area:thumb;width:132px;height:118px;border-radius:14px;background:#f8fafc;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;padding:0;}
        .item-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
        .item-thumb i{width:30px;height:30px;color:var(--primary);}
        .item-row-info{grid-area:info;min-width:0;align-self:end;}
        .item-row h5{font-size:.98rem;font-weight:800;line-height:1.22;margin-bottom:8px;}
        .item-unit{display:flex;align-items:center;gap:.45rem;color:#111827;font-size:.88rem;line-height:1.4;}
        .qty-control{display:inline-grid;grid-template-columns:30px 44px 30px;align-items:center;height:36px;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:#f8fafc;}
        .qty-control button{height:100%;border:0;background:#fff;color:var(--primary);font-weight:900;cursor:pointer;font-size:1rem;}
        .qty-control input{width:44px;height:100%;border:0 !important;border-left:1px solid var(--border) !important;border-right:1px solid var(--border) !important;border-radius:0 !important;background:#f8fafc !important;text-align:center;font-weight:800;color:#111827;padding:0 !important;}
        .item-row-actions{grid-area:actions;display:flex;align-items:center;justify-content:space-between;gap:8px;align-self:start;}
        .item-row-total{font-size:1.05rem;font-weight:900;white-space:nowrap;color:#111827;}
        .remove-item-btn{width:36px;height:36px;border-radius:10px;border:1px solid rgba(239,68,68,.18);background:#fff5f5;color:var(--danger);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:.2s;}
        .remove-item-btn:hover{background:var(--danger);color:#fff;border-color:var(--danger);}
        .btn{--btn-accent:#64748b;--btn-bg:linear-gradient(180deg,#fff 0%,#f2f6fb 48%,#dce6f2 100%);--btn-color:#111827;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;border-radius:999px;font-weight:900;cursor:pointer;border:1px solid rgba(148,163,184,.55);font-family:inherit;font-size:.85rem;line-height:1;transition:transform .18s ease,box-shadow .18s ease,filter .18s ease;text-decoration:none;color:var(--btn-color)!important;background:var(--btn-bg);box-shadow:inset 0 1px 0 rgba(255,255,255,.95),inset 0 -1px 0 rgba(15,23,42,.12),0 2px 0 var(--btn-accent),0 12px 22px -18px rgba(15,23,42,.75);text-shadow:0 1px 0 rgba(255,255,255,.72);}
        .btn:hover{transform:translateY(-1px);filter:saturate(1.04);box-shadow:inset 0 1px 0 rgba(255,255,255,.96),inset 0 -1px 0 rgba(15,23,42,.12),0 3px 0 var(--btn-accent),0 16px 28px -20px rgba(15,23,42,.8);}
        .btn:active{transform:translateY(1px);box-shadow:inset 0 2px 5px rgba(15,23,42,.18),0 1px 0 var(--btn-accent),0 8px 16px -18px rgba(15,23,42,.75);}
        .btn-primary{--btn-accent:#1d4ed8;--btn-bg:linear-gradient(180deg,#4f63ff 0%,#2d2dff 52%,#1717c8 100%);--btn-color:#fff;border-color:rgba(255,255,255,.2);text-shadow:0 1px 1px rgba(15,23,42,.38);}
        .btn-success{--btn-accent:#059669;--btn-bg:linear-gradient(180deg,#34d399 0%,#10b981 52%,#047857 100%);--btn-color:#fff;border-color:rgba(255,255,255,.2);text-shadow:0 1px 1px rgba(15,23,42,.32);}
        .form-control{width:100%;padding:11px 15px;border-radius:10px;border:1px solid #cbd8ea;font-family:inherit;font-size:.9rem;background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.96)),var(--bg-card);color:inherit;box-shadow:inset 0 1px 0 rgba(255,255,255,.9),0 10px 22px -20px rgba(15,23,42,.65);}
        .form-control:focus{outline:none;border-color:#60a5fa;box-shadow:0 0 0 4px rgba(37,99,235,.11),inset 0 1px 0 rgba(255,255,255,.9);}
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px var(--bg-card) inset !important; -webkit-text-fill-color: inherit !important; }
        .pdv-footer{padding:1rem 1.15rem 1.15rem;border-top:1px solid rgba(255,255,255,.16);background:linear-gradient(135deg,#3333ff 0%,#2d2dff 58%,#1f1fd6 100%);color:#fff;box-shadow:0 -18px 42px -34px rgba(0,20,70,.9);}
        .checkout-summary{display:none;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:14px;padding:.8rem .95rem;box-shadow:inset 0 1px 0 rgba(255,255,255,.12);}
        .checkout-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.2rem 0;color:rgba(255,255,255,.88);font-size:.86rem;}
        .checkout-row strong{color:#fff;font-size:.98rem;}
        .checkout-row.card-fee{display:none;color:rgba(255,255,255,.78);}
        .checkout-row.card-fee strong{font-size:.9rem;color:#ffe9a8;}
        .discount-input{width:104px !important;height:34px;padding:6px 10px !important;text-align:right;background:#fff !important;color:#111827 !important;border-color:rgba(255,255,255,.45) !important;font-size:.92rem !important;}
        .checkout-total{margin-top:.45rem;padding-top:.55rem;border-top:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:space-between;gap:.75rem;}
        .checkout-total-label{display:block;font-size:.64rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.7);}
        .total-value{font-size:1.72rem;font-weight:900;font-family:'Outfit',sans-serif;color:#fff;line-height:1;}
        .payment-block{display:none;margin:.55rem 0 .7rem;}
        .payment-block label{font-size:.7rem;font-weight:700;color:rgba(255,255,255,.82);margin-bottom:.3rem;display:block;}
        .payment-block .form-control{height:40px;padding:8px 12px;background:#fff;color:#111827;border-color:rgba(255,255,255,.35);}
        .checkout-actions{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:.85rem;}
        .checkout-actions .btn{width:100%;min-height:56px;border-radius:999px;font-size:.92rem;font-weight:900;}
        .checkout-actions .btn i{width:20px;height:20px;}
        .checkout-actions .btn-success{--btn-accent:#059669;--btn-bg:linear-gradient(180deg,#34d399 0%,#10b981 52%,#047857 100%);color:#fff !important;border:1px solid rgba(255,255,255,.18);}
        .checkout-actions .btn-success:disabled{opacity:.62;cursor:not-allowed;filter:saturate(.75);box-shadow:none;}
        .checkout-actions .btn-clear{--btn-accent:#dc2626;--btn-bg:linear-gradient(180deg,#fff 0%,#fff5f5 48%,#fee2e2 100%);--btn-color:var(--danger);border:1px solid rgba(255,255,255,.45) !important;}
        .pdv-dialog-overlay{position:fixed;inset:0;background:rgba(15,23,42,.58);display:none;align-items:center;justify-content:center;z-index:3000;padding:1rem;backdrop-filter:blur(3px);}
        .pdv-dialog-overlay.active{display:flex;}
        .pdv-dialog{width:min(430px,100%);background:var(--bg-card);color:#111827;border:1px solid var(--border);border-radius:16px;box-shadow:0 28px 80px -36px rgba(0,0,0,.7);overflow:hidden;}
        .pdv-dialog-head{display:flex;gap:.9rem;align-items:flex-start;padding:1.25rem 1.25rem .75rem;}
        .pdv-dialog-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(0,52,154,.08);color:var(--primary);flex:0 0 auto;}
        .pdv-dialog-icon.danger{background:#fff1f2;color:var(--danger);}
        .pdv-dialog-icon.success{background:#dcfce7;color:#047857;}
        .pdv-dialog h3{font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:800;margin:0 0 .25rem;color:inherit;}
        .pdv-dialog p{font-size:.9rem;line-height:1.45;color:var(--text-muted);margin:0;}
        .pdv-dialog-actions{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;padding:.35rem 1.25rem 1.25rem;}
        .pdv-dialog-actions.single{grid-template-columns:1fr;}
        .pdv-dialog-actions .btn{width:100%;min-height:46px;border-radius:999px;font-size:.9rem;}
        .pdv-dialog-cancel{--btn-accent:#64748b;--btn-bg:linear-gradient(180deg,#fff 0%,#f2f6fb 48%,#dce6f2 100%);--btn-color:#0f172a;}
        .pdv-dialog-confirm{--btn-accent:#1d4ed8;--btn-bg:linear-gradient(180deg,#4f63ff 0%,#2d2dff 52%,#1717c8 100%);--btn-color:#fff;}
        .pdv-dialog-success{--btn-accent:#059669;--btn-bg:linear-gradient(180deg,#34d399 0%,#10b981 52%,#047857 100%);--btn-color:#fff;}
        .pdv-dialog-danger{--btn-accent:#dc2626;--btn-bg:linear-gradient(180deg,#fb7185 0%,#ef4444 52%,#b91c1c 100%);--btn-color:#fff;}
        .empty-cart{text-align:center;min-height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4.5rem 1rem;color:#64748b;}
        .empty-cart-icon{width:74px;height:74px;border-radius:24px;background:linear-gradient(180deg,#f8fbff,#eef4ff);border:1px solid rgba(0,52,154,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 18px 36px -30px rgba(0,52,154,.55);}
        .empty-cart-icon i{width:34px;height:34px;color:#94a3b8;}
        .empty-cart h3{font-family:'Outfit',sans-serif;font-size:1.05rem;color:#334155;margin:0 0 .35rem;font-weight:800;}
        .empty-cart p{font-size:.92rem;line-height:1.45;max-width:260px;margin:0 auto;color:#8a94a6;}
        input[type=number]{padding:8px;border:1px solid #cbd8ea;border-radius:10px;background:#fff;color:inherit;}
        .pdv-sale-form{padding:1rem 1.25rem;background:var(--bg-pdv);border-bottom:1px solid var(--border);display:flex;gap:.75rem;flex-wrap:wrap;}
        .pdv-sale-form .btn-primary{min-width:170px;}
        .cash-panel{padding:.85rem 1.25rem;background:#f8fbff;border-bottom:1px solid var(--border);display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.85rem;align-items:center;}
        .cash-status{display:flex;align-items:center;gap:.75rem;min-width:0;}
        .cash-status-icon{width:40px;height:40px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:var(--primary);}
        .cash-status strong{display:block;font-size:.92rem;}
        .cash-status small{display:block;color:var(--text-muted);font-weight:700;font-size:.72rem;margin-top:2px;}
        .cash-status.open .cash-status-icon{background:#dcfce7;color:#047857;}
        .cash-status.closed .cash-status-icon{background:#fee2e2;color:#b91c1c;}
        .cash-actions{display:flex;gap:.55rem;align-items:center;flex-wrap:wrap;justify-content:flex-end;}
        .cash-mini-form{display:flex;gap:.4rem;align-items:center;}
        .cash-mini-form input{width:118px;}
        .search-bar{padding:0 1.25rem 1rem;border-bottom:1px solid var(--border);background:var(--bg-pdv);}
        @media(max-width:900px){
            body{overflow:auto;}
            .pdv-layout{display:flex;flex-direction:column;height:auto;min-height:100vh;overflow:visible;}
            .pdv-left,.pdv-right{overflow:visible;}
            .pdv-right{order:-1;}
            .pdv-left{order:1;}
            .pdv-header{padding:1rem;align-items:flex-start;gap:1rem;flex-direction:column;}
            .cash-panel{grid-template-columns:1fr;padding:.8rem 1rem;}
            .cash-actions{justify-content:flex-start;}
            .cash-mini-form{width:100%;flex-wrap:wrap;}
            .cash-mini-form input{width:100%;}
            .pdv-header>div{width:100%;flex-wrap:wrap;}
            .pdv-produtos{padding:1rem;}
            .produto-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;}
            .produto-card{padding:.75rem;min-height:245px;}
            .produto-thumb{height:140px;}
            .item-row{grid-template-columns:118px minmax(0,1fr);}
            .item-thumb{width:118px;height:108px;}
            .pdv-right{border-left:0;border-top:1px solid var(--border);min-height:420px;}
            .pdv-right-header{padding:1rem;align-items:flex-start;}
            .sale-clock{padding:.2rem 0;}
            .pdv-footer{padding:.75rem 1rem .9rem;}
            .pdv-itens{max-height:none;min-height:180px;}
            .checkout-summary{padding:.75rem .85rem;}
            .total-value{font-size:1.55rem;}
            .search-bar{padding:0 1rem 1rem;}
            [style*="min-width:200px"],[style*="min-width: 200px"],[style*="width:120px"],[style*="width: 120px"]{width:100%!important;min-width:0!important;}
            .btn{width:100%;}
        }
        @media(max-width:520px){
            .produto-grid{grid-template-columns:1fr 1fr;}
            .produto-thumb{height:118px;}
            .produto-card h4{font-size:.78rem;}
            .produto-card .preco{font-size:1rem;}
            .total-value{font-size:1.45rem;}
            .checkout-total{align-items:flex-start;flex-direction:column;gap:.35rem;}
            .discount-input{width:96px !important;}
            .item-row{grid-template-columns:96px minmax(0,1fr);align-items:flex-start;gap:.55rem .75rem;}
            .item-thumb{width:96px;height:88px;border-radius:12px;}
            .item-row h5{font-size:.85rem;}
            .item-row-actions{flex-direction:column;align-items:flex-end;justify-content:flex-start;padding-top:0;}
            .item-row-total{font-size:.9rem;}
            .qty-control{grid-template-columns:26px 38px 26px;height:32px;}
            .qty-control input{width:38px;}
            .checkout-actions{grid-template-columns:1fr;}
            .pdv-right-header{flex-direction:column;}
            .sale-clock{width:100%;justify-content:center;}
        }
    </style>
</head>
<body>
<div class="pdv-layout" id="pdv-layout">
    <!-- Esquerda: produtos -->
    <div class="pdv-left" id="pdv-left">
        <div class="pdv-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="<?= route_url('dashboard') ?>" style="color:var(--text-muted);text-decoration:none;"><i data-lucide="arrow-left" style="width:18px;"></i></a>
                <div style="border-left:1px solid var(--border);padding-left:12px;margin-left:8px;">
                    <h1>PDV — Conectados</h1>
                    <p style="font-size:.7rem;color:var(--text-muted);"><?= date('d/m/Y H:i') ?> · <?= count($produtos) ?> produtos</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="pdv-header-total" style="background:var(--bg-pdv);border:1px solid var(--border);padding:6px 12px;border-radius:8px;font-size:.8rem;font-weight:600;">
                    Hoje: <span style="color:var(--primary);">R$ <?= number_format($totalHoje, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <?php
            $caixaAberto = !empty($caixa) && ($caixa['status'] ?? '') === 'aberto';
            $scheduleText = !empty($horarioCaixa['dia_util'])
                ? 'Automatico hoje: ' . ($horarioCaixa['abre'] ?? '--:--') . ' as ' . ($horarioCaixa['fecha'] ?? '--:--')
                : 'Hoje nao ha abertura automatica';
            $cashSummary = 'Vendas no caixa: ' . (int) ($caixaResumo['qtd'] ?? 0) . ' | R$ ' . number_format((float) ($caixaResumo['total'] ?? 0), 2, ',', '.');
        ?>
        <div class="cash-panel">
            <div class="cash-status <?= $caixaAberto ? 'open' : 'closed' ?>">
                <span class="cash-status-icon"><i data-lucide="<?= $caixaAberto ? 'unlock' : 'lock' ?>"></i></span>
                <div>
                    <strong>Caixa <?= $caixaAberto ? 'aberto' : 'fechado' ?><?= !empty($caixa['abertura_tipo']) ? ' (' . htmlspecialchars($caixa['abertura_tipo']) . ')' : '' ?></strong>
                    <small><?= htmlspecialchars($scheduleText) ?> · <?= htmlspecialchars($cashSummary) ?></small>
                </div>
            </div>
            <?php if (!empty($isAdmin)): ?>
            <div class="cash-actions">
                <?php if (!$caixaAberto): ?>
                <form class="cash-mini-form" action="<?= e(route_url('pdv/abrirCaixa')) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="text" name="valor_inicial" class="form-control" placeholder="Valor inicial">
                    <button type="submit" class="btn btn-success"><i data-lucide="unlock"></i> Abrir</button>
                </form>
                <?php else: ?>
                <form class="cash-mini-form" action="<?= e(route_url('pdv/fecharCaixa')) ?>" method="POST" data-confirm="Fechar o caixa manualmente? O sistema nao reabrira sozinho hoje.">
                    <?= csrf_field() ?>
                    <input type="text" name="valor_informado" class="form-control" placeholder="Valor contado">
                    <button type="submit" class="btn btn-primary"><i data-lucide="lock"></i> Fechar</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Busca cliente -->
        <div class="pdv-sale-form">
            <select id="sel-cliente" class="form-control" style="flex:1;min-width:200px;">
                <option value="">Cliente — Consumidor Final</option>
                <?php foreach($clientes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['nome'] ?? '') ?></option><?php endforeach; ?>
            </select>
            <input type="text" id="custom-descricao" class="form-control" placeholder="Ou digitar descrição livre..." style="flex:1;min-width:200px;">
            <input type="number" id="custom-preco" class="form-control" placeholder="Preço R$" style="width:120px;" step="0.01" min="0">
            <button onclick="adicionarPersonalizado()" class="btn btn-primary"><i data-lucide="plus"></i> Adicionar</button>
        </div>

        <!-- Busca produto -->
        <div class="search-bar">
            <i data-lucide="search"></i>
            <input type="text" class="form-control" id="busca-produto" placeholder="Buscar produto por nome..." oninput="filtrarProdutos(this.value)">
        </div>

        <!-- Grid Produtos -->
        <div class="pdv-produtos">
            <div class="produto-grid" id="grade-produtos">
                <?php foreach($produtos as $p): ?>
                <?php $imagemProduto = $p['imagem_url'] ?? ''; ?>
                <div class="produto-card" onclick="adicionarItem(<?= (int) $p['id'] ?>, <?= e(json_attr($p['nome'])) ?>, <?= (float) $p['preco_venda'] ?>, <?= e(json_attr($imagemProduto)) ?>)"
                     data-nome="<?= htmlspecialchars(strtolower($p['nome'])) ?>"
                     data-id="<?= (int) $p['id'] ?>"
                     <?= $p['quantidade']<=0 ? 'style="opacity:.5;pointer-events:none;"' : '' ?>>
                    <div class="produto-thumb">
                        <?php if(!empty($imagemProduto)): ?>
                        <img src="<?= htmlspecialchars($imagemProduto) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" loading="lazy" onerror="this.closest('.produto-thumb').innerHTML='<i data-lucide=&quot;package&quot;></i>'; lucide.createIcons();">
                        <?php else: ?>
                        <i data-lucide="package"></i>
                        <?php endif; ?>
                    </div>
                    <h4><?= htmlspecialchars($p['nome']) ?></h4>
                    <div class="preco">R$ <?= number_format($p['preco_venda'],2,',','.') ?></div>
                    <div class="qtd-badge"><?= (int) $p['quantidade'] <= (int) $p['estoque_minimo'] ? '<span class="badge-low">Baixo: ' . (int) $p['quantidade'] . '</span>' : 'Estoque: ' . (int) $p['quantidade'] ?></div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($produtos)): ?>
                <div style="grid-column:span 5;text-align:center;padding:3rem;color:var(--text-muted);">
                    <i data-lucide="package-x" style="width:48px;display:block;margin:0 auto 1rem;"></i>
                    Nenhum produto no estoque. <a href="<?= route_url('produtos/create') ?>" style="color:var(--secondary);">Cadastrar</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Direita: Carrinho -->
    <div class="pdv-right" id="pdv-right">
        <div class="pdv-right-header">
            <div class="pdv-right-title">
                <span class="pdv-right-title-icon"><i data-lucide="shopping-cart"></i></span>
                <div>
                    <h2>Venda Atual</h2>
                    <span class="pdv-right-subtitle" id="pdv-items-summary">Carrinho vazio</span>
                </div>
            </div>
            <div class="sale-clock" aria-label="Horario atual">
                <i data-lucide="clock"></i>
                <div>
                    <span class="sale-clock-time" id="pdv-clock">--:--:--</span>
                    <span class="sale-clock-date" id="pdv-clock-date"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </div>
        <div class="pdv-itens" id="lista-itens">
            <?php if(!empty($_GET['success']) && !empty($_GET['venda_id'])): ?>
            <div style="background:#dcfce7;border:1px solid #86efac;padding:1.5rem;border-radius:12px;text-align:center;margin-bottom:1rem;">
                <i data-lucide="check-circle" style="width:40px;color:var(--success);display:block;margin:0 auto .5rem;"></i>
                <h4 style="color:#166534;">Venda Finalizada!</h4>
                <p style="font-size:.8rem;color:#166534;margin-bottom:1rem;">O estoque foi baixado e a receita registrada.</p>
                <button onclick="window.open('<?= route_url('pdv/imprimir', ['id' => $_GET['venda_id']]) ?>', '_blank')" class="btn btn-success" style="width:100%;">
                    <i data-lucide="printer"></i> Imprimir Recibo
                </button>
            </div>
            <?php endif; ?>

            <?php if(!empty($_GET['point_sent']) && !empty($_GET['venda_id'])): ?>
            <div style="background:#dbeafe;border:1px solid #93c5fd;padding:1.5rem;border-radius:12px;text-align:center;margin-bottom:1rem;">
                <i data-lucide="credit-card" style="width:40px;color:#1d4ed8;display:block;margin:0 auto .5rem;"></i>
                <h4 style="color:#1e3a8a;">Enviado para Smart Point</h4>
                <p style="font-size:.8rem;color:#1e3a8a;margin-bottom:1rem;">Confira a cobranca na maquininha. O financeiro sera lancado quando o Mercado Pago confirmar.</p>
            </div>
            <?php endif; ?>
            
            <div class="empty-cart" id="empty-msg">
                <div class="empty-cart-icon"><i data-lucide="shopping-cart"></i></div>
                <h3>Nenhum item adicionado</h3>
                <p>Clique nos produtos a esquerda para montar a venda.</p>
            </div>
        </div>
        <div class="pdv-footer">
            <div id="totais-block" class="checkout-summary">
                <div class="checkout-row">
                    <span>Subtotal</span>
                    <strong id="subtotal-val">R$ 0,00</strong>
                </div>
                <div class="checkout-row">
                    <span>Desconto</span>
                    <input type="number" id="desconto-input" class="form-control discount-input" min="0" step="0.01" placeholder="0,00" oninput="calcularTotal()">
                </div>
                <div class="checkout-row card-fee" id="taxa-cartao-row">
                    <span id="taxa-cartao-label">Taxa maquininha</span>
                    <strong id="taxa-cartao-val">R$ 0,00</strong>
                </div>
                <div class="checkout-row card-fee" id="liquido-cartao-row">
                    <span>Liquido estimado</span>
                    <strong id="liquido-cartao-val">R$ 0,00</strong>
                </div>
                <div class="checkout-total">
                    <span class="checkout-total-label">Total</span>
                    <span class="total-value" id="total-val">R$ 0,00</span>
                </div>
            </div>

            <div id="forma-pagamento-block" class="payment-block">
                <label>Forma de Pagamento</label>
                <select id="forma-pagamento" class="form-control" onchange="calcularTotal()">
                    <option>Pix</option>
                    <option>Dinheiro</option>
                    <option>Cartao de Debito</option>
                    <option>QR Mercado Pago</option>
                    <option>Saldo Mercado Pago</option>
                    <option>Cartao de Credito na hora</option>
                    <option>Cartao de Credito 14 dias</option>
                    <option>Cartao de Credito 30 dias</option>
                    <?php for ($parcelas = 2; $parcelas <= 12; $parcelas++): ?>
                    <option>Cartao de Credito <?= $parcelas ?>x</option>
                    <?php endfor; ?>
                </select>
            </div>

            <form id="form-finalizar" action="<?= e(route_url('pdv/finalizarVenda')) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="cliente_id" id="hidden-cliente">
                <input type="hidden" name="os_id" value="">
                <input type="hidden" name="desconto" id="hidden-desconto">
                <input type="hidden" name="forma_pagamento" id="hidden-forma">
                <input type="hidden" name="itens_json" id="hidden-itens">
                <input type="hidden" name="observacoes" value="">
                <input type="hidden" name="enviar_point" id="hidden-enviar-point" value="0">
            </form>

            <div class="checkout-actions">
                <button onclick="finalizarVenda()" class="btn btn-success" id="btn-finalizar-venda" disabled>
                    <i data-lucide="check-circle"></i> <span id="btn-finalizar-label">Finalizar Venda</span>
                </button>
                <button onclick="limparCarrinho()" class="btn btn-clear">
                    <i data-lucide="trash-2"></i> Limpar Carrinho
                </button>
            </div>
        </div>
    </div>
</div>


<div id="point-success-modal" class="point-success-modal" aria-hidden="true" onclick="if(event.target.id==='point-success-modal'){window.location.href=pdvPointSuccessUrl;}">
    <div class="point-success-dialog" role="dialog" aria-modal="true" aria-labelledby="point-success-title" aria-describedby="point-success-message" onclick="event.stopPropagation()">
        <img src="<?= app_url('assets/img/logo.png') ?>" alt="Logo" class="point-success-logo">
        <div class="point-success-slogan">Você conectado sempre</div>
        
        <div class="point-success-icon">
            <i data-lucide="check"></i>
        </div>
        <h2 id="point-success-title">Pagamento aprovado</h2>
        <p id="point-success-message">O pagamento na Smart Point foi efetuado com sucesso.</p>
        <button type="button" class="btn" onclick="window.location.href=pdvPointSuccessUrl">
            <i data-lucide="eye"></i> Ver venda
        </button>
    </div>
</div>

<div id="pdv-dialog-overlay" class="pdv-dialog-overlay" aria-hidden="true" onclick="fecharDialogoPdv(event)">
    <div class="pdv-dialog" role="dialog" aria-modal="true" aria-labelledby="pdv-dialog-title" aria-describedby="pdv-dialog-message" onclick="event.stopPropagation()">
        <div class="pdv-dialog-head">
            <div class="pdv-dialog-icon" id="pdv-dialog-icon-wrap">
                <i id="pdv-dialog-icon" data-lucide="info"></i>
            </div>
            <div>
                <h3 id="pdv-dialog-title">Aviso</h3>
                <p id="pdv-dialog-message">Verifique as informações da venda.</p>
            </div>
        </div>
        <div class="pdv-dialog-actions" id="pdv-dialog-actions">
            <button type="button" class="btn pdv-dialog-cancel" id="pdv-dialog-cancel" onclick="fecharDialogoPdv()">Cancelar</button>
            <button type="button" class="btn pdv-dialog-confirm" id="pdv-dialog-confirm">Confirmar</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

let carrinho = [];
let pdvDialogConfirmCallback = null;
const taxasCartao = {
    debito: <?= json_encode((float) ($taxaDebito ?? 0)) ?>,
    credito: <?= json_encode((float) ($taxaCredito ?? 0)) ?>,
    point: <?= json_encode($pointTaxas ?? [], JSON_UNESCAPED_UNICODE) ?>
};
const caixaAberto = <?= json_encode($caixaAberto) ?>;
<?php $pointPdvVendaId = (int) ($_GET['venda_id'] ?? 0); ?>
const pdvPointPending = <?= json_encode(!empty($_GET['point_sent']) && $pointPdvVendaId > 0) ?>;
const pdvPointStatusUrl = <?= json_attr(route_url('pdv/pointStatus', ['venda_id' => $pointPdvVendaId])) ?>;
const pdvPointSuccessUrl = <?= json_attr(route_url('pdv', ['success' => 1, 'venda_id' => $pointPdvVendaId])) ?>;
const pdvPointApprovedStatuses = new Set(['paid', 'approved', 'finished', 'processed']);

function atualizarRelogioPdv() {
    const now = new Date();
    const clock = document.getElementById('pdv-clock');
    const date = document.getElementById('pdv-clock-date');

    if (clock) {
        clock.textContent = now.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    if (date) {
        date.textContent = now.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
}

function abrirDialogoPdv(opcoes) {
    const config = {
        title: 'Aviso',
        message: 'Verifique as informações da venda.',
        icon: 'info',
        confirmText: 'Entendi',
        alertOnly: false,
        danger: false,
        success: false,
        onConfirm: null,
        ...opcoes
    };

    const overlay = document.getElementById('pdv-dialog-overlay');
    const title = document.getElementById('pdv-dialog-title');
    const message = document.getElementById('pdv-dialog-message');
    const icon = document.getElementById('pdv-dialog-icon');
    const iconWrap = document.getElementById('pdv-dialog-icon-wrap');
    const actions = document.getElementById('pdv-dialog-actions');
    const cancel = document.getElementById('pdv-dialog-cancel');
    const confirm = document.getElementById('pdv-dialog-confirm');

    pdvDialogConfirmCallback = config.onConfirm;
    title.textContent = config.title;
    message.textContent = config.message;
    icon.setAttribute('data-lucide', config.icon);
    iconWrap.classList.toggle('danger', config.danger);
    iconWrap.classList.toggle('success', config.success);
    actions.classList.toggle('single', config.alertOnly);
    cancel.style.display = config.alertOnly ? 'none' : '';
    confirm.textContent = config.confirmText;
    confirm.className = 'btn ' + (config.danger ? 'pdv-dialog-danger' : (config.success ? 'pdv-dialog-success' : 'pdv-dialog-confirm'));
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
    lucide.createIcons();
    confirm.focus();
}

function fecharDialogoPdv(event) {
    if (event && event.target && event.target.id !== 'pdv-dialog-overlay') {
        return;
    }

    const overlay = document.getElementById('pdv-dialog-overlay');
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
    pdvDialogConfirmCallback = null;
}

function confirmarDialogoPdv() {
    const callback = pdvDialogConfirmCallback;
    fecharDialogoPdv();

    if (typeof callback === 'function') {
        callback();
    }
}

function abrirPagamentoPointAprovado(data) {
    const paymentId = data && data.payment_id ? String(data.payment_id) : '';
    const modal = document.getElementById('point-success-modal');
    const msg = document.getElementById('point-success-message');
    if(msg && paymentId) {
        msg.textContent = 'Pagamento efetuado com sucesso na Smart Point. Codigo: ' + paymentId + '.';
    }
    if(modal) {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        lucide.createIcons();
    }
}

function iniciarPollingPointPdv() {
    if (!pdvPointPending) {
        return;
    }

    let tentativas = 0;
    let modalAberto = false;

    const consultar = () => {
        fetch(pdvPointStatusUrl, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Status HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const status = String(data.status || '').toLowerCase();
                if (!modalAberto && (data.paid || pdvPointApprovedStatuses.has(status))) {
                    modalAberto = true;
                    clearInterval(intervalo);
                    abrirPagamentoPointAprovado(data);
                }
            })
            .catch(error => console.error('Falha ao consultar Smart Point:', error));

        tentativas++;
        if (tentativas > 120) {
            clearInterval(intervalo);
        }
    };

    const intervalo = setInterval(consultar, 5000);
    consultar();
}

function organizarPdvMobile() {
    const layout = document.getElementById('pdv-layout');
    const left = document.getElementById('pdv-left');
    const right = document.getElementById('pdv-right');

    if (!layout || !left || !right) {
        return;
    }

    if (window.innerWidth <= 900) {
        if (layout.firstElementChild !== right) {
            layout.insertBefore(right, left);
        }
    } else {
        if (layout.firstElementChild !== left) {
            layout.insertBefore(left, right);
        }
    }
}

function focarCarrinhoMobile() {
    const carrinhoPanel = document.getElementById('pdv-right');
    if (!carrinhoPanel || window.innerWidth > 900) {
        return;
    }

    carrinhoPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function adicionarItem(id, nome, preco, imagem = '') {
    const existente = carrinho.find(i => i.produto_id === id);
    if (existente) { existente.qtd++; }
    else { carrinho.push({produto_id: id, descricao: nome, preco: parseFloat(preco), qtd: 1, imagem: imagem}); }
    renderCarrinho();
    focarCarrinhoMobile();
}

function adicionarPersonalizado() {
    const desc = document.getElementById('custom-descricao').value.trim();
    const preco = parseFloat(document.getElementById('custom-preco').value) || 0;
    if (!desc) {
        abrirDialogoPdv({
            title: 'Item personalizado',
            message: 'Informe a descrição do item.',
            icon: 'info',
            confirmText: 'Entendi',
            alertOnly: true
        });
        return;
    }
    if (!preco) {
        abrirDialogoPdv({
            title: 'Item personalizado',
            message: 'Informe o preço.',
            icon: 'info',
            confirmText: 'Entendi',
            alertOnly: true
        });
        return;
    }
    carrinho.push({produto_id: null, descricao: desc, preco: preco, qtd: 1});
    document.getElementById('custom-descricao').value = '';
    document.getElementById('custom-preco').value = '';
    renderCarrinho();
    focarCarrinhoMobile();
}

function removerItem(idx) { carrinho.splice(idx,1); renderCarrinho(); }

function atualizarQtd(idx, val) {
    carrinho[idx].qtd = Math.max(1, parseInt(val)||1);
    calcularTotal();
    renderCarrinho();
}

function alterarQtd(idx, delta) {
    carrinho[idx].qtd = Math.max(1, carrinho[idx].qtd + delta);
    renderCarrinho();
}

function calcularTotal() {
    const subtotal = carrinho.reduce((a,i) => a + (i.preco * i.qtd), 0);
    const desconto = parseFloat(document.getElementById('desconto-input')?.value)||0;
    const total = Math.max(0, subtotal - desconto);
    const forma = (document.getElementById('forma-pagamento')?.value || '').toLowerCase();
    const taxaPercentual = taxaPointPercentual(forma);
    const taxaValor = total * (taxaPercentual / 100);
    const totalLiquido = Math.max(0, total - taxaValor);
    const taxaRow = document.getElementById('taxa-cartao-row');
    const liquidoRow = document.getElementById('liquido-cartao-row');
    const taxaLabel = document.getElementById('taxa-cartao-label');
    document.getElementById('subtotal-val').textContent = 'R$ ' + subtotal.toFixed(2).replace('.',',');
    document.getElementById('total-val').textContent = 'R$ ' + total.toFixed(2).replace('.',',');
    document.getElementById('taxa-cartao-val').textContent = 'R$ ' + taxaValor.toFixed(2).replace('.',',');
    document.getElementById('liquido-cartao-val').textContent = 'R$ ' + totalLiquido.toFixed(2).replace('.',',');
    if (taxaLabel) {
        taxaLabel.textContent = 'Taxa maquininha (' + taxaPercentual.toFixed(2).replace('.', ',') + '%)';
    }
    if (taxaRow && liquidoRow) {
        const showTaxa = taxaPercentual > 0 && total > 0;
        taxaRow.style.display = showTaxa ? 'flex' : 'none';
        liquidoRow.style.display = showTaxa ? 'flex' : 'none';
    }
    atualizarBotaoFinalizar();
}

function taxaPointPercentual(forma) {
    const point = taxasCartao.point || {};
    if (forma.includes('debito') || forma.includes('qr') || forma.includes('saldo mercado')) {
        return parseFloat(point.debitoQrSaldo || taxasCartao.debito || 0);
    }
    if (forma.includes('credito') || forma.includes('crédito') || forma.includes('cr')) {
        let base = parseFloat(point.creditoHora || taxasCartao.credito || 0);
        if (forma.includes('30')) {
            base = parseFloat(point.credito30d || base || 0);
        } else if (forma.includes('14')) {
            base = parseFloat(point.credito14d || base || 0);
        }

        const match = forma.match(/\b([2-9]|1[0-2])x\b/);
        const parcelas = match ? parseInt(match[1], 10) : 0;
        const parcelamento = point.parcelamento || {};
        const acrescimo = parcelas >= 2 ? parseFloat(parcelamento[parcelas] || 0) : 0;
        return base + acrescimo;
    }
    return 0;
}

function formaPagamentoUsaPoint() {
    const forma = (document.getElementById('forma-pagamento')?.value || '').toLowerCase();
    return forma.includes('cartao')
        || forma.includes('credito')
        || forma.includes('debito')
        || forma.includes('qr mercado')
        || forma.includes('saldo mercado');
}

function atualizarBotaoFinalizar() {
    const label = document.getElementById('btn-finalizar-label');
    const btn = document.getElementById('btn-finalizar-venda');
    if (!label || !btn) {
        return;
    }

    if (formaPagamentoUsaPoint()) {
        label.textContent = 'Enviar Smart Point';
        btn.querySelector('i')?.setAttribute('data-lucide', 'credit-card');
    } else {
        label.textContent = 'Finalizar Venda';
        btn.querySelector('i')?.setAttribute('data-lucide', 'check-circle');
    }
    lucide.createIcons();
}

function renderCarrinho() {
    const lista = document.getElementById('lista-itens');
    const emptyMsg = document.getElementById('empty-msg');
    const totaisBlock = document.getElementById('totais-block');
    const fpBlock = document.getElementById('forma-pagamento-block');
    const btnFin = document.getElementById('btn-finalizar-venda');
    const itemsSummary = document.getElementById('pdv-items-summary');

    if(carrinho.length === 0) {
        lista.innerHTML = '<div class="empty-cart" id="empty-msg"><div class="empty-cart-icon"><i data-lucide="shopping-cart"></i></div><h3>Nenhum item adicionado</h3><p>Clique nos produtos a esquerda para montar a venda.</p></div>';
        totaisBlock.style.display = 'none';
        fpBlock.style.display = 'none';
        btnFin.disabled = true;
        if (itemsSummary) {
            itemsSummary.textContent = 'Carrinho vazio';
        }
        lucide.createIcons();
        return;
    }

    totaisBlock.style.display = 'block';
    fpBlock.style.display = 'block';
    btnFin.disabled = false;
    if (itemsSummary) {
        const totalItens = carrinho.reduce((sum, item) => sum + item.qtd, 0);
        itemsSummary.textContent = totalItens + (totalItens === 1 ? ' item na venda' : ' itens na venda');
    }

    let html = '';
    carrinho.forEach((item, idx) => {
        const thumb = item.imagem
            ? `<img src="${item.imagem}" alt="" onerror="this.closest('.item-thumb').innerHTML='<i data-lucide=&quot;package&quot;></i>'; lucide.createIcons();">`
            : `<i data-lucide="package"></i>`;
        html += `<div class="item-row">
            <div class="item-thumb">${thumb}</div>
            <div class="item-row-info">
                <h5>${item.descricao}</h5>
                <div class="item-unit">
                    <span>R$ ${item.preco.toFixed(2).replace('.',',')}</span>
                    <span>×</span>
                    <div class="qty-control" aria-label="Quantidade">
                        <button type="button" onclick="alterarQtd(${idx}, -1)">−</button>
                        <input type="number" min="1" value="${item.qtd}" onchange="atualizarQtd(${idx},this.value)" aria-label="Quantidade">
                        <button type="button" onclick="alterarQtd(${idx}, 1)">+</button>
                    </div>
                </div>
            </div>
            <div class="item-row-actions">
                <span class="item-row-total">R$ ${(item.preco*item.qtd).toFixed(2).replace('.',',')}</span>
                <button class="remove-item-btn" onclick="removerItem(${idx})" title="Remover item"><i data-lucide="trash-2" style="width:16px;"></i></button>
            </div>
        </div>`;
    });
    lista.innerHTML = html;
    lucide.createIcons();
    calcularTotal();
}

function finalizarVenda() {
    if (!caixaAberto) {
        abrirDialogoPdv({
            title: 'Caixa fechado',
            message: 'Abra o caixa para finalizar vendas. Fora do horario, apenas administrador pode abrir manualmente.',
            icon: 'lock',
            confirmText: 'Entendi',
            alertOnly: true
        });
        return;
    }
    document.getElementById('hidden-cliente').value = document.getElementById('sel-cliente').value;
    document.getElementById('hidden-desconto').value = document.getElementById('desconto-input')?.value || 0;
    document.getElementById('hidden-forma').value = document.getElementById('forma-pagamento').value;
    document.getElementById('hidden-itens').value = JSON.stringify(carrinho);
    document.getElementById('hidden-enviar-point').value = formaPagamentoUsaPoint() ? '1' : '0';
    document.getElementById('form-finalizar').submit();
}

function limparCarrinho() {
    if (carrinho.length === 0) {
        abrirDialogoPdv({
            title: 'Carrinho vazio',
            message: 'Adicione um produto antes de limpar a venda atual.',
            icon: 'shopping-cart',
            confirmText: 'Entendi',
            alertOnly: true
        });
        return;
    }

    abrirDialogoPdv({
        title: 'Limpar carrinho',
        message: 'Remover todos os itens da venda atual?',
        icon: 'trash-2',
        confirmText: 'Limpar carrinho',
        danger: true,
        onConfirm: () => {
            carrinho = [];
            renderCarrinho();
        }
    });
}

function filtrarProdutos(q) {
    const cards = document.querySelectorAll('.produto-card[data-nome]');
    q = q.toLowerCase();
    cards.forEach(c => { c.style.display = c.dataset.nome.includes(q) ? '' : 'none'; });
}

try { localStorage.removeItem('theme'); } catch (error) {}

atualizarRelogioPdv();
setInterval(atualizarRelogioPdv, 1000);
organizarPdvMobile();
iniciarPollingPointPdv();
window.addEventListener('resize', organizarPdvMobile);
document.getElementById('pdv-dialog-confirm').addEventListener('click', confirmarDialogoPdv);
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        fecharDialogoPdv();
    }
});
</script>
</body>
</html>
