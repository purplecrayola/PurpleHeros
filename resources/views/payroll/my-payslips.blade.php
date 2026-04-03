@extends('layouts.master')

@section('content')
@include('employees.partials.self-service-style')
<div class="page-wrapper self-service-modern">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">My Payslips</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payslips</li>
                    </ul>
                    <p class="section-intro">Access published monthly payslips and download available files.</p>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <h4 class="panel-title">Published Payslips</h4>
                <span class="panel-meta">{{ $payslips->total() }} total</span>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Source</th>
                                <th>Gross</th>
                                <th>PAYE</th>
                                <th>Pension</th>
                                <th>NHF</th>
                                <th>Deductions</th>
                                <th>Net</th>
                                <th>Total Paid</th>
                                <th>Issued</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payslips as $payslip)
                                @php($computed = data_get($payslip->payload, 'computed', []))
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromDate((int) $payslip->period_year, (int) $payslip->period_month, 1)->format('M Y') }}</td>
                                    <td>{{ ucfirst((string) $payslip->source) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'total_taxable_earnings', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'monthly_paye', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'monthly_pension', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'monthly_nhf', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'total_deductions', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'net_salary', 0), 2) }}</td>
                                    <td>₦{{ number_format((float) data_get($computed, 'total_paid', 0), 2) }}</td>
                                    <td>{{ optional($payslip->issued_at)->format('d M Y H:i') ?: '-' }}</td>
                                    <td>
                                        @if($payslip->file_path)
                                            <a href="{{ $payslip->file_url }}" target="_blank" rel="noopener">Download</a>
                                        @elseif(is_array($payslip->payload) && ! empty($payslip->payload))
                                            <a href="{{ route('my/payslips/download', $payslip) }}">Download PDF</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center table-empty">No payslips have been published for your account yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $payslips->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
