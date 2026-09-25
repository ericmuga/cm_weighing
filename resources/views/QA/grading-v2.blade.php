@extends('layouts.QA_master')

@section('content')
<div class="container-fluid">
    <div class="card">
            <div class="card-header row align-items-center">
                <div class="col-lg-8">
                    <h1 class="card-title">Grading Work Sheet V2 | <span id="subtext-h1-title"><small> showing
                                <strong>Today's</strong>
                                entries</small></span></h1>
                </div>
                <div class="col-lg-4 text-right">
                    <button type="button" class="btn btn-outline-success btn-sm mr-1" id="btn-qa-report">
                        <i class="fas fa-file-excel"></i> QA Report
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-slaughter-report">
                        <i class="fas fa-chart-bar"></i> Summary Report
                    </button>
                    <br>
                    <span class="text-danger" id="err"></span>
                    <span class="text-success" id="succ"></span>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                @if(isset($unmatched_weighins) && $unmatched_weighins->isNotEmpty())
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> {{ $unmatched_weighins->count() }} carcass(es) weighed today have no matching grading record.</strong>
                        This usually means more animals were weighed against a receipt than its declared quantity — check the receipt's received_qty. These carcasses are weighed but cannot be graded until fixed.
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead>
                                    <tr>
                                        <th>Receipt No.</th>
                                        <th>Agg No</th>
                                        <th>Item Code</th>
                                        <th>Vendor</th>
                                        <th>Settlement Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unmatched_weighins as $row)
                                        <tr>
                                            <td>{{ $row->receipt_no }}</td>
                                            <td>{{ $row->agg_no }}</td>
                                            <td>{{ $row->item_code }}</td>
                                            <td>{{ $row->vendor_no }} — {{ $row->vendor_name }}</td>
                                            <td>{{ $row->settlement_weight !== null ? number_format($row->settlement_weight, 2) : '--' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped " width="100%">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Settlement</th>
                                <th>Weight Classification</th>
                                <th>Grading Status</th>
                                <th>QA Classification</th>
                                <th>Auto Suggestion</th>
                                <th>Downgraded?</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Sno</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Settlement</th>
                                <th>Weight Classification</th>
                                <th>Grading Status</th>
                                <th>QA Classification</th>
                                <th>Auto Suggestion</th>
                                <th>Downgraded?</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($grading_data as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->agg_no }}</td>
                                    <td>{{ $data->receipt_no }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->vendor_no }}</td>
                                    <td>{{ number_format($data->settlement_weight, 2) }}</td>                                  

                                    <td>{{ $data->weigh_classification_code ?? '--' }}</td>

                                    @php
    $tdAttrs = 'class="gradingShow"'
        .' data-agg_no="'.$data->agg_no.'"'
        .' data-item_code="'.$data->item_code.'"'
        .' data-id="'.$data->id.'"'
        .' data-settlement_weight="'.$data->settlement_weight.'"'
        .' data-item_name="'.$data->description.'"'
        .' data-vendor="'.$data->vendor_no.'"'
        .' data-classification="'.($data->classification ?? '').'"'
        .' data-dentition="'.($data->dentition ?? '').'"'
        .' data-fat_cover="'.($data->fat_cover ?? '').'"'
        .' data-fat_color="'.($data->fat_color ?? '').'"'
        .' data-meat_color="'.($data->meat_color ?? '').'"'
        .' data-bruising="'.($data->bruising ?? '').'"'
        .' data-muscle="'.($data->muscle_conformation ?? '').'"'
        .' data-narration="'.e($data->narration ?? '').'"';
