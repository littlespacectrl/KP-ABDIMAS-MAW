<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kuota Pupuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #0d6efd; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-custom { border-radius: 15px; border: none; transition: 0.3s; background: white; }

        /* TOAST */
        #toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            display: flex; flex-direction: column; gap: 8px;
        }
        .toast-item {
            padding: 12px 20px; border-radius: 10px; color: white;
            font-size: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease; min-width: 250px;
        }
        .toast-success { background: #198754; }
        .toast-error   { background: #dc3545; }
        .toast-warning { background: #fd7e14; }
        @keyframes slideIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }

        /* LOADING OVERLAY */
        #loading-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(255,255,255,0.7); z-index: 8888;
            justify-content: center; align-items: center;
        }
        #loading-overlay.active { display: flex; }

        /* EMPTY STATE */
        .empty-state {
            text-align: center; padding: 50px 20px; color: #adb5bd;
        }
        .empty-state .empty-icon { font-size: 50px; margin-bottom: 10px; }

        /* RESPONSIVE TABLE */
        @media (max-width: 768px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { display: block; margin-bottom: 1rem; background: white; border-radius: 10px; padding: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
            .responsive-table td { display: flex; justify-content: space-between; padding: 8px 5px; border: none; }
            .responsive-table td::before { content: attr(data-label); font-weight: bold; color: #666; }
            .btn-mobile-full { width: 100%; margin-top: 5px; }
            .modal-dialog { margin: 0; max-width: 100%; }
            .modal-content { border-radius: 20px 20px 0 0 !important; }
            .modal.fade .modal-dialog { transform: translateY(100%); }
            .modal.show .modal-dialog { transform: translateY(0); }
            .filter-row { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>

<!-- LOADING OVERLAY -->
<div id="loading-overlay">
    <div class="text-center">
        <div class="spinner-border text-primary mb-2" role="status"></div>
        <div class="text-muted small">Memproses...</div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

<div class="container py-4">

    <!-- HEADER + ROLE SWITCHER -->
    <div class="mb-4 text-center">
        <h3 class="fw-bold">Manajemen Kuota Pupuk</h3>
        <p class="text-muted mb-2">Modul Transaksi & Petani (Frontend Only)</p>
        <div class="d-inline-flex gap-2 bg-white rounded-pill px-3 py-2 shadow-sm">
            <span class="text-muted small">Mode:</span>
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-0" id="btn-mode-admin" onclick="setMode('admin')">Admin</button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-0" id="btn-mode-petani" onclick="setMode('petani')">Petani</button>
        </div>
    </div>

    <!-- ===================== VIEW ADMIN ===================== -->
    <div id="view-admin">
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="admin-tab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#admin-transaksi">📦 Transaksi</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#admin-riwayat">📜 Riwayat</button></li>
        </ul>

        <div class="tab-content">

            <!-- TAB TRANSAKSI ADMIN -->
            <div class="tab-pane fade show active" id="admin-transaksi">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0">Daftar Kuota & Pengambilan</h5>
                    <button class="btn btn-primary rounded-pill px-4 btn-mobile-full" onclick="bukaModalTambahTransaksi()">+ Tambah Transaksi</button>
                </div>
                <div class="card card-custom shadow-sm p-3 mb-3">
                    <div class="d-flex flex-wrap gap-2 filter-row">
                        <select id="filter-petani-transaksi" class="form-select form-select-sm" style="max-width:180px;" onchange="renderAdminTransaksi()">
                            <option value="">Semua Petani</option>
                        </select>
                        <select id="filter-pupuk-transaksi" class="form-select form-select-sm" style="max-width:180px;" onchange="renderAdminTransaksi()">
                            <option value="">Semua Pupuk</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table responsive-table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Petani</th>
                                <th>Jenis Pupuk</th>
                                <th>Total Kuota</th>
                                <th>Sudah Diambil</th>
                                <th>Sisa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-admin-transaksi"></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB RIWAYAT ADMIN -->
            <div class="tab-pane fade" id="admin-riwayat">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0">Riwayat Semua Transaksi</h5>
                    <button class="btn btn-outline-success btn-sm rounded-pill" onclick="exportSimulasi()">⬇ Export Excel</button>
                </div>
                <div class="card card-custom shadow-sm p-3 mb-3">
                    <div class="d-flex flex-wrap gap-2 filter-row">
                        <select id="filter-tahun-riwayat" class="form-select form-select-sm" style="max-width:130px;" onchange="renderRiwayat()">
                            <option value="">Semua Tahun</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                        <select id="filter-petani-riwayat" class="form-select form-select-sm" style="max-width:180px;" onchange="renderRiwayat()">
                            <option value="">Semua Petani</option>
                        </select>
                        <select id="filter-pupuk-riwayat" class="form-select form-select-sm" style="max-width:180px;" onchange="renderRiwayat()">
                            <option value="">Semua Pupuk</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table responsive-table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Petani</th>
                                <th>Jenis Pupuk</th>
                                <th>Jumlah</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-riwayat"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ===================== VIEW PETANI ===================== -->
    <div id="view-petani" style="display:none;">
        <div class="mb-3">
            <label class="text-muted small">Pilih Petani:</label>
            <select id="select-petani-view" class="form-select" onchange="renderViewPetani()"></select>
        </div>

        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#petani-jatah">👨‍🌾 Jatah Pupuk</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#petani-riwayat">📜 Riwayat</button></li>
        </ul>

        <div class="tab-content">
            <!-- Jatah Pupuk -->
            <div class="tab-pane fade show active" id="petani-jatah">
                <h5 class="fw-bold mb-3">Status Jatah Pupuk</h5>
                <div id="petani-jatah-container" class="row"></div>
            </div>
            <!-- Riwayat Petani -->
            <div class="tab-pane fade" id="petani-riwayat">
                <h5 class="fw-bold mb-3">Riwayat Pengambilan</h5>
                <div class="table-responsive">
                    <table class="table responsive-table align-middle">
                        <thead class="table-light">
                            <tr><th>Tanggal</th><th>Jenis Pupuk</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody id="tabel-riwayat-petani"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===================== MODAL TAMBAH TRANSAKSI ===================== -->
<div class="modal fade" id="modalTransaksi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Form Pengambilan Pupuk</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Pilih Petani -->
                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Petani</label>
                    <select id="modal-select-petani" class="form-select rounded-3" onchange="updateModalKuotaInfo()"></select>
                </div>
                <!-- Pilih Pupuk -->
                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Jenis Pupuk</label>
                    <select id="modal-select-pupuk" class="form-select rounded-3" onchange="updateModalKuotaInfo()"></select>
                </div>
                <!-- Info Kuota -->
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Total Kuota:</span>
                        <span class="small fw-bold" id="modal-info-total">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Sudah Diambil:</span>
                        <span class="small fw-bold text-warning" id="modal-info-diambil">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Sisa Tersedia:</span>
                        <span class="small fw-bold text-primary" id="modal-info-sisa">-</span>
                    </div>
                </div>
                <!-- Jumlah -->
                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Jumlah yang Diambil (kg)</label>
                    <input type="number" id="modal-input-jumlah" class="form-control rounded-3" placeholder="Contoh: 20" min="1">
                </div>
                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Tanggal Pengambilan</label>
                    <input type="date" id="modal-input-tanggal" class="form-control rounded-3">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button class="btn btn-outline-secondary rounded-pill flex-fill" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary rounded-pill flex-fill" onclick="simpanTransaksi()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="modalConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <div class="modal-body p-4 text-center">
                <div style="font-size:40px;" class="mb-2">⚠️</div>
                <h6 class="fw-bold" id="confirm-pesan">Yakin?</h6>
                <p class="text-muted small" id="confirm-sub"></p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button class="btn btn-outline-secondary rounded-pill flex-fill" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary rounded-pill flex-fill" id="confirm-ok-btn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===================== DATA DUMMY =====================
const petaniList = [
    { nik: "123", nama: "Budi",  kelompok: "Tani Makmur",  luas_lahan: 2 },
    { nik: "124", nama: "Siti",  kelompok: "Tani Sejahtera", luas_lahan: 1.5 },
    { nik: "125", nama: "Joko",  kelompok: "Tani Maju",    luas_lahan: 3 },
];

const pupukList = [
    { id: 1, nama: "Urea", satuan: "kg" },
    { id: 2, nama: "NPK",  satuan: "kg" },
    { id: 3, nama: "ZA",   satuan: "kg" },
];

// Kuota per petani per pupuk
let kuotaList = [
    { nik: "123", pupuk_id: 1, total: 100, diambil: 20 },
    { nik: "123", pupuk_id: 2, total: 50,  diambil: 10 },
    { nik: "124", pupuk_id: 1, total: 75,  diambil: 0  },
    { nik: "124", pupuk_id: 2, total: 40,  diambil: 15 },
    { nik: "125", pupuk_id: 1, total: 120, diambil: 30 },
    { nik: "125", pupuk_id: 3, total: 75,  diambil: 0  },
];

// Riwayat gabungan (pengambilan + adjustment)
let riwayatList = [
    { tanggal: "2025-03-10", nik: "123", pupuk_id: 1, jumlah: 20, tipe: "pengambilan" },
    { tanggal: "2025-02-15", nik: "123", pupuk_id: 2, jumlah: 10, tipe: "pengambilan" },
    { tanggal: "2025-01-20", nik: "124", pupuk_id: 2, jumlah: 15, tipe: "pengambilan" },
    { tanggal: "2025-03-01", nik: "125", pupuk_id: 1, jumlah: 30, tipe: "pengambilan" },
    { tanggal: "2025-01-05", nik: "123", pupuk_id: 1, jumlah: 10, tipe: "adjustment" },
];

let currentMode = 'admin';

// ===================== HELPERS =====================
function getNamaPetani(nik)  { return petaniList.find(p => p.nik === nik)?.nama || nik; }
function getNamaPupuk(id)    { return pupukList.find(p => p.id === id)?.nama || id; }
function getSisaKuota(nik, pupuk_id) {
    const k = kuotaList.find(k => k.nik === nik && k.pupuk_id === pupuk_id);
    return k ? k.total - k.diambil : 0;
}

function showToast(pesan, tipe = 'success') {
    const container = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `toast-item toast-${tipe}`;
    const icons = { success: '✅', error: '❌', warning: '⚠️' };
    el.innerHTML = `${icons[tipe] || ''} ${pesan}`;
    container.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

function showLoading(show) {
    document.getElementById('loading-overlay').classList.toggle('active', show);
}

function showConfirm(pesan, sub, callback) {
    document.getElementById('confirm-pesan').innerText = pesan;
    document.getElementById('confirm-sub').innerText = sub;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirm'));
    modal.show();
    const btn = document.getElementById('confirm-ok-btn');
    btn.onclick = () => { modal.hide(); callback(); };
}

function emptyState(pesan = "Belum ada data") {
    return `<tr><td colspan="10">
        <div class="empty-state">
            <div class="empty-icon">🌱</div>
            <div>${pesan}</div>
        </div>
    </td></tr>`;
}

// ===================== MODE SWITCHER =====================
function setMode(mode) {
    currentMode = mode;
    document.getElementById('view-admin').style.display  = mode === 'admin'  ? '' : 'none';
    document.getElementById('view-petani').style.display = mode === 'petani' ? '' : 'none';
    document.getElementById('btn-mode-admin').className  = mode === 'admin'  ? 'btn btn-sm btn-primary rounded-pill px-3 py-0' : 'btn btn-sm btn-outline-secondary rounded-pill px-3 py-0';
    document.getElementById('btn-mode-petani').className = mode === 'petani' ? 'btn btn-sm btn-primary rounded-pill px-3 py-0' : 'btn btn-sm btn-outline-secondary rounded-pill px-3 py-0';
    if (mode === 'petani') renderViewPetani();
}

// ===================== ADMIN TRANSAKSI =====================
function renderAdminTransaksi() {
    const filterPetani = document.getElementById('filter-petani-transaksi').value;
    const filterPupuk  = document.getElementById('filter-pupuk-transaksi').value;

    let rows = kuotaList.filter(k =>
        (!filterPetani || k.nik === filterPetani) &&
        (!filterPupuk  || k.pupuk_id == filterPupuk)
    );

    const tbody = document.getElementById('tabel-admin-transaksi');
    if (rows.length === 0) { tbody.innerHTML = emptyState('Tidak ada data kuota'); return; }

    tbody.innerHTML = rows.map(k => {
        const sisa = k.total - k.diambil;
        const persen = Math.round((sisa / k.total) * 100);
        const statusClass = sisa === 0 ? 'text-danger' : sisa < k.total * 0.2 ? 'text-warning' : 'text-success';
        const statusLabel = sisa === 0 ? '● Habis' : sisa < k.total * 0.2 ? '● Hampir Habis' : '● Aktif';
        return `<tr>
            <td data-label="Petani"><strong>${getNamaPetani(k.nik)}</strong><br><small class="text-muted">${k.nik}</small></td>
            <td data-label="Jenis Pupuk">${getNamaPupuk(k.pupuk_id)}</td>
            <td data-label="Total Kuota">${k.total} kg</td>
            <td data-label="Sudah Diambil"><span class="text-warning fw-semibold">${k.diambil} kg</span></td>
            <td data-label="Sisa"><span class="badge bg-primary-subtle text-primary">${sisa} kg</span></td>
            <td data-label="Status"><span class="${statusClass} small">${statusLabel}</span></td>
        </tr>`;
    }).join('');
}

// ===================== RIWAYAT =====================
function renderRiwayat() {
    const filterTahun  = document.getElementById('filter-tahun-riwayat').value;
    const filterPetani = document.getElementById('filter-petani-riwayat').value;
    const filterPupuk  = document.getElementById('filter-pupuk-riwayat').value;

    let rows = riwayatList.filter(r =>
        (!filterTahun  || r.tanggal.startsWith(filterTahun)) &&
        (!filterPetani || r.nik === filterPetani) &&
        (!filterPupuk  || r.pupuk_id == filterPupuk)
    );

    const tbody = document.getElementById('tabel-riwayat');
    if (rows.length === 0) { tbody.innerHTML = emptyState('Belum ada riwayat transaksi'); return; }

    tbody.innerHTML = rows.map(r => {
        const tipeClass = r.tipe === 'adjustment' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success';
        const tipeLabel = r.tipe === 'adjustment' ? '🔧 Adjustment' : '📦 Pengambilan';
        return `<tr>
            <td data-label="Tanggal">${r.tanggal}</td>
            <td data-label="Petani">${getNamaPetani(r.nik)}</td>
            <td data-label="Jenis Pupuk">${getNamaPupuk(r.pupuk_id)}</td>
            <td data-label="Jumlah" class="text-danger fw-semibold">-${r.jumlah} kg</td>
            <td data-label="Tipe"><span class="badge ${tipeClass}">${tipeLabel}</span></td>
        </tr>`;
    }).join('');
}

// ===================== VIEW PETANI =====================
function renderViewPetani() {
    const nik = document.getElementById('select-petani-view').value;
    if (!nik) return;

    // Jatah Pupuk
    const kuotaPetani = kuotaList.filter(k => k.nik === nik);
    const container = document.getElementById('petani-jatah-container');
    if (kuotaPetani.length === 0) {
        container.innerHTML = `<div class="col-12"><div class="empty-state"><div class="empty-icon">🌱</div><div>Belum ada kuota untuk petani ini</div></div></div>`;
    } else {
        container.innerHTML = kuotaPetani.map(k => {
            const sisa = k.total - k.diambil;
            const persen = Math.round((sisa / k.total) * 100);
            const statusClass = sisa === 0 ? 'border-danger' : sisa < k.total * 0.2 ? 'border-warning' : 'border-success';
            const statusLabel = sisa === 0 ? '<span class="text-danger">● Habis</span>' : sisa < k.total * 0.2 ? '<span class="text-warning">● Hampir Habis</span>' : '<span class="text-success">● Tersedia</span>';
            return `<div class="col-md-6 mb-3">
                <div class="card card-custom shadow-sm p-4 border-start ${statusClass} border-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">${getNamaPupuk(k.pupuk_id)}</h6>
                        ${statusLabel}
                    </div>
                    <h2 class="fw-bold text-primary mb-1">${sisa} <small class="fs-6 text-muted fw-normal">kg</small></h2>
                    <div class="text-muted small mb-2">dari ${k.total} kg total jatah</div>
                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar ${sisa < k.total*0.2 ? 'bg-warning' : 'bg-primary'}" style="width:${persen}%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Sudah diambil: <strong>${k.diambil} kg</strong></span>
                        <span>${persen}% sisa</span>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    // Riwayat Petani
    const riwayatPetani = riwayatList.filter(r => r.nik === nik);
    const tbodyRiwayat = document.getElementById('tabel-riwayat-petani');
    if (riwayatPetani.length === 0) {
        tbodyRiwayat.innerHTML = emptyState('Belum ada riwayat pengambilan');
    } else {
        tbodyRiwayat.innerHTML = riwayatPetani.map(r => `<tr>
            <td data-label="Tanggal">${r.tanggal}</td>
            <td data-label="Jenis Pupuk">${getNamaPupuk(r.pupuk_id)}</td>
            <td data-label="Jumlah" class="text-danger fw-semibold">-${r.jumlah} kg</td>
        </tr>`).join('');
    }
}

// ===================== MODAL TRANSAKSI =====================
function bukaModalTambahTransaksi() {
    // Isi dropdown
    document.getElementById('modal-select-petani').innerHTML =
        petaniList.map(p => `<option value="${p.nik}">${p.nama} (${p.nik})</option>`).join('');
    document.getElementById('modal-select-pupuk').innerHTML =
        pupukList.map(p => `<option value="${p.id}">${p.nama}</option>`).join('');

    // Set tanggal hari ini
    document.getElementById('modal-input-tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('modal-input-jumlah').value = '';

    updateModalKuotaInfo();
    new bootstrap.Modal(document.getElementById('modalTransaksi')).show();
}

function updateModalKuotaInfo() {
    const nik      = document.getElementById('modal-select-petani').value;
    const pupuk_id = parseInt(document.getElementById('modal-select-pupuk').value);
    const kuota    = kuotaList.find(k => k.nik === nik && k.pupuk_id === pupuk_id);

    if (kuota) {
        const sisa = kuota.total - kuota.diambil;
        document.getElementById('modal-info-total').innerText   = kuota.total + ' kg';
        document.getElementById('modal-info-diambil').innerText = kuota.diambil + ' kg';
        document.getElementById('modal-info-sisa').innerText    = sisa + ' kg';
    } else {
        document.getElementById('modal-info-total').innerText   = '- (belum ada kuota)';
        document.getElementById('modal-info-diambil').innerText = '-';
        document.getElementById('modal-info-sisa').innerText    = '-';
    }
}

function simpanTransaksi() {
    const nik      = document.getElementById('modal-select-petani').value;
    const pupuk_id = parseInt(document.getElementById('modal-select-pupuk').value);
    const jumlah   = parseFloat(document.getElementById('modal-input-jumlah').value);
    const tanggal  = document.getElementById('modal-input-tanggal').value;

    // Validasi
    if (!jumlah || jumlah <= 0) { showToast('Masukkan jumlah yang benar!', 'error'); return; }
    if (!tanggal) { showToast('Pilih tanggal pengambilan!', 'error'); return; }

    const kuota = kuotaList.find(k => k.nik === nik && k.pupuk_id === pupuk_id);
    if (!kuota) { showToast('Data kuota tidak ditemukan!', 'error'); return; }

    const sisa = kuota.total - kuota.diambil;
    if (jumlah > sisa) {
        showToast(`Jumlah melebihi sisa kuota! Sisa: ${sisa} kg`, 'error');
        return;
    }

    const namaPetani = getNamaPetani(nik);
    const namaPupuk  = getNamaPupuk(pupuk_id);

    showConfirm(
        `Konfirmasi Pengambilan`,
        `${namaPetani} mengambil ${jumlah} kg ${namaPupuk}`,
        () => {
            showLoading(true);
            setTimeout(() => {
                // Update kuota
                kuota.diambil += jumlah;

                // Tambah ke riwayat
                riwayatList.unshift({ tanggal, nik, pupuk_id, jumlah, tipe: 'pengambilan' });

                // Tutup modal & refresh
                bootstrap.Modal.getInstance(document.getElementById('modalTransaksi')).hide();
                renderAdminTransaksi();
                renderRiwayat();
                showLoading(false);
                showToast(`Berhasil! ${namaPetani} mengambil ${jumlah} kg ${namaPupuk}`, 'success');
            }, 600);
        }
    );
}

// ===================== EXPORT SIMULASI =====================
function exportSimulasi() {
    showLoading(true);
    setTimeout(() => {
        showLoading(false);
        showToast('Export Excel berhasil diunduh! (simulasi)', 'success');
    }, 1000);
}

// ===================== POPULATE FILTER & DROPDOWN =====================
function populateFilters() {
    const petaniOpts = petaniList.map(p => `<option value="${p.nik}">${p.nama}</option>`).join('');
    const pupukOpts  = pupukList.map(p => `<option value="${p.id}">${p.nama}</option>`).join('');

    document.getElementById('filter-petani-transaksi').innerHTML += petaniOpts;
    document.getElementById('filter-pupuk-transaksi').innerHTML  += pupukOpts;
    document.getElementById('filter-petani-riwayat').innerHTML   += petaniOpts;
    document.getElementById('filter-pupuk-riwayat').innerHTML    += pupukOpts;

    // Dropdown pilih petani di view petani
    document.getElementById('select-petani-view').innerHTML =
        petaniList.map(p => `<option value="${p.nik}">${p.nama} (${p.nik})</option>`).join('');
}

// ===================== INIT =====================
window.onload = function() {
    populateFilters();
    renderAdminTransaksi();
    renderRiwayat();
    renderViewPetani();
};
</script>
</body>
</html>
