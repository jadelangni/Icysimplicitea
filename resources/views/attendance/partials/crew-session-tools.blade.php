<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div id="crewCheckInModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-black">Crew Check-In</h3>
            <button type="button" onclick="closeCrewCheckInModal()" class="text-gray-400 hover:text-black">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="crewCheckInError" class="hidden mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs"></div>
        <div id="crewCheckInSuccess" class="hidden mb-3 p-2 bg-green-900/40 border border-green-500/50 rounded-lg text-green-300 text-xs"></div>

        <div id="crewQrSection">
            <p class="text-gray-400 text-xs mb-3 text-center">Scan crew member's QR code to check in.</p>
            <div class="flex justify-center">
                <div id="crewQrReader" class="rounded-xl overflow-hidden mb-3" style="width: 250px; height: 250px;"></div>
            </div>
            <p id="crewQrStatus" class="text-gray-500 text-xs text-center mb-3">Initializing camera...</p>
        </div>

        <div id="crewEmailSection" class="hidden">
            <p class="text-gray-400 text-xs mb-3">Enter crew member credentials to check them in.</p>
            <form id="crewCheckInForm" onsubmit="handleCrewEmailCheckIn(event)">
                <div class="mb-3">
                    <label for="crewEmail" class="block text-xs text-gray-400 mb-1">Email</label>
                    <input type="email" id="crewEmail" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="crew@example.com">
                </div>
                <div class="mb-4">
                    <label for="crewPassword" class="block text-xs text-gray-400 mb-1">Password</label>
                    <input type="password" id="crewPassword" required class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-black text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="••••••••">
                </div>
                <button type="submit" id="crewCheckInBtn" class="w-full py-2 bg-green-700 hover:bg-green-600 text-black text-sm font-semibold rounded-lg transition-colors">
                    Check In
                </button>
            </form>
        </div>

        <div class="mt-3 text-center">
            <button type="button" id="crewToggleMethod" onclick="toggleCrewCheckInMethod()" class="text-gray-500 hover:text-gray-300 text-xs underline">
                Use email &amp; password instead
            </button>
        </div>
    </div>
</div>

<div id="crewCheckOutModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-black">Crew Check-Out</h3>
            <button type="button" onclick="closeCrewCheckOutModal()" class="text-gray-400 hover:text-black">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="checkOutError" class="hidden mb-3 p-2 bg-red-900/40 border border-red-500/50 rounded-lg text-red-300 text-xs"></div>
        <div id="checkOutSuccess" class="hidden mb-3 p-2 bg-green-900/40 border border-green-500/50 rounded-lg text-green-300 text-xs"></div>

        <div id="checkOutQrSection">
            <p class="text-gray-400 text-xs mb-3 text-center">Scan crew member's QR code to confirm check-out.</p>
            <div class="flex justify-center">
                <div id="checkOutQrReader" class="rounded-xl overflow-hidden mb-3" style="width: 250px; height: 250px;"></div>
            </div>
            <p id="checkOutQrStatus" class="text-gray-500 text-xs text-center mb-3">Initializing camera...</p>
        </div>

        <div id="checkOutEmailSection" class="hidden">
            <p class="text-gray-600 text-xs mb-3">Enter crew member credentials to confirm check-out.</p>
            <form id="checkOutForm" onsubmit="handleCheckOutEmailSubmit(event)">
                <div class="mb-3">
                    <label for="checkOutEmail" class="block text-xs text-gray-600 mb-1">Email</label>
                    <input type="email" id="checkOutEmail" required class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="crew@example.com">
                </div>
                <div class="mb-4">
                    <label for="checkOutPassword" class="block text-xs text-gray-600 mb-1">Password</label>
                    <input type="password" id="checkOutPassword" required class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="••••••••">
                </div>
                <button type="submit" id="checkOutBtn" class="w-full py-2 bg-red-700 hover:bg-red-600 text-black text-sm font-semibold rounded-lg transition-colors">
                    Confirm Check Out
                </button>
            </form>
        </div>

        <div class="mt-3 text-center">
            <button type="button" id="checkOutToggleMethod" onclick="toggleCheckOutMethod()" class="text-gray-500 hover:text-gray-300 text-xs underline">
                Use email &amp; password instead
            </button>
        </div>
    </div>
