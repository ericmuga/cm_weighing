<?php

namespace App\Http\Controllers;

use App\Models\GradingSetting;
use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * QA > Settings: edit grading parameters. Only usernames listed in
 * GRADING_SETTINGS_USERS (config/grading.php) can open or save it.
 */
class GradingSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Helpers $helpers)
    {
        $this->authorizeEditor();

        $title = 'Grading Settings';
        $values = GradingSetting::all();
        $groups = [];
        foreach (GradingSetting::DEFINITIONS as $key => [$default, $label, $unit, $group]) {
            $groups[$group][$key] = compact('default', 'label', 'unit') + ['value' => $values[$key]];
        }

        $changes = DB::table('grading_setting_changes')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('QA.settings', compact('title', 'helpers', 'groups', 'changes'));
    }

    public function update(Request $request)
    {
        $this->authorizeEditor();

        $rules = [];
        foreach (array_keys(GradingSetting::DEFINITIONS) as $key) {
            $rules[$key] = 'required|numeric|min:0|max:1000';
        }
        $validated = $request->validate($rules);

        $values = array_map('floatval', $validated);
        $errors = GradingSetting::validateValues($values);
        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        $user = Auth::user();
        $changed = GradingSetting::saveMany($values, $user->id, $user->username);

        if ($changed) {
            Log::info('Grading settings changed by ' . $user->username, $changed);
            Toastr::success(count($changed) . ' setting(s) updated. New grading uses them from now on.', 'Saved');
        } else {
            Toastr::info('No changes to save.', 'Settings');
        }

        return redirect()->route('qa_settings');
    }

    private function authorizeEditor(): void
    {
        abort_unless(GradingSetting::canEdit(Auth::user()->username ?? null), 403, 'You are not allowed to change grading settings.');
    }
}