@endphp

                                    @if($data->classification == null && !$data->settlement_weight)
                                        <td {!! $tdAttrs !!}><a href="#" class="text-warning">
                                            <i class="fas fa-exclamation-circle"></i> QA &amp; Wght pending <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @elseif($data->classification == null)
                                        <td {!! $tdAttrs !!}><a href="#" class="text-info">
                                            <i class="fas fa-clipboard-check"></i> QA pending <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @else
                                        <td {!! $tdAttrs !!}><a href="#" class="text-success">
                                            <i class="fas fa-check-circle"></i> graded <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @endif

                                    @if($data->classification == 1)
                                        <td>Premium</td>
                                    @elseif($data->classification == 2)
                                        <td>High Grade</td>
                                    @elseif($data->classification == 3)
                                        <td>Commercial</td>
                                    @elseif($data->classification == 4)
                                        <td>Poor C</td>
                                    @elseif($data->classification == 5)
                                        <td>1st grade</td>
                                    @elseif($data->classification == 6)
                                        <td>2nd grade</td>
                                    @elseif($data->classification == 7)
                                        <td>Class R</td>
                                    @elseif($data->classification == 8)
                                        <td>FAQ</td>
                                    @elseif($data->classification == 9)
                                        <td>Standard</td>
                                    @else
                                        <td>---</td>
                                    @endif

                                    <td class="auto-suggestion-cell" data-id="{{ $data->id }}">
                                        @if($data->auto_classification ?? null)
                                            @if(($data->auto_classification) === 'Condemned')
                                                <span class="text-danger font-weight-bold">{{ $data->auto_classification }}</span>
                                            @else
                                                {{ $data->auto_classification }}
                                            @endif
                                            @if($data->awaiting_weight ?? false)
                                                <span class="badge badge-info" title="Provisional &mdash; settlement weight isn't recorded yet; weight can only lower this grade, never raise it">awaiting weight</span>
                                            @elseif($data->is_indeterminate ?? false)
                                                <span class="badge badge-warning" title="Could not be auto-graded with confidence &mdash; please review">flagged</span>
                                            @endif
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>

                                    @if($data->is_downgraded === null)
                                        <td class="downgraded-cell"><span class="badge badge-secondary">--</span></td>
                                    @elseif($data->is_downgraded == 0)
                                        <td class="downgraded-cell"><span class="badge badge-success">No</span></td>
                                    @else
                                        <td class="downgraded-cell"><span class="badge badge-danger">Yes</span></td>
                                    @endif

                                    <td>{{ $helpers->shortDateTime($data->updated_at) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>

<div id="gradingShow" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-grading-slaughter" class="form-prevent-multiple-submits"
                action="{{ route('qa_update_grading_v2') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Grading For Carcass No: <strong><input
                                style="border:none" type="text" id="agg_no" name="agg_no" value="" readonly></strong>
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row text-center form-group">
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Vendor Number</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="vendor_no"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Item Code</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_code"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Item Description</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_name"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Settlement Weight</label>
                            <input type="text" style="text-align: center" class="form-control" id="settlement_weight"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <div class="row text-center form-group">
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Dentition</label>
                            <select class="form-control select2 params" name="dentition" id="dentition">
                                <option value="" selected> Select an option </option>
                                <option value="1">Full mouth </option>
                                <option value="2">3 pairs </option>
                                <option value="3">2 pairs </option>
                                <option value="4">1 pair </option>
                                <option value="5">Milk Teeth </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Cover</label>
                            <select class="form-control select2 params" name="fat_cover" id="fat_cover">
                                <option disabled selected> select an option </option>
                                <option value="4">Marbling</option>
                                <option value="1">Good </option>
                                <option value="2">Fair </option>
                                <option value="3">inadequate </option>
                                <option value="0">None </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Color</label>
                            <select class="form-control select2 params" name="fat_color" id="fat_color">
                                <option disabled selected> select an option </option>
                                <option value="1">Cream white </option>
                                <option value="3">Light yellow </option>
                                <option value="2">Deep yellow </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Meat Color</label>
                            <select class="form-control select2 params" name="meat_color" id="meat_color">
                                <option disabled selected> select an option </option>
                                <option value="1">Bright red </option>
                                <option value="2">Dark </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Bruising</label>
                            <select class="form-control select2 params" name="bruising" id="bruising">
                                <option disabled selected> select an option </option>
                                <option value="0">No Bruises </option>
                                <option value="1">Mild </option>
                                <option value="3">Severe </option>
                                <option value="4">Detained</option>
                                <option value="5">Condemned</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Muscles</label>
                            <select class="form-control select2 params" name="muscle" id="muscle">
                                <option disabled selected> select an option </option>
                                <option value="1">Well finished </option>
                                <option value="2">Fairly conformed </option>
                                <option value="3">Poorly conformed </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Classification</label>
                        <select class="form-control select2 params" name="fat_group" id="fat_group" required>
                            
                        </select>
                        <small id="autoGradeHint" class="form-text text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputIdNumber">Narration (<em>optional</em>) </label>
                        <input type="text" class="form-control params" id="narration" name="narration" value="">
                    </div>
                    <input type="hidden" id="item_id" name="item_id" value="">
                </div>{{-- /modal-body --}}
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning btn-lg btn-prevent-multiple-submits">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>{{-- /modal-content --}}
    </div>
</div>

<!-- QA Grading Report Modal -->
<div class="modal fade" id="qaGradingReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('qa_grading_report_export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-excel text-success"></i> QA Grading Report</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Per-carcass grading details: vendor, carcass no, QA parameters, grade and narration.</p>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="from_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="to_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Slaughter Grading Summary Report Modal -->
<div class="modal fade" id="slaughterGradingReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('slaughter_grading_report_export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chart-bar text-primary"></i> Slaughter Grading Summary</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Summary per vendor/receipt: qty supplied, total CDW, grading breakdown and downgrade count.</p>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="from_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="to_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Mirrors app/Models/CarcassGradingService.php — beef (BG1021) only. Keep
    // both in sync if the scorecard in "Grading template formulation (2).xlsx"
    // ever changes; the server recomputes and is authoritative on save, this
    // is purely for the live preview in the modal.
    var CarcassGrading = (function () {
        var GRADE_BANDS = {
            1: { tier: 5, min: 17, max: 18 }, // Premium
            2: { tier: 4, min: 13, max: 17 }, // High Grade
            8: { tier: 3, min: 10, max: 16 }, // FAQ
            9: { tier: 2, min: 10, max: 16 }, // Standard
            3: { tier: 1, min: 8,  max: 14 }, // Commercial
            4: { tier: 0, min: 0,  max: 7  }  // Poor C
        };
        var WEIGHT_BANDS = [
            { min: 200, tier: 5 }, // Premium: 200-300kg
            { min: 170, tier: 4 }, // High Grade: 170-199.9kg
            { min: 150, tier: 3 }, // FAQ: 150-169.9kg
            { min: 120, tier: 2 }, // Standard: 120-149.9kg
            { min: 0,   tier: 1 }  // Commercial: below 120kg
        ];
        var MAX_WEIGHT = 300; // Premium's band has an upper bound; above it, falls off the defined bands.
        var POINTS = {
            dentition:  { 1: 1, 2: 2, 3: 3, 4: 3, 5: 3 },
            fat_cover:  { 4: 4, 1: 3, 2: 2, 3: 1, 0: 0 },
            fat_color:  { 1: 3, 3: 2, 2: 1 },
            meat_color: { 1: 2, 2: 1 },
            bruising:   { 0: 3, 1: 2, 3: 1, 4: 1, 5: 0 },
            muscle:     { 1: 3, 2: 2, 3: 1 }
        };
        var LABELS = { 1: 'Premium', 2: 'High Grade', 8: 'FAQ', 9: 'Standard', 3: 'Commercial', 4: 'Poor C', 10: 'Condemned' };
        // Hard-override option values — bypass verdict1/verdict2 scoring
        // entirely (see resolveOverride below).
        var FAT_COVER_NONE = 0, FAT_COVER_INADEQUATE = 3, FAT_COLOR_DEEP_YELLOW = 2,
            BRUISING_MILD = 1, BRUISING_SEVERE = 3, BRUISING_DETAINED = 4, BRUISING_CONDEMNED = 5,
            MUSCLE_POOR = 3, CONDEMNED = 10;

        function scoreAttributes(attrs) {
            var total = 0, missing = [];
            Object.keys(POINTS).forEach(function (field) {
                var raw = attrs[field];
                if (raw === undefined || raw === null || raw === '') { missing.push(field); return; }
                var val = parseInt(raw, 10);
                if (!(val in POINTS[field])) { missing.push(field); return; }
                total += POINTS[field][val];
            });
            return { verdict1: missing.length ? null : total, missing: missing };
        }

        function weightTier(weight) {
            if (weight > MAX_WEIGHT) return null; // falls off the defined weight bands
            for (var i = 0; i < WEIGHT_BANDS.length; i++) {
                if (weight >= WEIGHT_BANDS[i].min) return WEIGHT_BANDS[i].tier;
            }
            return 1;
        }

        function gradeForTier(tier) {
            var found = 4;
            Object.keys(GRADE_BANDS).forEach(function (g) {
                if (GRADE_BANDS[g].tier === tier) found = parseInt(g, 10);
            });
            return found;
        }

        function intOrNull(v) {
            return (v === undefined || v === null || v === '') ? null : parseInt(v, 10);
        }

        // Hard overrides — unconditional, don't need weight, worst-first so
        // the most severe one wins when several apply at once.
        function resolveOverride(attrs) {
            var bruising = intOrNull(attrs.bruising);
            var muscle = intOrNull(attrs.muscle);
            var fatCover = intOrNull(attrs.fat_cover);
            var fatColor = intOrNull(attrs.fat_color);

            if (bruising === BRUISING_CONDEMNED) return CONDEMNED;
            if (bruising === BRUISING_DETAINED || bruising === BRUISING_SEVERE || muscle === MUSCLE_POOR) return 4; // Poor C
            if (fatCover === FAT_COVER_NONE || fatCover === FAT_COVER_INADEQUATE
                || fatColor === FAT_COLOR_DEEP_YELLOW || bruising === BRUISING_MILD) return 3; // Commercial
            return null;
        }

        function compute(attrs, weight) {
            var scored = scoreAttributes(attrs);
            var result = {
                verdict1: scored.verdict1, verdict2: null, classification: null,
                is_indeterminate: false, awaiting_weight: false, is_override: false,
                candidates: [], missing: scored.missing
            };

            var hasWeight = !!weight && weight > 0;
            var wTier = hasWeight ? weightTier(weight) : null;
            if (hasWeight && wTier !== null && scored.verdict1 !== null) {
                result.verdict2 = scored.verdict1 + wTier;
            }

            var override = resolveOverride(attrs);
            if (override !== null) {
                result.classification = override;
                result.is_override = true;
                return result;
            }

            if (scored.verdict1 === null) return result;

            var candidates = [];
            Object.keys(GRADE_BANDS).forEach(function (g) {
                g = parseInt(g, 10);
                var band = GRADE_BANDS[g];
                if (scored.verdict1 >= band.min && scored.verdict1 <= band.max) candidates.push(g);
            });
            candidates.sort(function (a, b) { return GRADE_BANDS[b].tier - GRADE_BANDS[a].tier; });
            result.candidates = candidates;

            if (!candidates.length) {
                result.classification = 4;
                result.is_indeterminate = true;
                return result;
            }

            // Weight is now mandatory to finalize a normal-path grade —
            // without it (or if it's out of the defined bands), offer the
            // best-case candidate as a provisional suggestion only.
            if (!hasWeight) {
                result.classification = candidates[0];
                result.awaiting_weight = true;
                return result;
            }

            if (wTier === null) {
                result.classification = candidates[0];
                result.is_indeterminate = true;
                return result;
            }

            if (candidates.length === 1) {
                var grade = candidates[0];
                if (wTier < GRADE_BANDS[grade].tier) grade = gradeForTier(wTier);
                result.classification = grade;
                return result;
            }

            var supported = candidates.filter(function (g) { return GRADE_BANDS[g].tier <= wTier; });
            if (supported.length) {
                supported.sort(function (a, b) { return GRADE_BANDS[b].tier - GRADE_BANDS[a].tier; });
                result.classification = supported[0];
                return result;
            }

            // Weight rules out every tied candidate (it's below all of them)
            // — weight is the supreme verdict, so it resolves the grade
            // directly. Decisive, not ambiguous — no is_indeterminate flag.
            result.classification = gradeForTier(wTier);
            return result;
        }

        return { compute: compute, LABELS: LABELS };
    })();

    $(document).ready(function () {

        // Report buttons — explicit JS to avoid Bootstrap data-api conflicts
        $('#btn-qa-report').on('click', function () {
            $('#qaGradingReportModal').modal('show');
        });
        $('#btn-slaughter-report').on('click', function () {
            $('#slaughterGradingReportModal').modal('show');
        });

        // Auto-open report modal when navigating from the header dropdown
        var exportParam = new URLSearchParams(window.location.search).get('export');
        if (exportParam === 'qa')        $('#qaGradingReportModal').modal('show');
        if (exportParam === 'slaughter') $('#slaughterGradingReportModal').modal('show');

        var currentGradingRow = null;
        var currentItemCode = null;
        var currentSettlementWeight = null;

        $("#execute-grading-btn").click(function (e) {
            e.preventDefault();
            runGradingClasses();
        });

        $('.params').on("change", function () {
            $('.btn-prevent-multiple-submits').prop('disabled', false);
        });

        // Live grade preview — beef (BG1021) only. Recomputes on every
        // attribute change and auto-fills Classification; QA can still pick a
        // different value afterwards before saving.
        function updateAutoGradePreview() {
            if (currentItemCode !== 'BG1021') {
                $('#autoGradeHint').text('');
                return;
            }

            var attrs = {
                dentition: $('#dentition').val(),
                fat_cover: $('#fat_cover').val(),
                fat_color: $('#fat_color').val(),
                meat_color: $('#meat_color').val(),
                bruising: $('#bruising').val(),
                muscle: $('#muscle').val()
            };

            var result = CarcassGrading.compute(attrs, currentSettlementWeight);

            if (result.classification === null) {
                if (result.verdict1 === null) {
                    $('#autoGradeHint').removeClass('text-warning').addClass('text-muted')
                        .text('Select all 6 attributes to see the auto-computed grade.');
                    return;
                }

                // Only reachable when verdict1 ties between candidates and
                // settlement weight isn't known yet to break it.
                var tied = result.candidates.map(function (g) { return CarcassGrading.LABELS[g]; });
                $('#autoGradeHint').removeClass('text-muted').addClass('text-warning')
                    .text('Tied between ' + tied.join(', ') + ' (Verdict 1: ' + result.verdict1 +
                        ') — settlement weight is needed to break the tie.');
                return;
            }

            $('#fat_group').val(result.classification).trigger('change');

            if (result.classification === 10) { // Condemned
                $('#autoGradeHint').removeClass('text-muted').removeClass('text-warning').addClass('text-danger')
                    .text('⚠ Condemned — bruising is marked Condemned. This carcass is recorded as condemned, not graded.');
                return;
            }

            if (result.is_override) {
                $('#autoGradeHint').removeClass('text-muted').removeClass('text-warning').addClass('text-info')
                    .text('Auto-computed: ' + CarcassGrading.LABELS[result.classification] +
                        ' — forced by a hard rule (Detained / poorly conformed muscle / no fat cover), regardless of the point score.');
                return;
            }

            if (result.awaiting_weight) {
                $('#autoGradeHint').removeClass('text-muted').addClass('text-warning')
                    .text('Suggested: ' + CarcassGrading.LABELS[result.classification] + ' (Verdict 1: ' + result.verdict1 +
                        ') — provisional; settlement weight is still needed to finalize the grade (weight can only lower it further).');
                return;
            }

            var hint = 'Auto-computed: ' + CarcassGrading.LABELS[result.classification] +
                ' (Verdict 1: ' + result.verdict1 + ', Verdict 2: ' + result.verdict2 + ')';
            if (result.is_indeterminate) {
                if (result.candidates.length) {
                    var others = result.candidates
                        .filter(function (g) { return g !== result.classification; })
                        .map(function (g) { return CarcassGrading.LABELS[g]; });
                    hint += '. ⚠ Tied with ' + others.join(', ') + ' — weight did not clearly resolve it; please review.';
                } else {
                    hint += '. ⚠ Weight is outside the expected range — please review.';
                }
                $('#autoGradeHint').removeClass('text-muted').addClass('text-warning');
            } else {
                $('#autoGradeHint').removeClass('text-warning').addClass('text-muted');
            }
            $('#autoGradeHint').text(hint);
        }

        $('#dentition, #fat_cover, #fat_color, #meat_color, #bruising, #muscle').on('change', updateAutoGradePreview);

        $("body").on("click", ".gradingShow", function (a) {
            a.preventDefault();

            currentGradingRow = $(this).closest('tr');

            var id             = $(this).data('id');
            var agg_no         = $(this).data('agg_no');
            var item_code      = $(this).data('item_code');
            var item_name      = $(this).data('item_name');
            var vendor         = $(this).data('vendor');
            var settlement     = $(this).data('settlement_weight');
            // Saved QA values for pre-population
            var savedClass     = $(this).data('classification');
            var savedDentition = $(this).data('dentition');
            var savedFatCover  = $(this).data('fat_cover');
            var savedFatColor  = $(this).data('fat_color');
            var savedMeatColor = $(this).data('meat_color');
            var savedBruising  = $(this).data('bruising');
            var savedMuscle    = $(this).data('muscle');
            var savedNarration = $(this).data('narration');

            $('#item_id').val(id);
            $('#agg_no').val(agg_no);
            $('#item_code').val(item_code);
            $('#item_name').val(item_name);
            $('#vendor_no').val(vendor);
            $('#settlement_weight').val((Math.round(settlement * 100) / 100).toFixed(2));
            $('#narration').val(savedNarration || '');

            // Build fat_group options based on animal type
            $('#fat_group').empty().append('<option disabled selected> select an option </option>');
            if (item_code === 'BG1021') {
                $('#fat_group').append('<option value="1">Premium</option>');
                $('#fat_group').append('<option value="2">High Grade</option>');
                $('#fat_group').append('<option value="8">FAQ</option>');
                $('#fat_group').append('<option value="9">Standard</option>');
                $('#fat_group').append('<option value="3">Commercial</option>');
                $('#fat_group').append('<option value="4">Poor C</option>');
                $('#fat_group').append('<option value="10">Condemned</option>');
            } else {
                $('#fat_group').append('<option value="5">Lamb 1st grade</option>');
                $('#fat_group').append('<option value="6">Lamb 2nd grade</option>');
                $('#fat_group').append('<option value="7">Lamb Class R</option>');
            }

            // Re-init select2 on fat_group (dynamic options), then pre-select saved value
            $('#fat_group').select2('destroy').select2();
            if (savedClass !== undefined && savedClass !== '') {
                $('#fat_group').val(savedClass).trigger('change');
            }
            $('#autoGradeHint').text('');
            currentItemCode = item_code;
            currentSettlementWeight = parseFloat(settlement) || null;

            // Pre-populate remaining selects with saved values ('' resets to placeholder)
            // Use !== undefined check so 0 (No Bruises) is correctly restored
            var setSelect = function(id, val) {
                $(id).val((val !== undefined && val !== '') ? val : '').trigger('change');
            };
            setSelect('#dentition',  savedDentition);
            setSelect('#fat_cover',  savedFatCover);
            setSelect('#fat_color',  savedFatColor);
            setSelect('#meat_color', savedMeatColor);
            setSelect('#bruising',   savedBruising);
            setSelect('#muscle',     savedMuscle);

            $('#gradingShow').modal('show');
        });

        $('#form-grading-slaughter').validate({
            rules: {
                fat_group: { required: true },
                narration:  { maxlength: 50 },
            },
            messages: {
                fat_group: "Please select a classification first",
                narration: { maxlength: "Narration must be at most 50 characters" },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var $btn = $('.btn-prevent-multiple-submits');
                var originalBtnHtml = $btn.html();
                $btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Saving...');

                var savedRowId    = $('#item_id').val();
                var savedClassText = $('#fat_group option:selected').text().trim();

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (response) {
                        // Find the td by data-id (works regardless of DataTable page/order)
                        var $gradingTd = $('td.gradingShow[data-id="' + savedRowId + '"]');
                        $gradingTd.find('a')
                            .attr('class', 'text-success')
                            .html('<i class="fas fa-check-circle"></i> graded <i class="fas fa-arrow-right"></i>');
                        $gradingTd.next('td').text(savedClassText);

                        // Update the Auto Suggestion cell from what the server just computed
                        var $autoTd = $('td.auto-suggestion-cell[data-id="' + savedRowId + '"]');
                        if (response.auto_classification === null || response.auto_classification === undefined) {
                            $autoTd.html('<span class="text-muted">--</span>');
                        } else {
                            var autoHtml = response.auto_classification_label === 'Condemned'
                                ? '<span class="text-danger font-weight-bold">Condemned</span>'
                                : response.auto_classification_label;
                            if (response.awaiting_weight) {
                                autoHtml += ' <span class="badge badge-info" title="Provisional &mdash; settlement weight isn\'t recorded yet; weight can only lower this grade, never raise it">awaiting weight</span>';
                            } else if (response.is_indeterminate) {
                                autoHtml += ' <span class="badge badge-warning" title="Could not be auto-graded with confidence &mdash; please review">flagged</span>';
                            }
                            $autoTd.html(autoHtml);
                        }

                        // Update is_downgraded badge
                        var $downgradedTd = $gradingTd.closest('tr').find('.downgraded-cell');
                        if (response.is_downgraded === null || response.is_downgraded === undefined) {
                            $downgradedTd.html('<span class="badge badge-secondary">--</span>');
                        } else if (response.is_downgraded === 0) {
                            $downgradedTd.html('<span class="badge badge-success">No</span>');
                        } else {
                            $downgradedTd.html('<span class="badge badge-danger">Yes</span>');
                        }

                        $('#gradingShow').modal('hide');
                        $btn.prop('disabled', false).html(originalBtnHtml);
                        toastr.success(response.message, 'Saved');
                        setUserMessage('succ', 'err', response.message, '');
                        setTimeout(function () { setUserMessage('succ', 'err', '', ''); }, 3000);
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                        toastr.error(msg, 'Error');
                        setUserMessage('succ', 'err', '', msg);
                        $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            }
        });
    });

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ;
        document.getElementById(field_err).innerHTML = message_err;
    };

    const runGradingClasses = () => {
        setUserMessage('succ', 'err', 'Generating classifications...', '');
        $.ajax({
            url: "{{ url('QA/run/grading-classes') }}",
            type: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                setUserMessage('succ', 'err', 'Classifications generated. Reloading...', '');
                setTimeout(function () { window.location.reload(); }, 1000);
            },
            error: function (data) {
                console.log(data.responseJSON);
                setUserMessage('succ', 'err', '', 'Error generating classifications');
            }
        });
    };

</script>
@endsection
