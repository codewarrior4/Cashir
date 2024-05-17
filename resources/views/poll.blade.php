<!-- resources/views/payment/poll.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Checking Payment Status</title>
    <script>
        function checkPaymentStatus() {
            fetch(`{{ route('poll.payment.status') }}?reference={{ $reference }}`)
                .then(response => response.text())
                .then(html => {
                    document.documentElement.innerHTML = html;
                });
        }

        setInterval(checkPaymentStatus, 5000); // Poll every 5 seconds
    </script>
</head>
<body>
    <h1>Checking Payment Status...</h1>
</body>
</html>
