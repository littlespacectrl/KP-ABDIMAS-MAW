@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-effect rounded-xl shadow-lg p-6 lg:p-8 border border-white/20 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-calculator text-purple-500 mr-3"></i>Pengelolaan Kuota Pupuk
                    </h1>
                    <p class="text-gray-600">Generate massal kuota petani berdasarkan luas lahan dan master jatah.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="filterTahun" class="h-11 px-4 rounded-lg border-2 border-gray-200 focus:outline-none focus:border-purple-500 bg-white">
                        <option value="2025">Tahun 2025</option>
                        <option value="2026">Tahun 2026</option>
                    </select>
                    <button onclick="generateKuotaMassal()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-lg shadow hover:shadow-lg smooth-transition">
                        <i class="fas fa-sync-alt mr-2"></i>Generate Kuota
                    </button>
                    <button onclick="exportExcel()" class="inline-flex items-center px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow hover:bg-green-600 smooth-transition">
                        <i class="fas fa-file-excel mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-xl shadow-lg border border-white/20 overflow-hidden mb-8">
            <div class="p-6">
                <div id="loadingState" class="hidden py-12 text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-purple-500 mb-4"></i>
                    <p class="text-gray-600 font-medium">Sedang menghitung kuota untuk seluruh petani...</p>
                </div>

                <div id="emptyState" class="py-12 text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty" class="w-32 mx-auto mb-4 opacity-50">
                    <h3 class="text-lg font-bold text-gray-700">Data Kuota Kosong</h3>
                    <p class="text-gray-500">Klik tombol "Generate Kuota" di atas untuk mulai menghitung.</p>
                </div>

                <div id="tableContainer" class="hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-200 text-sm uppercase tracking-wider text-gray-600">
                                <th class="py-3 px-4 font-semibold">NIK</th>
                                <th class="py-3 px-4 font-semibold">Nama Petani</th>
                                <th class="py-3 px-4 font-semibold">Kelompok</th>
                                <th class="py-3 px-4 font-semibold text-center">Luas Lahan</th>
                                <th class="py-3 px-4 font-semibold text-center">Jatah Urea</th>
                                <th class="py-3 px-4 font-semibold text-center">Jatah NPK</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kuota-body" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-effect { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95); }
    .smooth-transition { transition: all 0.3s ease; }
</style>

<script>
    // --- STATE MANAGEMENT JS ---
    const petaniList = [
        { nik: "3302111", nama: "Budi Santoso", kelompok: "Tani Makmur", luas_lahan: 2 },
        { nik: "3302112", nama: "Andi Saputra", kelompok: "Suka Maju", luas_lahan: 1.5 },
        { nik: "3302113", nama: "Siti Aminah", kelompok: "Tani Makmur", luas_lahan: 0.5 }
    ];

    // Master Jatah (diambil dari logika halaman sebelumnya)
    const rulePerHa = [
        { id_pupuk: 1, nama: "Urea", kg_per_ha: 200 },
        { id_pupuk: 2, nama: "NPK", kg_per_ha: 300 }
    ];

    let kuotaList = [];

    function generateKuotaMassal() {
        if(kuotaList.length > 0) {
            Swal.fire({
                title: 'Generate Ulang?', text: "Data kuota yang sudah ada akan ditimpa!", icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Generate!', cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) processGenerate(); });
        } else {
            processGenerate();
        }
    }

    function processGenerate() {
        // Tampilkan loading state
        document.getElementById('emptyState').classList.add('hidden');
        document.getElementById('tableContainer').classList.add('hidden');
        document.getElementById('loadingState').classList.remove('hidden');

        // Simulasi proses delay
        setTimeout(() => {
            // Core Logic: Hitung Kuota
            kuotaList = petaniList.map(petani => {
                // kuota = luas_lahan * kg_per_ha
                let urea = petani.luas_lahan * rulePerHa.find(r => r.id_pupuk === 1).kg_per_ha;
                let npk = petani.luas_lahan * rulePerHa.find(r => r.id_pupuk === 2).kg_per_ha;

                return { ...petani, kuota_urea: urea, kuota_npk: npk };
            });

            renderTable();

            // Sembunyikan loading, tampilkan tabel
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('tableContainer').classList.remove('hidden');

            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Kuota berhasil di-generate berdasarkan luas lahan!', timer: 2000, showConfirmButton: false });
        }, 1200); // 1.2 detik delay simulasi
    }

    function renderTable() {
        let html = '';
        kuotaList.forEach((k, index) => {
            html += `<tr class="hover:bg-gray-50 smooth-transition">
                <td class="py-3 px-4 font-mono text-sm">${k.nik}</td>
                <td class="py-3 px-4 font-medium text-gray-800">${k.nama}</td>
                <td class="py-3 px-4 text-sm">${k.kelompok}</td>
                <td class="py-3 px-4 text-center"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">${k.luas_lahan} Ha</span></td>
                <td class="py-3 px-4 text-center font-semibold text-blue-600">${k.kuota_urea} Kg</td>
                <td class="py-3 px-4 text-center font-semibold text-orange-500">${k.kuota_npk} Kg</td>
                <td class="py-3 px-4 text-center">
                    <button onclick="adjustManual(${index})" class="text-sm bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200 smooth-transition">
                        <i class="fas fa-edit mr-1"></i>Adjust
                    </button>
                </td>
            </tr>`;
        });
        document.getElementById('kuota-body').innerHTML = html;
    }

    function adjustManual(index) {
        Swal.fire({
            title: `Adjust Kuota: ${kuotaList[index].nama}`,
            html: `
                <div class="mt-3 text-left">
                    <label class="block text-sm font-medium mb-1">Urea (Kg)</label>
                    <input id="swal-urea" class="swal2-input w-full m-0 h-10" value="${kuotaList[index].kuota_urea}">
                    <label class="block text-sm font-medium mb-1 mt-3">NPK (Kg)</label>
                    <input id="swal-npk" class="swal2-input w-full m-0 h-10" value="${kuotaList[index].kuota_npk}">
                </div>`,
            focusConfirm: false,
            showCancelButton: true,
            preConfirm: () => {
                return {
                    urea: document.getElementById('swal-urea').value,
                    npk: document.getElementById('swal-npk').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                kuotaList[index].kuota_urea = parseInt(result.value.urea);
                kuotaList[index].kuota_npk = parseInt(result.value.npk);
                renderTable();
                Swal.fire({icon: 'success', title: 'Diperbarui', text:'Adjustment manual berhasil disimpan.', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000});
            }
        });
    }

    function exportExcel() {
        if(kuotaList.length === 0) return Swal.fire('Kosong', 'Generate data terlebih dahulu sebelum export.', 'error');
        Swal.fire({ icon: 'success', title: 'Downloading...', text: 'Simulasi export data ke Excel (.xlsx) berjalan.', timer: 2500, showConfirmButton: false });
    }
</script>
@endsection
