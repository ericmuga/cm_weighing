@extends('layouts.QA_master')

@section('styles')
<style>
    .gs-group { border-left: 4px solid #17a2b8; }
    .gs-group .card-header { background: #f8f9fa; }
    .gs-input { width: 8rem; }
    .gs-rules { border-left: 4px solid #dc3545; }
    .gs-rules .card-header { background: #f8f9fa; }
    .gs-rule-result { font-weight: 600; font-size: .9rem; margin-bottom: .15rem; }
    .gs-rule-result::before { content: "\2192"; color: #dc3545; margin-right: .35rem; }
    .gs-table td, .gs-table th { padding-left: .75rem; padding-right: .75rem; }
    .gs-table thead th { border-top: 0; font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; }
    .gs-table .gs-input { width: 7rem; }
    .gs-default { font-size: .75rem; }
    .gs-changed { background: #fff8e1; }
</style>
@endsection

@section('content-header')
<div class="container-fluid">
    <h1 class="card-title"> {{ $title }} | <small class="text-muted">parameters used by the auto-grading scorecard and weigh-in codes</small></h1>
</div>
<hr>
@endsection

@section('content')
<div class="container-fluid">
    <ul class="nav nav-tabs mb-3">
        @foreach(\App\Models\GradingSetting::PAGES as $tabPage => $tabLabel)
            <li class="nav-item">
                <a class="nav-link {{ $tabPage === $page ? 'active' : '' }}" href="{{ route('qa_settings', $tabPage) }}">{{ $tabLabel }}</a>
            </li>
        @endforeach
    </ul>

    <div class="alert alert-light border small mb-3">
        <i class="fas fa-info-circle text-info mr-1"></i>
        Changes apply to grading and weigh-ins saved <strong>after</strong> you save here; records already
        graded keep their result until they are graded again. Every change is logged with your username.
        @if($page === 'beef')
            <br><i class="fas fa-tag text-info mr-1"></i>
            Weigh-in codes keep their names (PG+200, HG+170, FAQ+150, STD+120, COMM) even if the kg values change.
        @endif
    </div>

    {{-- Hard rules override the score; listed so editors know what the numbers below can't change. --}}
    <div class="card gs-rules mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-gavel mr-1"></i> Hard rules</strong>
            <span class="text-muted small">Applied before the score, worst first · fixed in the scorecard, not editable here</span>
        </div>
        <div class="card-body py-2">
            <div class="row">
                @foreach($hardRules as $rule)
                    <div class="col-md-6 col-lg mb-2">
                        <div class="gs-rule-result">{{ $rule['result'] }}</div>
                        <ul class="small text-muted pl-3 mb-0">
                            @foreach($rule['when'] as $condition)
                                <li>{{ $condition }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Not saved.</strong> Please fix the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ route('qa_settings_update', $page) }}" id="gs-form">
        @csrf
        @php
            // Fill the row whatever the number of groups (beef 2 -> halves, lamb 3 -> thirds).
            $colClass = 'col-lg-' . intdiv(12, max(1, min(3, count($groups))));
        @endphp
        <div class="row">
            @foreach($groups as $group => $fields)
                @php
                    // Score-band groups are lowest/highest pairs: show them as one row per grade.
                    $isBands = collect(array_keys($fields))->every(fn ($k) => str_ends_with($k, '_min') || str_ends_with($k, '_max'));
                    $bandRows = $isBands
                        ? collect($fields)->groupBy(fn ($f, $k) => substr($k, 0, -4), true)
                        : collect();
                @endphp
                <div class="{{ $colClass }} col-md-6 mb-3">
                    <div class="card gs-group h-100 mb-0">
                        <div class="card-header py-2"><strong>{{ $group }}</strong></div>
                        @if($isBands)
                            <div class="card-body p-0">
                                <table class="table table-sm gs-table mb-0">
                                    <thead>
                                        <tr><th>Grade</th><th>Lowest</th><th>Highest</th><th class="text-muted font-weight-normal">Default</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bandRows as $base => $pair)
                                            @php $min = $pair[$base . '_min']; $max = $pair[$base . '_max']; @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ \Illuminate\Support\Str::before($min['label'], ' —') }}</td>
                                                <td>@include('QA.partials.setting-input', ['key' => $base . '_min', 'f' => $min])</td>
                                                <td>@include('QA.partials.setting-input', ['key' => $base . '_max', 'f' => $max])</td>
                                                <td class="align-middle text-muted gs-default text-nowrap">{{ $min['default'] }} – {{ $max['default'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="card-body py-1">
                                @foreach($fields as $key => $f)
                                    <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="pr-3">
                                            <label for="{{ $key }}" class="small font-weight-bold mb-0 d-block">{{ $f['label'] }}</label>
                                            <span class="text-muted gs-default">Default: {{ $f['default'] }} {{ $f['unit'] }}</span>
                                        </div>
                                        <div class="flex-shrink-0">@include('QA.partials.setting-input', ['key' => $key, 'f' => $f])</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-right mb-4">
            <span class="text-muted small mr-2" id="gs-dirty"></span>
            <a href="{{ route('qa_settings', $page) }}" class="btn btn-sm btn-outline-secondary">Discard changes</a>
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