</div>

<script>
    let crewQrScanner = null;
    let crewQrIsProcessing = false;
    let crewCheckInMode = 'qr';

    function openCrewCheckInModal() {
        const modal = document.getElementById('crewCheckInModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('crewCheckInError').classList.add('hidden');
        document.getElementById('crewCheckInSuccess').classList.add('hidden');
        crewCheckInMode = 'qr';
        document.getElementById('crewQrSection').classList.remove('hidden');
        document.getElementById('crewEmailSection').classList.add('hidden');
        document.getElementById('crewToggleMethod').textContent = 'Use email & password instead';
        startCrewQrScanner();
    }

    function closeCrewCheckInModal() {
        const modal = document.getElementById('crewCheckInModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        stopCrewQrScanner();
    }

    function toggleCrewCheckInMethod() {
        const qrSection = document.getElementById('crewQrSection');
        const emailSection = document.getElementById('crewEmailSection');
        const toggleBtn = document.getElementById('crewToggleMethod');
        document.getElementById('crewCheckInError').classList.add('hidden');
        document.getElementById('crewCheckInSuccess').classList.add('hidden');

        if (crewCheckInMode === 'qr') {
            stopCrewQrScanner();
            qrSection.classList.add('hidden');
            emailSection.classList.remove('hidden');
            toggleBtn.textContent = 'Use QR scan instead';
            crewCheckInMode = 'email';
            document.getElementById('crewEmail').value = '';
            document.getElementById('crewPassword').value = '';
            document.getElementById('crewEmail').focus();
        } else {
            emailSection.classList.add('hidden');
            qrSection.classList.remove('hidden');
            toggleBtn.textContent = 'Use email & password instead';
            crewCheckInMode = 'qr';
            startCrewQrScanner();
        }
    }

    function startCrewQrScanner() {
        const statusEl = document.getElementById('crewQrStatus');
        statusEl.textContent = 'Initializing camera...';
        statusEl.className = 'text-gray-500 text-xs text-center mb-3';
        crewQrIsProcessing = false;

        if (crewQrScanner) {
            try { crewQrScanner.stop().catch(() => {}); } catch(e) {}
            crewQrScanner = null;
        }

        crewQrScanner = new Html5Qrcode('crewQrReader');

        crewQrScanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1, disableFlip: false },
            (decodedText) => onCrewQrScanSuccess(decodedText),
            () => {}
        ).then(() => {
            statusEl.textContent = 'Point camera at crew member\'s QR code';
        }).catch(() => {
            statusEl.textContent = 'Camera not available. Use email login below.';
            statusEl.className = 'text-red-400 text-xs text-center mb-3';
        });
    }

    function stopCrewQrScanner() {
        if (crewQrScanner) {
            try {
                crewQrScanner.stop().catch(() => {});
            } catch(e) {}
            crewQrScanner = null;
        }
        crewQrIsProcessing = false;
    }

    async function onCrewQrScanSuccess(qrToken) {
        if (crewQrIsProcessing) return;
        crewQrIsProcessing = true;

        const errorDiv = document.getElementById('crewCheckInError');
        const successDiv = document.getElementById('crewCheckInSuccess');
        const statusEl = document.getElementById('crewQrStatus');
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        statusEl.textContent = 'Processing...';

        try { if (crewQrScanner) crewQrScanner.pause(); } catch(e) {}

        try {
            const response = await fetch('{{ route("crew-session.check-in") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_token: qrToken })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successDiv.textContent = data.message || 'Crew member checked in!';
                successDiv.classList.remove('hidden');
                statusEl.textContent = 'Check-in successful!';
                statusEl.className = 'text-green-400 text-xs text-center mb-3';
                refreshCrewList();
                setTimeout(() => closeCrewCheckInModal(), 1500);
            } else {
                errorDiv.textContent = data.message || 'Invalid QR code.';
                errorDiv.classList.remove('hidden');
                statusEl.textContent = 'Scan failed. Try again.';
                setTimeout(() => {
                    crewQrIsProcessing = false;
                    try { if (crewQrScanner) crewQrScanner.resume(); } catch(e) {}
                    statusEl.textContent = 'Point camera at crew member\'s QR code';
                    statusEl.className = 'text-gray-500 text-xs text-center mb-3';
                }, 2500);
                return;
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.remove('hidden');
            setTimeout(() => {
                crewQrIsProcessing = false;
                try { if (crewQrScanner) crewQrScanner.resume(); } catch(e) {}
            }, 2500);
            return;
        }
    }

    async function handleCrewEmailCheckIn(e) {
        e.preventDefault();
        const btn = document.getElementById('crewCheckInBtn');
        const errorDiv = document.getElementById('crewCheckInError');
        const successDiv = document.getElementById('crewCheckInSuccess');
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        btn.disabled = true;
        btn.textContent = 'Checking in...';

        try {
            const response = await fetch('{{ route("crew-session.check-in") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: document.getElementById('crewEmail').value,
                    password: document.getElementById('crewPassword').value
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successDiv.textContent = data.message || 'Crew member checked in!';
                successDiv.classList.remove('hidden');
                document.getElementById('crewEmail').value = '';
                document.getElementById('crewPassword').value = '';
                refreshCrewList();
                setTimeout(() => closeCrewCheckInModal(), 1500);
            } else {
                errorDiv.textContent = data.message || 'Check-in failed.';
                errorDiv.classList.remove('hidden');
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Check In';
        }
    }

    let crewCheckOutSessionId = null;
    let checkOutQrScanner = null;
    let checkOutQrIsProcessing = false;
    let checkOutMode = 'qr';

    function handleCrewCheckOut(sessionId) {
        crewCheckOutSessionId = sessionId;
        openCrewCheckOutModal();
    }

    function openCrewCheckOutModal() {
        const modal = document.getElementById('crewCheckOutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('checkOutError').classList.add('hidden');
        document.getElementById('checkOutSuccess').classList.add('hidden');
        checkOutMode = 'qr';
        document.getElementById('checkOutQrSection').classList.remove('hidden');
        document.getElementById('checkOutEmailSection').classList.add('hidden');
        document.getElementById('checkOutToggleMethod').textContent = 'Use email & password instead';
        startCheckOutQrScanner();
    }

    function closeCrewCheckOutModal() {
        const modal = document.getElementById('crewCheckOutModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        stopCheckOutQrScanner();
        crewCheckOutSessionId = null;
    }

    function toggleCheckOutMethod() {
        const qrSection = document.getElementById('checkOutQrSection');
        const emailSection = document.getElementById('checkOutEmailSection');
        const toggleBtn = document.getElementById('checkOutToggleMethod');
        document.getElementById('checkOutError').classList.add('hidden');
        document.getElementById('checkOutSuccess').classList.add('hidden');

        if (checkOutMode === 'qr') {
            stopCheckOutQrScanner();
            qrSection.classList.add('hidden');
            emailSection.classList.remove('hidden');
            toggleBtn.textContent = 'Use QR scan instead';
            checkOutMode = 'email';
            document.getElementById('checkOutEmail').value = '';
            document.getElementById('checkOutPassword').value = '';
            document.getElementById('checkOutEmail').focus();
        } else {
            emailSection.classList.add('hidden');
            qrSection.classList.remove('hidden');
            toggleBtn.textContent = 'Use email & password instead';
            checkOutMode = 'qr';
            startCheckOutQrScanner();
        }
    }

    function startCheckOutQrScanner() {
        const statusEl = document.getElementById('checkOutQrStatus');
        statusEl.textContent = 'Initializing camera...';
        statusEl.className = 'text-gray-500 text-xs text-center mb-3';
        checkOutQrIsProcessing = false;

        if (checkOutQrScanner) {
            try { checkOutQrScanner.stop().catch(() => {}); } catch(e) {}
            checkOutQrScanner = null;
        }

        checkOutQrScanner = new Html5Qrcode('checkOutQrReader');

        checkOutQrScanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1, disableFlip: false },
            (decodedText) => onCheckOutQrScanSuccess(decodedText),
            () => {}
        ).then(() => {
            statusEl.textContent = 'Scan crew member\'s QR code to confirm check-out';
        }).catch(() => {
            statusEl.textContent = 'Camera not available. Use email option below.';
            statusEl.className = 'text-red-400 text-xs text-center mb-3';
        });
    }

    function stopCheckOutQrScanner() {
        if (checkOutQrScanner) {
            try { checkOutQrScanner.stop().catch(() => {}); } catch(e) {}
            checkOutQrScanner = null;
        }
        checkOutQrIsProcessing = false;
    }

    async function onCheckOutQrScanSuccess(qrToken) {
        if (checkOutQrIsProcessing) return;
        checkOutQrIsProcessing = true;

        const errorDiv = document.getElementById('checkOutError');
        const successDiv = document.getElementById('checkOutSuccess');
        const statusEl = document.getElementById('checkOutQrStatus');
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        statusEl.textContent = 'Processing...';

        try { if (checkOutQrScanner) checkOutQrScanner.pause(); } catch(e) {}

        try {
            const response = await fetch('{{ route("crew-session.check-out") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ session_id: crewCheckOutSessionId, qr_token: qrToken })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successDiv.textContent = data.message || 'Crew member checked out!';
                successDiv.classList.remove('hidden');
                statusEl.textContent = 'Check-out confirmed!';
                statusEl.className = 'text-green-400 text-xs text-center mb-3';
                refreshCrewList();
                setTimeout(() => closeCrewCheckOutModal(), 1500);
            } else {
                errorDiv.textContent = data.message || 'QR does not match.';
                errorDiv.classList.remove('hidden');
                setTimeout(() => {
                    checkOutQrIsProcessing = false;
                    try { if (checkOutQrScanner) checkOutQrScanner.resume(); } catch(e) {}
                    statusEl.textContent = 'Scan crew member\'s QR code to confirm check-out';
                    statusEl.className = 'text-gray-500 text-xs text-center mb-3';
                }, 2500);
                return;
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.remove('hidden');
            setTimeout(() => {
                checkOutQrIsProcessing = false;
                try { if (checkOutQrScanner) checkOutQrScanner.resume(); } catch(e) {}
            }, 2500);
            return;
        }
    }

    async function handleCheckOutEmailSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('checkOutBtn');
        const errorDiv = document.getElementById('checkOutError');
        const successDiv = document.getElementById('checkOutSuccess');
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        btn.disabled = true;
        btn.textContent = 'Checking out...';

        try {
            const response = await fetch('{{ route("crew-session.check-out") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: crewCheckOutSessionId,
                    email: document.getElementById('checkOutEmail').value,
                    password: document.getElementById('checkOutPassword').value
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successDiv.textContent = data.message || 'Crew member checked out!';
                successDiv.classList.remove('hidden');
                refreshCrewList();
                setTimeout(() => closeCrewCheckOutModal(), 1500);
            } else {
                errorDiv.textContent = data.message || 'Check-out failed.';
                errorDiv.classList.remove('hidden');
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Confirm Check Out';
        }
    }

    async function refreshCrewList() {
        try {
            const response = await fetch('{{ route("crew-session.active") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            const container = document.getElementById('crewListContainer');
            if (!container) return;

            if (data.crew && data.crew.length > 0) {
                let html = '<div class="mb-3 p-2 bg-yellow-900/30 border border-yellow-500/30 rounded-lg">';
                html += '<p class="text-yellow-300 text-xs font-semibold mb-1">Active Crew Members:</p>';
                data.crew.forEach(member => {
                    html += '<div class="flex items-center justify-between py-1">';
                    html += '<p class="text-yellow-200 text-xs">• ' + member.name + '</p>';
                    html += '<button type="button" onclick="handleCrewCheckOut(' + member.session_id + ')" class="text-red-400 hover:text-red-300 text-xs underline">Check Out</button>';
                    html += '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '';
            }
        } catch (err) {
            console.error('Failed to refresh crew list:', err);
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCrewCheckInModal();
            closeCrewCheckOutModal();
        }
    });

    document.getElementById('crewCheckInModal').addEventListener('click', function(e) {
        if (e.target === this) closeCrewCheckInModal();
    });

    document.getElementById('crewCheckOutModal').addEventListener('click', function(e) {
        if (e.target === this) closeCrewCheckOutModal();
    });

    refreshCrewList();
</script>
