@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-effect rounded-xl shadow-lg p-6 lg:p-8 border border-white/20 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-seedling text-green-500 mr-3"></i>Master Jenis Pupuk
                    </h1>
                    <p class="text-gray-600">Kelola data jenis pupuk dan satuannya</p>
                </div>
                <button onclick="openAddModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl smooth-transition hover-lift">
                    <i class="fas fa-plus mr-2"></i>Tambah Pupuk
                </button>
            </div>
        </div>

        <div class="glass-effect rounded-xl shadow-lg border border-white/20 overflow-hidden">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="pupuk-table" class="display w-full">
                        <thead>
                            <tr>
                                <th class="text-left">No</th>
                                <th class="text-left">Nama Pupuk</th>
                                <th class="text-left">Satuan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pupukModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <form id="pupukForm">
                <div class="modal-header bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-t-lg">
                    <h5 class="modal-title font-bold" id="modalTitle">
                        <i class="fas fa-seedling mr-2"></i>Tambah Jenis Pupuk
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-6 space-y-6">
                    <input type="hidden" id="pupuk_id">

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-leaf text-green-500 mr-2"></i>Nama Pupuk
                        </label>
                        <input type="text" id="nama_pupuk"
                               class="w-full h-12 px-4 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:outline-none smooth-transition"
                               placeholder="Contoh: Urea, NPK" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-weight text-blue-500 mr-2"></i>Satuan
                        </label>
                        <input type="text" id="satuan_pupuk"
                               class="w-full h-12 px-4 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:outline-none smooth-transition"
                               placeholder="Contoh: Kg, Liter" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-6 pt-0">
                    <button type="button"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 smooth-transition mr-3"
                            data-bs-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:shadow-lg smooth-transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .glass-effect {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    .smooth-transition { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-2px); }
</style>

<script>
    // --- STATE MANAGEMENT JS (DUMMY DATA) ---
    let pupukList = [
        { id: 1, nama_pupuk: 'Urea', satuan: 'Kg' },
        { id: 2, nama_pupuk: 'NPK Phonska', satuan: 'Kg' }
    ];
    let autoId = 3;

    let table;
    let modal;

    document.addEventListener('DOMContentLoaded', () => {
        // Inisialisasi Modal Bootstrap
        modal = new bootstrap.Modal(document.getElementById('pupukModal'));
        initDataTable();
    });

    function initDataTable() {
        table = $('#pupuk-table').DataTable({
            data: pupukList,
            destroy: true,
            responsive: true,
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    render: (data, type, row, meta) => `<span class="font-semibold text-gray-600">${meta.row + 1}</span>`
                },
                {
                    data: 'nama_pupuk',
                    render: data => `<span class="font-medium text-gray-800">${data}</span>`
                },
                {
                    data: 'satuan',
                    render: data => `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">${data}</span>`
                },
                {
                    data: null,
                    className: 'text-center',
                    render: data => `
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick='openEditModal(${JSON.stringify(data)})'
                                    class='inline-flex items-center px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 smooth-transition text-sm'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick='deletePupuk(${data.id})'
                                    class='inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 smooth-transition text-sm'>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `
                }
            ],
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" }
        });
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-seedling mr-2"></i>Tambah Jenis Pupuk';
        document.getElementById('pupukForm').reset();
        document.getElementById('pupuk_id').value = '';
        modal.show();
    }

    function openEditModal(item) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Jenis Pupuk';
        document.getElementById('pupuk_id').value = item.id;
        document.getElementById('nama_pupuk').value = item.nama_pupuk;
        document.getElementById('satuan_pupuk').value = item.satuan;
        modal.show();
    }

    // Handle Form Submit (Tambah / Edit Local State)
    document.getElementById('pupukForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('pupuk_id').value;
        const nama = document.getElementById('nama_pupuk').value;
        const satuan = document.getElementById('satuan_pupuk').value;

        if (id) {
            // Edit Data
            let index = pupukList.findIndex(p => p.id == id);
            if(index !== -1) {
                pupukList[index].nama_pupuk = nama;
                pupukList[index].satuan = satuan;
            }
        } else {
            // Tambah Data Baru
            pupukList.push({
                id: autoId++,
                nama_pupuk: nama,
                satuan: satuan
            });
        }

        modal.hide();
        // Update table Datatables
        table.clear().rows.add(pupukList).draw();

        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: 'Data pupuk berhasil disimpan',
            timer: 2000, showConfirmButton: false, toast: true, position: 'top-end'
        });
    });

    // Handle Delete Local State
    function deletePupuk(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?', text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280', confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Hapus data dari array javascript
                pupukList = pupukList.filter(p => p.id !== id);
                table.clear().rows.add(pupukList).draw(); // Update tabel

                Swal.fire({
                    icon: 'success', title: 'Terhapus!', text: 'Data pupuk berhasil dihapus',
                    timer: 2000, showConfirmButton: false, toast: true, position: 'top-end'
                });
            }
        });
    }
</script>
@endsection
