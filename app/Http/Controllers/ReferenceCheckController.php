<?php

namespace App\Http\Controllers;

use App\Models\EmployeeReference;
use App\Support\ReferenceCheckManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferenceCheckController extends Controller
{
    public function show(string $token): View
    {
        $reference = EmployeeReference::query()
            ->with('employeeUser:user_id,name,email')
            ->where('request_token', $token)
            ->first();

        if (! $reference) {
            abort(404);
        }

        if ($reference->request_status === 'sent' && $reference->request_expires_at && $reference->request_expires_at->isPast()) {
            $reference->request_status = 'expired';
            $reference->save();
        }

        $canSubmit = $reference->request_status === 'sent'
            && (! $reference->request_expires_at || ! $reference->request_expires_at->isPast());

        return view('references.respond', [
            'reference' => $reference,
            'canSubmit' => $canSubmit,
            'employeeName' => (string) ($reference->employeeUser?->name ?: $reference->user_id),
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $reference = EmployeeReference::query()
            ->with('employeeUser:user_id,name,email')
            ->where('request_token', $token)
            ->first();

        if (! $reference) {
            abort(404);
        }

        if ($reference->request_status !== 'sent') {
            return redirect()->route('reference/check/show', ['token' => $token])
                ->with('error', 'This reference request is not active.');
        }

        if ($reference->request_expires_at && $reference->request_expires_at->isPast()) {
            $reference->request_status = 'expired';
            $reference->save();

            return redirect()->route('reference/check/show', ['token' => $token])
                ->with('error', 'This reference request has expired.');
        }

        $data = $request->validate([
            'known_duration' => ['nullable', 'string', 'max:255'],
            'worked_together' => ['required', 'in:yes,no'],
            'rehire_recommendation' => ['required', 'in:yes,no,unsure'],
            'overall_rating' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:4000'],
        ]);

        ReferenceCheckManager::applyResponse($reference, $data);

        return redirect()->route('reference/check/show', ['token' => $token])
            ->with('success', 'Thank you. Your reference response has been submitted.');
    }
}
