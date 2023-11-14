@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('common.Security') }}</h1>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ route('admin.companies.security.update', $company) }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">{{ __('common.Allowed IP Addresses') }}</div>
                    <div class="card-body">

                    @foreach($ipAddresses as $index => $ipAddress)
                    <div class="form-group">
                        <label for="ip_addresses[{{ $index }}]" class="form-label">{{ __('common.IP Address') }} {{ $index + 1 }}</label>
                        <input type="text" class="form-control" id="ip_addresses[{{ $index }}]" name="ip_addresses[]" value="{{ old('ip_addresses.' . $index, $ipAddress) }}">
                        <button type="button" class="btn btn-danger remove-ip">{{ __('common.Delete') }}</button>
                    </div>
                    @endforeach

                    <button type="button" class="btn btn-secondary" id="add-ip">{{ __('common.Add New IP Address') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('add-ip').addEventListener('click', function() {
    const container = document.createElement('div');
    container.classList.add('form-group');

    // ラベルを作成・追加
    const label = document.createElement('label');
    label.innerText = '{{ __('common.IP Address') }}';
    container.appendChild(label);

    // 入力フィールドを作成・追加
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'ip_addresses[]';
    input.classList.add('form-control'); 
    container.appendChild(input); 

    // 削除ボタンを作成・追加
    const removeButton = document.createElement('button');
    removeButton.innerText = '{{ __('common.Delete') }}';
    removeButton.type = 'button';
    removeButton.classList.add('btn', 'btn-danger', 'remove-ip');
    removeButton.addEventListener('click', function() {
        container.remove();
    });
    container.appendChild(removeButton);

    this.before(container);
});

document.querySelectorAll('.remove-ip').forEach(button => {
    button.addEventListener('click', function() {
        const ip_address = this.previousElementSibling.value;
        console.error('ip_address:', ip_address);
        fetch("{{ route('admin.companies.security.delete', $company) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ ip_address: ip_address })
        })
        .then((response) => {
            if (response.ok) {
                button.parentElement.remove();
            } else {
                throw new Error('Error in Ajax');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });
});
</script>
<style>
.form-control {
    margin-bottom: 5px;
}
</style>
@endsection