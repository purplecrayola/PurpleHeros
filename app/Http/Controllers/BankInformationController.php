<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\BankInformation;
use App\Models\CompanySettings;
use Illuminate\Support\Facades\Auth;

class BankInformationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $allowSelfEdit = (bool) CompanySettings::current()->allow_employee_bank_edit;
        abort_unless(Auth::user()?->isAdmin() || $allowSelfEdit, 403);
        abort_unless(Auth::user()?->canAccessUserId($request->input('user_id')), 403);
        $request->validate([
            'primary_bank_name' => 'required|string|max:255',
            'primary_bank_account_no' => 'required|string|max:255',
            'primary_ifsc_code' => 'required|string|max:255',
            'primary_pan_no' => 'required|string|max:255',
            'secondary_bank_name' => 'nullable|string|max:255',
            'secondary_bank_account_no' => 'nullable|string|max:255',
            'secondary_ifsc_code' => 'nullable|string|max:255',
            'secondary_pan_no' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            
            $bankInformation = BankInformation::firstOrNew(
                ['user_id' =>  $request->user_id],
            );
            $bankInformation->user_id         = $request->user_id;
            $bankInformation->bank_name       = $request->primary_bank_name;
            $bankInformation->bank_account_no = $request->primary_bank_account_no;
            $bankInformation->ifsc_code       = $request->primary_ifsc_code;
            $bankInformation->pan_no          = $request->primary_pan_no;
            $bankInformation->primary_bank_name = $request->primary_bank_name;
            $bankInformation->primary_bank_account_no = $request->primary_bank_account_no;
            $bankInformation->primary_ifsc_code = $request->primary_ifsc_code;
            $bankInformation->primary_pan_no = $request->primary_pan_no;
            $bankInformation->secondary_bank_name = $request->secondary_bank_name;
            $bankInformation->secondary_bank_account_no = $request->secondary_bank_account_no;
            $bankInformation->secondary_ifsc_code = $request->secondary_ifsc_code;
            $bankInformation->secondary_pan_no = $request->secondary_pan_no;
            $bankInformation->save();

            DB::commit();
            Toastr::success('Add bank information successfully :)','Success');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Add bank information fail :)','Error');
            return redirect()->back();
        }
    }
}
