$(document).ready(function() {
    var table = $('#dom-jqry').DataTable();

    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        var period = $(this).data('period');
        var url = '/transactions/' + period;
        console.log(period);
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                table.clear().draw(); // Clear the table

                if (data.length > 0) {
                    $.each(data, function(index, transaction) {
                        var statusBadge;
                        if (transaction.status === 'Completed') {
                            statusBadge = '<span class="badge bg-success">Completed</span>';
                        } else if (transaction.status === 'Failed') {
                            statusBadge = '<span class="badge bg-danger">Failed</span>';
                        } else {
                            statusBadge = '<span class="badge bg-warning">Pending</span>';
                        }

                        // Format the created_at date
                        var createdAtFormatted = new Date(transaction.created_at);
                        var formattedDate = `${createdAtFormatted.getDate()} ${createdAtFormatted.toLocaleString('default', { month: 'short' })}, ${createdAtFormatted.getFullYear()} ${createdAtFormatted.getHours()}:${createdAtFormatted.getMinutes().toString().padStart(2, '0')}`;

                        table.row.add([
                            index + 1,
                            transaction.title,
                            transaction.payment_method,
                            statusBadge,
                            '&#8358; ' + parseFloat(transaction.amount).toFixed(2),
                            formattedDate // Display the formatted date
                        ]).draw(false);
                    });
                } else {
                    table.row.add([
                        '',
                        'No transactions found',
                        '',
                        '',
                        '',
                        ''
                    ]).draw(false);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    });
});