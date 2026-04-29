@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-effect rounded-xl shadow-lg p-6 lg:p-8 border border-white/20 mb-8">
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-balance-scale text-blue-500 mr-3"></i>Master Jatah Pupuk per Ha</h1>
            <p class="text-gray-600">Tentukan standar jatah pupuk (Kg) untuk setiap 1 Hektar lahan.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="glass-effect rounded-xl shadow-lg p-6 border border-white/20">
                    <h3 class="text-lg font-semibold mb-4">Input Aturan Baru</h3>
                    <form id="ruleForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Pupuk</label>
                            <select id="select_pupuk" class="w-full h-12 px-4 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:outline-none bg-white" required>
                                <option value="">Pilih Pupuk...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jatah (Kg / Ha)</label>
                            <input type="number" id="kg_per_ha" class="w-full h-12 px-4 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:outline-none" placeholder="Contoh: 200" required>
                        </div>
                        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 smooth-transition">Simpan Aturan</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="glass-effect rounded-xl shadow-lg p-6 border border-white/20">
                    <table id="rule-table" class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Jenis Pupuk</th>
                                <th class="text-left">Jatah (Kg/Ha)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rule-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // State
    let pupukList = JSON.parse(localStorage.getItem('pupukList')) || [{id:1, nama_pupuk:'Urea'}, {id:2, nama_pupuk:'NPK'}];
    let rulePerHa = [{id: 1, id_pupuk: 1, kg_per_ha: 200}];

    function render() {
        // Render Dropdown
        let options = '<option value="">Pilih Pupuk...</option>';
        pupukList.forEach(p => options += `<option value="${p.id}">${p.nama_pupuk}</option>`);
        document.getElementById('select_pupuk').innerHTML = options;

        // Render Table
        let html = '';
        rulePerHa.forEach(r => {
            const pupuk = pupukList.find(p => p.id == r.id_pupuk);
            html += `<tr class="border-b">
                <td class="py-4 font-medium">${pupuk ? pupuk.nama_pupuk : 'Unknown'}</td>
                <td class="py-4">${r.kg_per_ha} Kg</td>
                <td class="py-4 text-center">
                    <button onclick="hapusRule(${r.id})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        document.getElementById('rule-body').innerHTML = html;
    }

    document.getElementById('ruleForm').onsubmit = (e) => {
        e.preventDefault();
        rulePerHa.push({
            id: Date.now(),
            id_pupuk: document.getElementById('select_pupuk').value,
            kg_per_ha: document.getElementById('kg_per_ha').value
        });
        render();
        Swal.fire({icon:'success', title:'Tersimpan!', toast:true, position:'top-end', showConfirmButton:false, timer:2000});
    };

    function hapusRule(id) { rulePerHa = rulePerHa.filter(r => r.id !== id); render(); }
    window.onload = render;
</script>
@endsection
