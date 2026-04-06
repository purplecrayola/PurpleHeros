@php
    $peopleOpsSettings = \App\Models\CompanySettings::current();
    $peopleOpsEmail = trim((string) (
        $peopleOpsSettings->people_ops_email
        ?: $peopleOpsSettings->mail_reply_to_address
        ?: $peopleOpsSettings->email
        ?: 'heros@purplecrayola.com'
    ));

    if (! filter_var($peopleOpsEmail, FILTER_VALIDATE_EMAIL)) {
        $peopleOpsEmail = 'heros@purplecrayola.com';
    }
@endphp

<style>
    body.employee-dashboard-shell #employeePeopleOpsModal .modal-content {
        border-radius: 16px;
        border: 1px solid #eae7f2;
        box-shadow: 0 8px 24px rgba(40, 24, 82, 0.06);
    }
    body.employee-dashboard-shell #employeePeopleOpsModal .modal-header {
        border-bottom: 1px solid #eae7f2;
    }
    body.employee-dashboard-shell #employeePeopleOpsModal .modal-title {
        color: #171327;
        font-size: 20px;
        line-height: 28px;
        font-weight: 600;
    }
    body.employee-dashboard-shell #employeePeopleOpsModal .modal-subtext {
        color: #5e5873;
        font-size: 14px;
        line-height: 20px;
        margin-bottom: 18px;
    }
    body.employee-dashboard-shell #employeePeopleOpsModal .modal-subtext strong {
        color: #171327;
        font-weight: 600;
    }
    body.employee-dashboard-shell #employeePeopleOpsModal textarea.form-control {
        min-height: 132px;
        resize: vertical;
    }
</style>

<div class="modal fade" id="employeePeopleOpsModal" tabindex="-1" role="dialog" aria-labelledby="employeePeopleOpsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('employee/people-ops/contact') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="employeePeopleOpsModalLabel">Contact People Ops</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-subtext">
                        Send a support request to <strong>{{ $peopleOpsEmail }}</strong>.
                    </p>

                    <div class="form-group">
                        <label for="people_ops_subject">Subject</label>
                        <input
                            id="people_ops_subject"
                            type="text"
                            name="subject"
                            class="form-control @error('subject') is-invalid @enderror"
                            maxlength="120"
                            required
                            value="{{ old('subject') }}"
                            placeholder="Short summary of your request"
                        >
                        @error('subject')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="people_ops_message">Message</label>
                        <textarea
                            id="people_ops_message"
                            name="message"
                            class="form-control @error('message') is-invalid @enderror"
                            maxlength="4000"
                            required
                            placeholder="Tell People Ops what you need help with">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="context_url" id="people_ops_context_url" value="{{ old('context_url') }}">
                    @error('people_ops')
                        <div class="alert alert-danger mb-0">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('employee-dashboard-shell')) {
            return;
        }

        var triggerSelector = '.employee-people-ops-trigger';
        var contextInput = document.getElementById('people_ops_context_url');
        var modalElement = document.getElementById('employeePeopleOpsModal');

        document.querySelectorAll(triggerSelector).forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
            });
        });

        if (contextInput) {
            contextInput.value = window.location.href;
        }

        @if($errors->has('subject') || $errors->has('message') || $errors->has('people_ops'))
            if (modalElement && window.jQuery) {
                window.jQuery(modalElement).modal('show');
            }
        @endif
    });
</script>

