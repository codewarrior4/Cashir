@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif 
@if (session('success'))
<script>
     swal(
        'Done',
        '{{ session('success') }}',
        'success',
    )
</script>

@endif

@if (session('error'))
    <script>
        swal(
            'Error',
            '{{ session('error') }}',
            'error',
        )
    </script>
@endif