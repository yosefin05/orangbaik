<link rel="stylesheet" href="{{ asset('css/alert-error.css') }}">
<link rel="stylesheet" href="{{ asset('css/global.css') }}">
@if ($errors->any())
    <div class="alert-error">
        <div class="alert-error-header">
            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <h4>Data belum dapat disimpan</h4>
                <p>Silakan perbaiki beberapa bagian berikut.</p>
            </div>
        </div>

        <ul class="alert-error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif