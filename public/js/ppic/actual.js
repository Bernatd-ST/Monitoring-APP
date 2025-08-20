// base_url didefinisikan di layout.php
// Fallback jika base_url tidak didefinisikan
if (typeof base_url === 'undefined') {
    console.warn('base_url tidak didefinisikan di layout.php, menggunakan fallback');
    var base_url = window.location.origin;
}
console.log('Actual.js menggunakan base_url:', base_url);

// Definisikan table sebagai variabel global
var table;

$(document).ready(function() {
    console.log('DEBUG: Document ready event fired');
    console.log('DEBUG: Variabel table dalam document ready:', typeof table, table);
    
    // Toggle filter section dengan animasi yang lebih smooth
    $('#toggle-filters').click(function() {
        $('#filter-section').slideToggle(150, 'linear');
    });
    
    // Tambah data baru
    $('#btn-add-actual').click(function() {
        resetForm();
        $('#actualModalLabel').text('Tambah Data Actual Production');
        $('#actualModal').modal('show');
    });
    // Hitung jumlah kolom di thead
    var headerColumnCount = $('#actual-table thead tr:first th').length;
    console.log('Header column count:', headerColumnCount);
    
    // Hapus instance DataTable yang mungkin sudah ada
    if ($.fn.DataTable.isDataTable('#actual-table')) {
        $('#actual-table').DataTable().destroy();
    }
    
    console.log('DEBUG: Sebelum inisialisasi DataTable');
    console.log('DEBUG: Variabel table sebelum inisialisasi:', typeof table);

    // Inisialisasi DataTable dengan konfigurasi yang lebih sederhana
    table = $('#actual-table').DataTable({
            // Nonaktifkan fitur yang mungkin menyebabkan masalah column count
            processing: true,
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            ordering: true,
            searching: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            // Definisi kolom yang sangat sederhana
            columns: [
                null, // Model No
                null, // Class
                null, null, null, null, null, null, null, null, null, null, // Hari 1-10
                null, null, null, null, null, null, null, null, null, null, // Hari 11-20
                null, null, null, null, null, null, null, null, null, null, null, // Hari 21-31
                null, // Total
                { orderable: false } // Aksi
            ]
        });
    });
    
    // Handle edit button click
    $('#actual-table tbody').on('click', '.edit-actual', function() {
        var id = $(this).data('id');
        editActual(id);
    });
    
    // Handle view detail button click
    $('#actual-table tbody').on('click', '.view-actual', function() {
        var id = $(this).data('id');
        viewActualDetail(id);
    });
    
    // Handle delete button click
    $('#actual-table tbody').on('click', '.delete-actual', function() {
        var id = $(this).data('id');
        var model = $(this).closest('tr').find('td:first').text();
        var cls = $(this).closest('tr').find('td:nth-child(2)').text();
        
        $('#delete-model-no').text(model);
        $('#delete-class').text(cls);
        $('#delete_actual_id').val(id);
        $('#deleteActualModal').modal('show');
    });
    
    // Handle form import Excel
    $('#formImportActual').on('submit', function(e) {
        e.preventDefault();
        
        // Debug
        console.log('Form import Excel submit triggered');
        
        // Validasi file input
        var fileInput = $('#excelFile')[0];
        if (fileInput.files.length === 0) {
            toastr.error('Pilih file Excel terlebih dahulu');
            return false;
        }
        
        // Debug info file
        console.log('File selected:', fileInput.files[0].name, 'size:', fileInput.files[0].size);
        
        // Create FormData object langsung dari form
        var formData = new FormData(this);
        
        // Pastikan file ada di formData
        console.log('Checking if file exists in form:', formData.has('excelFile'));
        
        // Jika tidak ada, tambahkan secara manual
        if (!formData.has('excelFile')) {
            formData.append('excelFile', fileInput.files[0]);
            console.log('File ditambahkan secara manual');
        }
        
        // Debugging formData
        for (var pair of formData.entries()) {
            console.log('FormData entry:', pair[0], pair[1]);
        }
        
        // Pastikan base_url tersedia dan konsisten
        if (typeof base_url === 'undefined') {
            console.warn('base_url tidak didefinisikan, menggunakan default');
            var base_url = window.location.origin;
        }
        console.log('Base URL yang digunakan:', base_url);
        
        console.log('Using URL:', base_url + '/admin/ppic/import-actual');
        
        // Kirim request AJAX
        $.ajax({
            url: base_url + '/admin/ppic/import-actual',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function() {
                $('#btnImportActual').prop('disabled', true).html('<i class="fa fa-spin fa-spinner"></i> Processing...');
            },
            success: function(response) {
                console.log('Success response:', response);
                try {
                    // Pastikan response adalah objek JSON
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.status === 'success') {
                        // Tutup modal dan reset form
                        toastr.success(response.message);
                        $('#importActualModal').modal('hide');
                        $('#formImportActual')[0].reset();
                        
                        // Tunda reload untuk memastikan modal selesai menutup dan toastr ditampilkan
                        setTimeout(function() {
                            // Reload halaman - cara paling pasti untuk memperbarui data
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Terjadi kesalahan saat import data');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    toastr.error('Terjadi kesalahan pada format respons server');
                    setTimeout(function() {
                        window.location.reload(); // Tetap reload jika data berhasil masuk ke database
                    }, 1500);
                }
            },
            error: function() {
                showAlert('error', 'Terjadi kesalahan saat menghubungi server.');
            }
        });
    });

    // --- FUNGSI FILTER --- 
    // Filter handling
    $('#apply-filter').click(function() {
        console.log('DEBUG: Tombol apply-filter diklik');
        console.log('DEBUG: Variabel table sebelum memanggil applyCustomFilters:', typeof table, table);
        applyCustomFilters();
    });

    $('#reset-filter').click(function() {
        $('#filter-model').val('').trigger('change');
        $('#filter-class').val('').trigger('change');
        $('#filter-daterange').val('');
        $('#filter-date-start').val('');
        $('#filter-date-end').val('');
        // Hapus custom filter dan gambar ulang tabel
        if($.fn.dataTable.ext.search.length > 0){
            $.fn.dataTable.ext.search.pop();
        }
        table.search('').columns().search('').draw();
    });

    // Inisialisasi daterangepicker
    $('#filter-daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'D',
            cancelLabel: 'Batal',
            applyLabel: 'Terapkan',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        },
        opens: 'right',
    });

    $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val('Tanggal ' + picker.startDate.format('D') + ' - ' + picker.endDate.format('D'));
        $('#filter-date-start').val(picker.startDate.format('D'));
        $('#filter-date-end').val(picker.endDate.format('D'));
    });

    $('#filter-daterange').on('cancel.daterangepicker', function() {
        $(this).val('');
        $('#filter-date-start').val('');
        $('#filter-date-end').val('');
    });

    function applyCustomFilters() {
        console.log('Applying custom filters...');
        console.log('DEBUG: Variabel table dalam applyCustomFilters:', typeof table, table);
        var modelFilter = $('#filter-model').val();
        var classFilter = $('#filter-class').val();
        var dateStart = $('#filter-date-start').val();
        var dateEnd = $('#filter-date-end').val();

        console.log('Filter values:', { modelFilter, classFilter, dateStart, dateEnd });

        // Hapus filter sebelumnya jika ada
        if($.fn.dataTable.ext.search.length > 0){
            $.fn.dataTable.ext.search.pop();
        }
        
        // Periksa apakah table terdefinisi
        if (typeof table === 'undefined' || table === null) {
            console.error('ERROR: Variabel table tidak terdefinisi atau null!');
            return;
        }
        
        // Reset semua filter terlebih dahulu
        table.search('').columns().search('').draw();

        // Terapkan filter model & class
        if (modelFilter) {
            console.log('Applying model filter:', modelFilter);
            table.column(0).search('^' + $.fn.dataTable.util.escapeRegex(modelFilter) + '$', true, false);
        }
        
        if (classFilter) {
            console.log('Applying class filter:', classFilter);
            table.column(1).search('^' + $.fn.dataTable.util.escapeRegex(classFilter) + '$', true, false);
        }

        // Terapkan filter rentang tanggal
        if (dateStart && dateEnd) {
            console.log('Applying date range filter:', dateStart, 'to', dateEnd);
            var startDay = parseInt(dateStart);
            var endDay = parseInt(dateEnd);

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'actual-table') return true;
                for (var i = startDay; i <= endDay; i++) {
                    var colIndex = i + 1; // Kolom tanggal mulai dari indeks 2
                    var dayValue = parseFloat(data[colIndex]) || 0;
                    if (dayValue > 0) return true;
                }
                return false;
            });
        }
        
        // Penting: Panggil draw() untuk menerapkan filter
        console.log('Drawing table with filters applied');
        table.draw();
    }

    // --- FUNGSI FORM & CRUD --- 
    function resetForm() {
        $('#actual_id').val('');
        $('#actualForm')[0].reset();
        for (var i = 1; i <= 31; i++) {
            $('#day_' + i).val('0');
        }
        $('#total').val('0');
    }

    function calculateTotal() {
        var total = 0;
        for (var i = 1; i <= 31; i++) {
            var val = parseInt($('#day_' + i).val()) || 0;
            total += val;
        }
        $('#total').val(total);
    }

    for (var i = 1; i <= 31; i++) {
        $('#day_' + i).on('input', calculateTotal);
    }

    function editActual(id) {
        console.log('editActual called with ID:', id);
        $.ajax({
            url: base_url + '/admin/ppic/get-actual/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Edit response:', response);
                if (response && response.status === 'success' && response.data) {
                    var data = response.data;
                    resetForm();
                    $('#actual_id').val(data.id);
                    $('#model_no').val(data.model_no);
                    $('#class').val(data.class);
                    
                    // Isi nilai untuk setiap hari
                    for (var i = 1; i <= 31; i++) {
                        $('#day_' + i).val(data['day_' + i] || '0');
                    }
                    
                    calculateTotal();
                    $('#actualModalLabel').text('Edit Data Actual Production');
                    $('#actualModal').modal('show');
                } else if (response) {
                    // Jika response ada tapi tidak dalam format yang diharapkan
                    resetForm();
                    $('#actual_id').val(response.id);
                    $('#model_no').val(response.model_no);
                    $('#class').val(response.class);
                    
                    // Isi nilai untuk setiap hari
                    for (var i = 1; i <= 31; i++) {
                        $('#day_' + i).val(response['day_' + i] || '0');
                    }
                    
                    calculateTotal();
                    $('#actualModalLabel').text('Edit Data Actual Production');
                    $('#actualModal').modal('show');
                } else {
                    showAlert('error', 'Data tidak ditemukan.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
                showAlert('error', 'Gagal mengambil data untuk diedit.');
            }
        });
    }

    function viewActualDetail(id) {
        console.log('viewActualDetail called with ID:', id);
        $.ajax({
            url: base_url + '/admin/ppic/get-actual/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                var data;
                
                // Handle different response formats
                if (response && response.status === 'success' && response.data) {
                    data = response.data;
                } else if (response) {
                    data = response;
                } else {
                    showAlert('error', 'Format data tidak valid');
                    return;
                }
                
                // Isi detail data
                $('#actualDetailModal #detail-model-no').text(data.model_no || '-');
                $('#actualDetailModal #detail-class').text(data.class || '-');
                
                // Hitung total dan isi nilai total
                var total = 0;
                for (var i = 1; i <= 31; i++) {
                    var dayValue = parseFloat(data['day_' + i] || 0);
                    total += dayValue;
                }
                $('#actualDetailModal #detail-total').text(total.toFixed(1));
                
                // Isi data harian ke tabel
                for (var i = 1; i <= 31; i++) {
                    $('#actualDetailModal #detail-day-' + i).text(data['day_' + i] || '0');
                }
                
                // Siapkan data untuk chart
                var labels = [];
                var dayValues = [];
                
                for (var i = 1; i <= 31; i++) {
                    var value = parseFloat(data['day_' + i] || 0);
                    if (value > 0) {
                        dayValues.push(value);
                        labels.push('Hari ' + i);
                    }
                }
                
                // Buat grafik distribusi actual harian
                createActualChart(labels, dayValues);
                
                // Tampilkan modal
                $('#actualDetailModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
                showAlert('error', 'Terjadi kesalahan saat mengambil detail data.');
            }
        });
    }
    
    // Fungsi untuk membuat chart actual production
    function createActualChart(labels, values) {
        // Jika sudah ada chart sebelumnya, hancurkan dulu
        if (window.actualChart instanceof Chart) {
            window.actualChart.destroy();
        }
        
        var ctx = document.getElementById('actualChart').getContext('2d');
        window.actualChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Actual Production',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    $('#saveActual').click(function() {
        var id = $('#actual_id').val();
        var model_no = $('#model_no').val();
        var classVal = $('#class').val();
        
        console.log('Save Actual - ID:', id);
        console.log('Model No:', model_no);
        console.log('Class:', classVal);
        
        // Validasi form sebelum submit
        if (!model_no || model_no.trim() === '') {
            showAlert('error', 'Model No tidak boleh kosong');
            $('#model_no').focus();
            return false;
        }
        
        if (!classVal || classVal.trim() === '') {
            showAlert('error', 'Class tidak boleh kosong');
            $('#class').focus();
            return false;
        }
        
        var formData = $('#actualForm').serialize();
        console.log('Form Data:', formData);
        
        var url = id ? 
            base_url + '/admin/ppic/update-actual/' + id :
            base_url + '/admin/ppic/add-actual';
            
        console.log('Request URL:', url);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#saveActual').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                console.log('Success Response:', response);
                if (response.status === 'success') {
                    $('#actualModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Terjadi kesalahan saat menyimpan data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                try {
                    var responseObj = JSON.parse(xhr.responseText);
                    if (responseObj && responseObj.message) {
                        errorMessage = responseObj.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                showAlert('error', errorMessage);
            },
            complete: function() {
                // Re-enable tombol save
                $('#saveActual').prop('disabled', false).html('Simpan');
            }
        });
    });
    
    // Import Excel button click event
    $('#btn-import-excel').click(function() {
        $('#importExcelModal').modal('show');
    });
    
    // Handle delete confirmation button
    $('#confirmDelete').click(function() {
        var id = $('#delete_actual_id').val();
        console.log('Deleting actual with ID:', id);
        if (id) {
            deleteActual(id);
        } else {
            showAlert('error', 'ID data tidak valid');
        }
    });
    
    // Form import Excel submit event
    $('#form-import-excel').submit(function(e) {
        e.preventDefault();
        
        // Validasi file input
        var fileInput = $('#excelFile')[0];
        if (fileInput.files.length === 0) {
            toastr.error('Silakan pilih file Excel terlebih dahulu.');
            return;
        }
        
        // Validasi ekstensi file
        var fileName = fileInput.files[0].name;
        var fileExt = fileName.split('.').pop().toLowerCase();
        if (fileExt !== 'xlsx' && fileExt !== 'xls') {
            toastr.error('Format file harus Excel (.xlsx atau .xls)');
            return;
        }
        
        // Disable submit button dan tampilkan loading spinner
        $('#btn-submit-import').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengimport...');
        
        // Ambil form data
        var formData = new FormData(this);
        
        // Kirim request AJAX
        $.ajax({
            url: base_url + '/admin/ppic/import-actual',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Enable kembali tombol submit
                $('#btn-submit-import').prop('disabled', false).html('Import');
                
                if (response.status === 'success') {
                    // Tutup modal
                    $('#importExcelModal').modal('hide');
                    
                    // Reset form
                    $('#form-import-excel')[0].reset();
                    
                    // Tampilkan pesan sukses
                    toastr.success(response.message);
                    
                    // Reload halaman setelah 1 detik
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Tampilkan pesan error
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                // Enable kembali tombol submit
                $('#btn-submit-import').prop('disabled', false).html('Import');
                
                // Parse response jika dalam format JSON
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        toastr.error(response.message);
                    } else {
                        toastr.error('Terjadi kesalahan saat mengimport data. Silakan coba lagi.');
                    }
                } catch (e) {
                    // Tampilkan pesan error
                    toastr.error('Terjadi kesalahan saat mengimport data. Silakan coba lagi.');
                }
                console.error(xhr.responseText);
            }
        });
    });

    // Delete actual production
    function deleteActual(id) {
        console.log('Executing deleteActual with ID:', id);
        console.log('Using URL:', base_url + '/admin/ppic/delete-actual/' + id);
        
        $.ajax({
            url: base_url + '/admin/ppic/delete-actual/' + id,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                console.log('Delete request sending...');
                $('#confirmDelete').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...');
            },
            success: function(response) {
                console.log('Delete response received:', response);
                if (response.status === 'success') {
                    $('#deleteActualModal').modal('hide');
                    showAlert('success', response.message);
                    // Reload page to refresh data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Gagal menghapus data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                var errorMessage = 'Terjadi kesalahan saat menghapus data.';
                try {
                    var responseObj = JSON.parse(xhr.responseText);
                    if (responseObj && responseObj.message) {
                        errorMessage = responseObj.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                showAlert('error', errorMessage);
            },
            complete: function() {
                console.log('Delete request completed');
                $('#confirmDelete').prop('disabled', false).html('Hapus');
            }
        });
    }
    
    // Helper function to show alerts
    function showAlert(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    }

