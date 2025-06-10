// script.js
$(function() {
    loadBMIHistory();
    $('#bmiForm').on('submit', function(e) {
        e.preventDefault();
        calculateBMI();
    });
    $('#closeModal, #modalConfirm, #modalCancel').on('click', closeModal);
});

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

function calculateBMI() {
    let nama = $('#nama').val().trim();
    let usia = parseInt($('#usia').val());
    let jenisKelamin = $('#jenisKelamin').val();
    let berat = parseFloat($('#berat').val());
    let tinggi = parseFloat($('#tinggi').val());
    if (!nama.match(/^[A-Za-z\s]{3,30}$/)) {
        showModal('Error', 'Nama hanya boleh huruf, 3-30 karakter.');
        return;
    }
    if (!nama || !usia || !jenisKelamin || !berat || !tinggi) {
        showModal('Error', 'Semua field harus diisi!');
        return;
    }
    if (isNaN(usia) || usia < 1 || usia > 120) {
        showModal('Error', 'Usia harus antara 1-120 tahun.');
        return;
    }
    if (isNaN(berat) || berat < 20 || berat > 300) {
        showModal('Error', 'Berat badan harus antara 20-300 kg.');
        return;
    }
    if (isNaN(tinggi) || tinggi < 50 || tinggi > 250) {
        showModal('Error', 'Tinggi badan harus antara 50-250 cm.');
        return;
    }

    let tinggiMeter = tinggi / 100;
    let bmi = berat / (tinggiMeter * tinggiMeter);
    let bmiRounded = Math.round(bmi * 10) / 10;

    let kategori, textColor;
    if (bmiRounded < 18.5) {
        kategori = 'Kekurangan Berat Badan'; textColor = 'text-blue-500';
    } else if (bmiRounded < 25) {
        kategori = 'Normal'; textColor = 'text-green-500';
    } else if (bmiRounded < 30) {
        kategori = 'Kelebihan Berat Badan'; textColor = 'text-yellow-500';
    } else {
        kategori = 'Obesitas'; textColor = 'text-red-500';
    }

    $.ajax({
        url: 'api/save_bmi.php',
        type: 'POST',
        dataType: 'json',
        data: {
            nama: nama,
            usia: usia,
            jenis_kelamin: jenisKelamin,
            berat: berat,
            tinggi: tinggi,
            bmi: bmiRounded,
            kategori: kategori
        }
    }).done(function(result) {
        if (result.status === 'success') {
            showModal('Hasil BMI', `
                <div class="text-center">
                    <div class="text-xl mb-2">BMI Anda: <span class="font-bold ${textColor}">${bmiRounded}</span></div>
                    <div class="text-lg mb-4">Kategori: <span class="font-bold ${textColor}">${kategori}</span></div>
                </div>
            `);
            $('#bmiForm')[0].reset();
            loadBMIHistory();
        } else {
            showModal('Error', result.message || 'Terjadi kesalahan saat menyimpan data.');
        }
    }).fail(function(xhr) {
        showModal('Error', 'Terjadi kesalahan pada server.');
    });
}

function loadBMIHistory() {
    $.ajax({
        url: 'api/get_bmi_history.php',
        dataType: 'json'
    }).done(function(result) {
        if (result.status === 'success') {
            displayBMIHistory(result.data);
        } else {
            showModal('Error', result.message || 'Gagal menampilkan riwayat.');
        }
    }).fail(function() {
        showModal('Error', 'Gagal terhubung ke server (riwayat).');
    });
}

function displayBMIHistory(data) {
    const tbody = $('#historyData').empty();
    if (!data || data.length === 0) {
        tbody.append(`<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data BMI tersimpan</td></tr>`);
        return;
    }
    data.forEach(item => {
        let textColor = '';
        if (item.kategori === 'Kekurangan Berat Badan') textColor = 'text-blue-500';
        else if (item.kategori === 'Normal') textColor = 'text-green-500';
        else if (item.kategori === 'Kelebihan Berat Badan') textColor = 'text-yellow-500';
        else if (item.kategori === 'Obesitas') textColor = 'text-red-500';
        tbody.append(`
            <tr>
                <td class="px-6 py-4">${escapeHtml(item.nama)}</td>
                <td class="px-6 py-4">${item.usia} / ${escapeHtml(item.jenis_kelamin)}</td>
                <td class="px-6 py-4">${formatDate(item.tanggal)}</td>
                <td class="px-6 py-4 font-bold">${item.bmi}</td>
                <td class="px-6 py-4 font-bold ${textColor}">${escapeHtml(item.kategori)}</td>
                <td class="px-6 py-4">
                    <button onclick="deleteRecord(${item.id})" class="text-red-500 hover:text-red-700">Hapus</button>
                </td>
            </tr>
        `);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const d = String(date.getDate()).padStart(2, '0');
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const y = date.getFullYear();
    const h = String(date.getHours()).padStart(2, '0');
    const min = String(date.getMinutes()).padStart(2, '0');
    return `${d}/${m}/${y} ${h}:${min}`;
}

function deleteRecord(id) {
    showConfirmModal('Konfirmasi', 'Hapus data ini?', function() {
        $.ajax({
            url: 'api/delete_bmi.php',
            type: 'POST',
            dataType: 'json',
            data: { id: id }
        }).done(function(result) {
            if (result.status === 'success') {
                showModal('Sukses', 'Data berhasil dihapus.');
                loadBMIHistory();
            } else {
                showModal('Error', result.message || 'Gagal menghapus data.');
            }
        }).fail(function() {
            showModal('Error', 'Gagal terhubung ke server.');
        });
    });
}

// Modal helpers
function showModal(title, message) {
    $('#modalTitle').text(title);
    $('#modalBody').html(message);
    $('#modalContainer').removeClass('hidden');
    $('#modalConfirm').off().on('click', closeModal);
    $('#modalCancel').addClass('hidden');
}
function showConfirmModal(title, message, onConfirm) {
    $('#modalTitle').text(title);
    $('#modalBody').html(message);
    $('#modalContainer').removeClass('hidden');
    $('#modalConfirm').text('Ya').off().on('click', function() {
        onConfirm();
        closeModal();
    });
    $('#modalCancel').removeClass('hidden').text('Tidak');
}
function closeModal() {
    $('#modalContainer').addClass('hidden');
    $('#modalConfirm').text('OK');
    $('#modalCancel').addClass('hidden');
}
