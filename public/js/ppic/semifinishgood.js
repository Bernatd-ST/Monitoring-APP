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
    $('#btn-add-semifinishgood').click(function() {
        resetForm();
        $('#semiFinishGoodModalLabel').text('Tambah Data Semi Finish Good');
        $('#semiFinishGoodModal').modal('show');
    });
    
    // Initialize DataTable dengan konfigurasi lengkap
    var table = $('#semifinishGoodTable').DataTable({
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
    $('#semifinishGoodTable tbody').on('click', '.btn-edit', function() {
        var data = table.row($(this).closest('tr')).data();
        var id = $(this).closest('tr').attr('data-id');
        editSemiFinishGood(id);
    });
    
    // Handle delete button click
    $('#semifinishGoodTable tbody').on('click', '.btn-delete', function() {
        var id = $(this).closest('tr').attr('data-id');
        $('#deleteSemiFinishGoodId').val(id);
        $('#deleteSemiFinishGoodModal').modal('show');
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
    
    // Save semi finish good data (add/update)
    $('#saveSemiFinishGoodBtn').click(function() {
        saveSemiFinishGood();
    });
    
    // Handle delete confirmation
    $('#confirmDeleteSemiFinishGoodBtn').click(function() {
        var id = $('#deleteSemiFinishGoodId').val();
        if (id) {
            deleteSemiFinishGood(id);
        }
    });
    
    // Reset form fields
    function resetForm() {
        $('#semiFinishGoodId').val('');
        $('#semiFinishGoodForm')[0].reset();
    }
    
    // Edit semi finish good
    function editSemiFinishGood(id) {
        $.ajax({
            url: base_url + '/admin/ppic/get-semifinishgood-detail/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var data = response.data;
                    
                    $('#semiFinishGoodId').val(data.id);
                    $('#criteria').val(data.criteria);
                    $('#period').val(data.period);
                    $('#description').val(data.description);
                    $('#part_no').val(data.part_no);
                    $('#class').val(data.class);
                    $('#begining_bal').val(data.begining_bal);
                    
                    $('#semiFinishGoodModalLabel').text('Edit Data Semi Finish Good');
                    $('#semiFinishGoodModal').modal('show');
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
    
    // Save semi finish good (add/update)
    function saveSemiFinishGood() {
        var id = $('#semiFinishGoodId').val();
        var formData = $('#semiFinishGoodForm').serialize();
        var url = id ? 
            base_url + '/admin/ppic/update-semifinishgood/' + id : 
            base_url + '/admin/ppic/add-semifinishgood';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#saveSemiFinishGoodBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#semiFinishGoodModal').modal('hide');
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
                $('#saveSemiFinishGoodBtn').prop('disabled', false).html('Simpan');
            }
        });
    }
    
    // Delete semi finish good
    function deleteSemiFinishGood(id) {
        $.ajax({
            url: base_url + '/admin/ppic/delete-semifinishgood/' + id,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#confirmDeleteSemiFinishGoodBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteSemiFinishGoodModal').modal('hide');
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
                $('#confirmDeleteSemiFinishGoodBtn').prop('disabled', false).html('Hapus');
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
