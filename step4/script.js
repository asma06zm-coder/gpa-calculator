$(document).ready(function () {

    $('#addCourse').click(function () {
        var row = $('.course-row').first().clone();
        row.find('input').val('');
        row.append(
            '<div class="col-auto">' +
            '<button type="button" class="btn btn-danger remove-row">X</button>' +
            '</div>'
        );
        $('#courses').append(row);
    });

    $(document).on('click', '.remove-row', function () {
        if ($('.course-row').length > 1) {
            $(this).closest('.course-row').remove();
        }
    });

    $('#gpaForm').submit(function (e) {
        e.preventDefault();

        var student  = $('#studentName').val().trim();
        var semester = $('#semester').val().trim();

        if (!student || !semester) {
            $('#result').html('<div class="alert alert-warning">Student name and semester are required.</div>');
            return;
        }

        var valid      = true;
        var usedNames  = [];

        $('[name="course[]"]').each(function () {
            var name = $(this).val().trim().toLowerCase();
            if (name === '') { valid = false; return false; }
            if (usedNames.indexOf(name) !== -1) {
                $('#result').html('<div class="alert alert-danger">Duplicate course name: ' + $(this).val() + '</div>');
                valid = false;
                return false;
            }
            usedNames.push(name);
        });

        $('[name="credits[]"]').each(function () {
            var c = parseFloat($(this).val());
            if (isNaN(c) || c <= 0 || c > 10) { valid = false; return false; }
        });

        if (!valid) {
            if ($('#result').html() === '')
                $('#result').html('<div class="alert alert-warning">Please enter valid values in all fields.</div>');
            return;
        }

        var formData = $(this).serialize();
        formData += '&student=' + encodeURIComponent(student) + '&semester=' + encodeURIComponent(semester);

        $.ajax({
            url: 'calculate.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var gpa       = response.gpa;
                    var alertClass, barClass;

                    if      (gpa >= 3.7) { alertClass = 'alert-success';  barClass = 'bg-success'; }
                    else if (gpa >= 3.0) { alertClass = 'alert-info';     barClass = 'bg-info'; }
                    else if (gpa >= 2.0) { alertClass = 'alert-warning';  barClass = 'bg-warning'; }
                    else                 { alertClass = 'alert-danger';   barClass = 'bg-danger'; }

                    var pct = (gpa / 4) * 100;
                    $('#gpaBar').removeClass('bg-success bg-info bg-warning bg-danger')
                                .addClass(barClass)
                                .css('width', pct + '%')
                                .text(gpa.toFixed(2));
                    $('#progressSection').show();

                    var lastId = response.id || '';
                    var exportBtn = lastId
                        ? '<a href="export.php?id=' + lastId + '" class="btn btn-sm btn-outline-secondary mt-2">Export CSV</a>'
                        : '';

                    $('#result').html(
                        '<div class="alert ' + alertClass + '">' + response.message + '</div>' +
                        response.tableHtml +
                        exportBtn
                    );
                } else {
                    $('#result').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function () {
                $('#result').html('<div class="alert alert-danger">Server error occurred.</div>');
            }
        });
    });

    $('#showHistory').click(function () {
        $('#historyModal').modal('show');
        $.ajax({
            url: 'history.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.history.length > 0) {
                    var html = '<table class="table table-sm table-bordered">';
                    html += '<thead class="thead-dark"><tr><th>#</th><th>Student</th><th>Semester</th><th>GPA</th><th>Date</th><th>Export</th></tr></thead><tbody>';
                    $.each(response.history, function (i, row) {
                        html += '<tr><td>' + row.id + '</td><td>' + row.student + '</td><td>' + row.semester +
                                '</td><td>' + parseFloat(row.gpa).toFixed(2) + '</td><td>' + row.created_at +
                                '</td><td><a href="export.php?id=' + row.id + '" class="btn btn-sm btn-outline-primary">CSV</a></td></tr>';
                    });
                    html += '</tbody></table>';
                    $('#historyBody').html(html);
                } else {
                    $('#historyBody').html('<p>No records found.</p>');
                }
            }
        });
    });
});
