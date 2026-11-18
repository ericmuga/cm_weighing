{{-- One grading setting input. Expects $key and $f (value, default, unit, label). --}}
<div class="input-group input-group-sm gs-input">
    <input type="number" step="{{ $f['unit'] === 'pts' ? '1' : '0.1' }}" min="0"
        class="form-control text-right gs-field @error($key) is-invalid @enderror"
        id="{{ $key }}" name="{{ $key }}"
        value="{{ old($key, rtrim(rtrim(number_format($f['value'], 2, '.', ''), '0'), '.')) }}"
        data-original="{{ $f['value'] }}" aria-label="{{ $f['label'] }}"
        title="Default: {{ $f['default'] }} {{ $f['unit'] }}" required>
    <div class="input-group-append"><span class="input-group-text">{{ $f['unit'] }}</span></div>
</div>
@error($key)
    <div class="text-danger small">{{ $message }}</div>
@enderror
