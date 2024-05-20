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
     toastr.success('{{ session('success','Done') }}');
</script>

@endif

@if (session('error'))
    <script>
        toastr.error('{{ session('error','Error Occured') }}');
    </script>
@endif