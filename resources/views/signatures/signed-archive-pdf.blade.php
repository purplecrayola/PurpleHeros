<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Signed Document Archive</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 24px;
        }
        .header {
            border-bottom: 3px solid #8A00FF;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .brand {
            font-size: 20px;
            font-weight: 700;
            color: #00163F;
        }
        .title {
            margin-top: 4px;
            font-size: 14px;
            color: #475569;
        }
        .section {
            border: 1px solid #dbe2ef;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .value {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            word-break: break-word;
        }
        .mono {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            font-weight: 400;
            word-break: break-all;
        }
        .footer {
            margin-top: 14px;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">PurpleHeros</div>
        <div class="title">Signed Document Archive (Internal)</div>
    </div>

    <div class="section">
        <div class="label">Employee</div>
        <div class="value">{{ $employee_name }}</div>

        <div class="label">Employee ID</div>
        <div class="value">{{ $request->user_id }}</div>

        <div class="label">Document Type</div>
        <div class="value">{{ strtoupper($request->document_type) }}</div>

        <div class="label">Signed At</div>
        <div class="value">{{ $signed_at?->format('Y-m-d H:i:s') }}</div>
    </div>

    <div class="section">
        <div class="label">Signature Actors</div>
        @foreach (($signers ?? collect()) as $signer)
            <div class="value">
                #{{ (int) ($signer->sign_order ?? 1) }}
                {{ $signer->role_label ?: 'Signer' }} -
                {{ $signer->signer_name ?: $signer->signer_email }}
                | {{ strtoupper((string) ($signer->status ?? 'pending')) }}
                | {{ $signer->signature_field_key ?: 'SIGNATURE' }} / Pg {{ (int) ($signer->page_number ?? 1) }}
                | X={{ $signer->position_x ?? '-' }},Y={{ $signer->position_y ?? '-' }},W={{ $signer->field_width ?? '-' }},H={{ $signer->field_height ?? '-' }}
                @if ($signer->signed_at)
                    | {{ $signer->signed_at->format('Y-m-d H:i:s') }}
                @endif
            </div>
        @endforeach
    </div>

    <div class="section">
        <div class="label">Audit Hash</div>
        <div class="value mono">{{ $signed_hash }}</div>

        <div class="label">Request Token</div>
        <div class="value mono">{{ $request->token }}</div>

        <div class="label">IP Address</div>
        <div class="value">{{ $ip_address ?: 'N/A' }}</div>

        <div class="label">User Agent</div>
        <div class="value mono">{{ $user_agent ?: 'N/A' }}</div>
    </div>

    <div class="footer">
        This is an internally generated PurpleHeros signature archive copy.
    </div>
</body>
</html>
