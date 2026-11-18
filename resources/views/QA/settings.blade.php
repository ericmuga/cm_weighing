@extends('layouts.QA_master')

@section('styles')
<style>
    .gs-group { border-left: 4px solid #17a2b8; }
    .gs-group .card-header { background: #f8f9fa; }
    .gs-input { max-width: 9rem; }
    .gs-default { font-size: .75rem; }
    .gs-changed { background: #fff8e1; }
</style>
@endsection

@section('content-header')
<div class="container-fluid">
    <h1 class="card-title"> Grading Settings | <small class="text-muted">parameters used by the auto-grading scorecards and weigh-in codes</small></h1>
</div>
<hr>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-light border small mb-3">
        <i class="fas fa-info-circle text-info mr-1"></i>
        Changes apply to grading and weigh-ins saved <strong>after</strong> you save here; records already
        graded keep their result until they are graded again. Every change is logged with your username.
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Not saved.</strong> Please fix the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ route('qa_settings_update') }}" id="gs-form">
        @csrf
        <div class="row">
            @foreach($groups as $group => $fields)
                <div class="col-lg-4 mb-3">
                    <div class="card gs-group h-100 mb-0">
                        <div class="card-header py-2"><strong>{{ $group }}</strong></div>
                        <div class="card-body py-2">
                            @foreach($fields as $key => $f)
                                <div class="form-group mb-2">
                                    <label for="{{ $key }}" class="small mb-1">{{ $f['label'] }}</label>
                                    <div class="input-group input-group-sm gs-input">
                                        <input type="number" step="{{ $f['unit'] === 'pts' ? '1' : '0.1' }}" min="0"
                                            class="form-control gs-field @error($key) is-invalid @enderror"
                                            id="{{ $key }}" name="{{ $key }}"
                                            value="{{ old($key, rtrim(rtrim(number_format($f['value'], 2, '.', ''), '0'), '.')) }}"
                                            data-original="{{ $f['value'] }}" required>
                                        <div class="input-group-append"><span class="input-group-text">{{ $f['unit'] }}</span></div>
                                    </div>
                                    @error($key)
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <span class="text-muted gs-default">Default: {{ $f['default'] }} {{ $f['unit'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-right mb-4">
            <span class="text-muted small mr-2" id="gs-dirty"></span>
            <a href="{{ route('qa_settings') }}" class="btn btn-sm btn-outline-secondary">Discard changes</a>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i> Save settings</button>
        </div>
    </form>

    <div class="card">
        <div class="card-header py-2"><strong>Recent changes</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr><th>When</th><th>Setting</th><th class="text-right">From</th><th class="text-right">To</th><th>By</th></tr>
                </thead>
                <tbody>
                    @forelse($changes as $c)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($c->created_at)->format('d-m-Y H:i') }}</td>
                            <td>{{ \App\Models\GradingSetting::DEFINITIONS[$c->key][1] ?? $c->key }}</td>
                            <td class="text-right">{{ $c->old_value !== null ? (float) $c->old_value : '--' }}</td>
                            <td class="text-right">{{ (float) $c->new_value }}</td>
                            <td>{{ $c->changed_by_username }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No changes yet — all settings are on their defaults.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Highlight edited fields and confirm before saving, since these drive live grading.
    $(function () {
        function refresh() {
            var n = 0;
            $('.gs-field').each(function () {
                var changed = parseFloat(this.value) !== parseFloat($(this).data('original'));
                $(this).toggleClass('gs-changed', changed);
                if (changed) n++;
            });
            $('#gs-dirty').text(n ? n + ' unsaved change(s)' : '');
            return n;
        }
        $('.gs-field').on('input', refresh);
        refresh();
        $('#gs-form').on('submit', function (e) {
            var n = refresh();
            if (n && !confirm('Save ' + n + ' change(s)? Grading saved from now on will use the new values.')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
