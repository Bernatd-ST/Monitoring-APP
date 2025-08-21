// Mendefinisikan base_url untuk digunakan dalam AJAX requests
var base_url = window.location.origin;

$(document).ready(function() {
    // Toggle filter section dengan animasi yang lebih smooth
    $('#toggle-filters').click(function() {
        $('#filter-section').slideToggle(150, 'linear');
    });

    // inisialisasi select2
    $('#criteriaFilter').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih Criteria',
        allowClear: true,
        dropdownParent: $('#filter-section')
    });
    
    $('#partNoFilter').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih Part No',
        allowClear: true,
        dropdownParent: $('#filter-section')
    });
    
    $('#classFilter').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih Class',
        allowClear: true,
        dropdownParent: $('#filter-section')
    });
    
    // Tambah data baru
    $('#btn-add-finishgood').click(function() {
        resetForm();
        $('#finishGoodModalLabel').text('Tambah Data Finish Good');
        $('#finishGoodModal').modal('show');
    });
    
    // Initialize DataTable dengan konfigurasi yang lengkap
    var table = $('#finishGoodTable').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        language: {
            url: '/plugins/datatables/i18n/id.json'
        },
        columnDefs: [
            {
                targets: -1,
                data: null,
                defaultContent: '<div class="btn-group btn-group-sm" role="group">' +
                               '<button class="btn btn-warning btn-edit"><i class="fas fa-edit"></i></button>' +
                               '<button class="btn btn-danger btn-delete"><i class="fas fa-trash"></i></button>' +
                               '</div>',
                orderable: false
            }
        ]
    });
    
    // Handle edit button click
    $('#finishGoodTable tbody').on('click', '.btn-edit', function() {
        var id = $(this).closest('tr').attr('data-id');
        editFinishGood(id);
    });
    
    // Handle delete button click
    $('#finishGoodTable tbody').on('click', '.btn-delete', function() {
        var id = $(this).closest('tr').attr('data-id');
        $('#deleteFinishGoodId').val(id);
        $('#deleteFinishGoodModal').modal('show');
    });
    
    // Show Upload Modal
    $('#uploadExcelBtn').click(function() {
        $('#uploadExcelModal').modal('show');
    });
    
    // Submit Excel File
    $('#submitExcelBtn').click(function() {
        var formData = new FormData($('#uploadForm')[0]);
        var fileInput = $('#excelFile')[0];
        
        if (fileInput.files.length === 0) {
            alert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }
        
        $.ajax({
            url: $('#uploadForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#submitExcelBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#uploadExcelModal').modal('hide');
                    // Show success message using toastr if available, otherwise use alert
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Terjadi kesalahan saat upload file. Silakan coba lagi.');
                } else {
                    alert('Terjadi kesalahan saat upload file. Silakan coba lagi.');
                }
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#submitExcelBtn').prop('disabled', false).html('Upload');
            }
        });
    });
    
    // Filter functionality
    $('#applyFilterBtn').click(function() {
        var criteriaFilter = $('#criteriaFilter').val();
        var periodFilter = $('#periodFilter').val();
        var partNoFilter = $('#partNoFilter').val();
        var classFilter = $('#classFilter').val();
        
        // Refresh the datatable and apply filters
        table.column(1).search(criteriaFilter).draw();
        table.column(4).search(partNoFilter).draw();
        table.column(5).search(classFilter).draw();
        
        if (periodFilter) {
            // Assuming period column uses a date format
            var month = new Date(periodFilter).getMonth() + 1;
            var year = new Date(periodFilter).getFullYear();
            var searchStr = year + '-' + (month < 10 ? '0' + month : month);
            table.column(2).search(searchStr).draw();
        } else {
            table.column(2).search('').draw();
        }
    });
    
    // Reset filter
    $('#resetFilterBtn').click(function() {
        $('#criteriaFilter').val('');
        $('#periodFilter').val('');
        $('#partNoFilter').val('');
        $('#classFilter').val('');
        table.search('').columns().search('').draw();
    });
    
    // Save finish good data (add/update)
    $('#saveFinishGoodBtn').click(function() {
        saveFinishGood();
    });
    
    // Handle delete confirmation
    $('#confirmDeleteFinishGoodBtn').click(function() {
        var id = $('#deleteFinishGoodId').val();
        if (id) {
            deleteFinishGood(id);
        }
    });
    
    // Reset form fields
    function resetForm() {
        $('#finishGoodId').val('');
        $('#finishGoodForm')[0].reset();
    }
    
    // Edit finish good
    function editFinishGood(id) {
        $.ajax({
            url: base_url + '/admin/ppic/get-finishgood-detail/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var data = response.data;
                    
                    $('#finishGoodId').val(data.id);
                    $('#criteria').val(data.criteria);
                    $('#period').val(data.period);
                    $('#description').val(data.description);
                    $('#part_no').val(data.part_no);
                    $('#class').val(data.class);
                    $('#end_bal').val(data.end_bal);
                    
                    $('#finishGoodModalLabel').text('Edit Data Finish Good');
                    $('#finishGoodModal').modal('show');
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('error', 'Terjadi kesalahan saat mengambil data');
                console.error(xhr.responseText);
            }
        });
    }
    
    // Save finish good (add/update)
    function saveFinishGood() {
        var id = $('#finishGoodId').val();
        var formData = $('#finishGoodForm').serialize();
        var url = id ? 
            base_url + '/admin/ppic/update-finishgood/' + id : 
            base_url + '/admin/ppic/add-finishgood';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#saveFinishGoodBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#finishGoodModal').modal('hide');
                    showAlert('success', response.message);
                    // Reload page to refresh data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('error', 'Terjadi kesalahan saat menyimpan data');
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#saveFinishGoodBtn').prop('disabled', false).html('Simpan');
            }
        });
    }
    
    // Delete finish good
    function deleteFinishGood(id) {
        $.ajax({
            url: base_url + '/admin/ppic/delete-finishgood/' + id,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#confirmDeleteFinishGoodBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteFinishGoodModal').modal('hide');
                    showAlert('success', response.message);
                    // Reload page to refresh data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('error', 'Terjadi kesalahan saat menghapus data');
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#confirmDeleteFinishGoodBtn').prop('disabled', false).html('Hapus');
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
});
