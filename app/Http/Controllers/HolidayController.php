<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function holiday()
    {
        $holidays = Holiday::query()->orderBy('date_holiday')->get();

        return view('employees.holidays', compact('holidays'));
    }

    public function saveRecord(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $request->validate([
            'nameHoliday' => 'required|string|max:255',
            'holidayDate' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            Holiday::query()->create([
                'name_holiday' => $validated['nameHoliday'],
                'date_holiday' => $validated['holidayDate'],
            ]);

            DB::commit();
            Toastr::success('Holiday created successfully.', 'Success');

            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollBack();
            Toastr::error('Unable to create holiday.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    public function updateRecord(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $request->validate([
            'id' => 'required|integer|exists:holidays,id',
            'holidayName' => 'required|string|max:255',
            'holidayDate' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            Holiday::query()->where('id', $validated['id'])->update([
                'name_holiday' => $validated['holidayName'],
                'date_holiday' => $validated['holidayDate'],
            ]);

            DB::commit();
            Toastr::success('Holiday updated successfully.', 'Success');

            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollBack();
            Toastr::error('Unable to update holiday.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    public function deleteRecord(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $request->validate([
            'id' => 'required|integer|exists:holidays,id',
        ]);

        DB::beginTransaction();

        try {
            Holiday::query()->where('id', $validated['id'])->delete();

            DB::commit();
            Toastr::success('Holiday deleted successfully.', 'Success');

            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollBack();
            Toastr::error('Unable to delete holiday.', 'Error');

            return redirect()->back();
        }
    }
}
