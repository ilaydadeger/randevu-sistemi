<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Takip — {{ $appointment->artist->salon_name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f0f13;
            --surface:   #18181f;
            --border:    rgba(255,255,255,0.08);
            --text:      #f0eff4;
            --muted:     #9391a1;
            --primary:   #c4a882;
            --pending:   #f5c842;
            --approved:  #5cba8a;
            --cancelled: #e05c5c;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ── Back link ── */
        .back-link {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
            letter-spacing: 0.05em;
        }
        .back-link:hover { color: var(--text); }
        .back-link svg { width: 14px; height: 14px; }

        /* ── Card ── */
        .card {
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Status Icon ── */
        .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            animation: popIn 0.4s 0.15s cubic-bezier(0.34,1.56,0.64,1) both;
            transition: background 0.4s, border-color 0.4s;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.6); }
            to   { opacity: 1; transform: scale(1); }
        }

        .icon-pending  { background: rgba(245,200,66,0.12);  border: 1.5px solid rgba(245,200,66,0.3); }
        .icon-approved { background: rgba(92,186,138,0.12);  border: 1.5px solid rgba(92,186,138,0.3); }
        .icon-cancelled{ background: rgba(224,92,92,0.12);   border: 1.5px solid rgba(224,92,92,0.3); }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            margin-bottom: 1rem;
            transition: background 0.3s, color 0.3s;
        }
        .badge-pending   { background: rgba(245,200,66,0.15); color: var(--pending); }
        .badge-approved  { background: rgba(92,186,138,0.15); color: var(--approved); }
        .badge-cancelled { background: rgba(224,92,92,0.15);  color: var(--cancelled); }

        /* ── Typography ── */
        .headline {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 0.75rem;
            letter-spacing: -0.01em;
        }

        .body-text {
            font-size: 0.875rem;
            color: var(--muted);
            line-height: 1.65;
        }

        /* ── Info grid (approved details) ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .info-cell {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem 0.75rem;
        }
        .info-label {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.35rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.75rem 0;
        }

        /* ── Tracking pill ── */
        .tracking-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.6rem 0.85rem;
            margin-top: 1.75rem;
        }
        .tracking-label {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }
        .tracking-code {
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            color: var(--primary);
            word-break: break-all;
            text-align: right;
        }
        .copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 0.25rem;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .copy-btn:hover { color: var(--text); background: rgba(255,255,255,0.08); }
        .copy-btn svg { width: 14px; height: 14px; }

        /* ── Polling indicator ── */
        .poll-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--pending);
            margin-right: 5px;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }
        .poll-hint {
            font-size: 0.68rem;
            color: var(--muted);
            opacity: 0.55;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
        }

        /* ── Footer note ── */
        .footer-note {
            margin-top: 2rem;
            font-size: 0.72rem;
            color: var(--muted);
            opacity: 0.6;
            text-align: center;
        }

        /* ── Retry button ── */
        .retry-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.65rem 1.5rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            background: rgba(255,255,255,0.06);
            color: var(--text);
            border: 1px solid var(--border);
            transition: background 0.2s, border-color 0.2s;
        }
        .retry-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }

        /* ── Toast ── */
        .toast {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            background: #2a2a35;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 0.78rem;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            white-space: nowrap;
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

        /* Hidden sections */
        [data-section] { display: none; }
        [data-section].active { display: block; }
    </style>
</head>
<body>

    @php
        $artist    = $appointment->artist;
        $backUrl   = $artist && $artist->slug ? '/' . $artist->slug : '/';
        $salonName = $artist->salon_name ?? $artist->name ?? 'Salon';
        $status    = $appointment->status;
    @endphp

    <a href="{{ $backUrl }}" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        {{ $salonName }}
    </a>

    <div class="card" id="statusCard">

        {{-- Status Icon --}}
        <div class="icon-wrap icon-{{ $status === 'completed' ? 'approved' : $status }}" id="iconWrap">
            <span id="iconEmoji">
                @if($status === 'pending') ⏳
                @elseif($status === 'approved' || $status === 'completed') ✅
                @else ❌
                @endif
            </span>
        </div>

        {{-- Badge --}}
        <span class="badge badge-{{ $status === 'pending' ? 'pending' : ($status === 'cancelled' ? 'cancelled' : 'approved') }}" id="statusBadge">
            @if($status === 'pending') Beklemede
            @elseif($status === 'approved' || $status === 'completed') Onaylandı
            @else Reddedildi
            @endif
        </span>

        {{-- Content sections --}}
        <div data-section="pending" class="{{ $status === 'pending' ? 'active' : '' }}">
            <p class="headline">Randevunuz Onay Bekliyor</p>
            <p class="body-text">Uzmanımız talebinizi en kısa sürede değerlendirecektir.</p>
            <div class="poll-hint">
                <span class="poll-dot"></span> Durum otomatik olarak güncelleniyor…
            </div>
        </div>

        <div data-section="approved" class="{{ ($status === 'approved' || $status === 'completed') ? 'active' : '' }}">
            <p class="headline">Randevunuz Onaylandı! 🎉</p>
            <p class="body-text">Sizi bekliyoruz. Aşağıda randevu detaylarınızı görebilirsiniz.</p>
            <div class="info-grid">
                <div class="info-cell">
                    <div class="info-label">Tarih</div>
                    <div class="info-value" id="appointmentDate">
                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->locale('tr')->translatedFormat('d M Y') }}
                    </div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Saat</div>
                    <div class="info-value" id="appointmentTime">
                        {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                    </div>
                </div>
                @if($appointment->estimated_price > 0)
                <div class="info-cell" style="grid-column: 1 / -1;">
                    <div class="info-label">Tahmini Ücret</div>
                    <div class="info-value" id="appointmentPrice">
                        ₺{{ number_format($appointment->estimated_price, 0, ',', '.') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div data-section="cancelled" class="{{ $status === 'cancelled' ? 'active' : '' }}">
            <p class="headline">Randevunuz Reddedildi</p>
            <p class="body-text">
                Üzgünüz, bu sefer uygun bir zaman dilimi bulunamadı.<br>
                Farklı bir tarih veya saat seçerek tekrar randevu oluşturabilirsiniz.
            </p>
            <a href="{{ $backUrl }}" class="retry-btn">Yeni Randevu Oluştur</a>
        </div>

        <hr class="divider">

        {{-- Tracking Code Pill --}}
        {{-- Tracking Link Pill --}}
        <div class="tracking-pill cursor-pointer hover:bg-surface-variant/20 transition-colors" onclick="copyLink()" title="Linki Kopyala">
            <span class="tracking-label">Takip Linki</span>
            <div class="flex items-center gap-2">
                <span class="tracking-code !text-on-surface-variant text-[0.65rem] lowercase">Sayfa linkini kopyala</span>
                <button class="copy-btn pointer-events-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <p class="footer-note">Bu linki saklayarak durumunuzu istediğiniz zaman kontrol edebilirsiniz.</p>

    <div class="toast" id="toast">Link kopyalandı!</div>

    <script>
        const TRACKING_CODE = '{{ $appointment->tracking_code }}';
        const CHECK_URL     = '/randevu-takip/' + TRACKING_CODE + '/status';
        let   currentStatus = '{{ $status }}';
        let   pollTimer     = null;

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const t = document.getElementById('toast');
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 2000);
            });
        }

        /* ── AJAX Status Polling (only while pending) ── */
        function showSection(status) {
            document.querySelectorAll('[data-section]').forEach(el => el.classList.remove('active'));
            const section = status === 'approved' || status === 'completed' ? 'approved' : status;
            const target = document.querySelector('[data-section="' + section + '"]');
            if (target) target.classList.add('active');

            // Update badge
            const badge = document.getElementById('statusBadge');
            badge.className = 'badge badge-' + (status === 'pending' ? 'pending' : status === 'cancelled' ? 'cancelled' : 'approved');
            badge.textContent = status === 'pending' ? 'Beklemede' : (status === 'cancelled' ? 'Reddedildi' : 'Onaylandı');

            // Update icon wrap class
            const iconWrap = document.getElementById('iconWrap');
            iconWrap.className = 'icon-wrap icon-' + (status === 'completed' || status === 'approved' ? 'approved' : status === 'pending' ? 'pending' : 'cancelled');
            const iconEmoji = document.getElementById('iconEmoji');
            iconEmoji.textContent = status === 'pending' ? '⏳' : (status === 'cancelled' ? '❌' : '✅');
        }

        function pollStatus() {
            fetch(CHECK_URL, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (data.status && data.status !== currentStatus) {
                        currentStatus = data.status;
                        showSection(data.status);

                        // Update price if approved and present
                        if ((data.status === 'approved' || data.status === 'completed') && data.estimated_price) {
                            const priceEl = document.getElementById('appointmentPrice');
                            if (priceEl) priceEl.textContent = '₺' + Number(data.estimated_price).toLocaleString('tr-TR');
                        }

                        // Stop polling if no longer pending
                        if (data.status !== 'pending') {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                    }
                })
                .catch(() => {}); // silent fail — network error
        }

        // Only start polling if currently pending
        if (currentStatus === 'pending') {
            pollTimer = setInterval(pollStatus, 15000); // every 15 seconds
        }
    </script>

</body>
</html>
