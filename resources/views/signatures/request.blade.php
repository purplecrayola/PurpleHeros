<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PurpleHeros Signature Request</title>
    <style>
        :root {
            --brand: #8A00FF;
            --brand-dark: #00163F;
            --bg: #f3f1fb;
            --card: #ffffff;
            --muted: #64748b;
            --border: #d8deea;
            --ok: #0f9d58;
            --warn: #c2410c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #ece8fb 0%, var(--bg) 100%);
            color: #0f172a;
            min-height: 100vh;
        }
        .wrap {
            width: min(920px, 94vw);
            margin: 40px auto;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .head {
            padding: 22px 26px;
            background: linear-gradient(90deg, var(--brand) 0%, #b84dff 100%);
            color: #fff;
        }
        .head h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }
        .head p {
            margin: 8px 0 0;
            opacity: 0.92;
        }
        .content { padding: 24px 26px; }
        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .meta-box {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            background: #fafcff;
        }
        .label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
        .value { margin-top: 5px; font-weight: 600; }
        .msg {
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 14px;
        }
        .msg.ok { background: #e7f8ef; color: #05603a; border: 1px solid #b7e9cc; }
        .msg.error { background: #fff1ec; color: #9a3412; border: 1px solid #ffd9ca; }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .status-pending { background: #fff6e9; color: #92400e; }
        .status-signed { background: #e8f7ee; color: #166534; }
        .status-expired, .status-cancelled { background: #fdf1f2; color: #991b1b; }
        .doc-link {
            display: inline-block;
            margin-bottom: 16px;
            font-size: 14px;
            color: var(--brand-dark);
            text-decoration: none;
            border-bottom: 1px dashed rgba(0,22,63,.3);
        }
        .doc-link:hover { color: var(--brand); border-color: rgba(138,0,255,.5); }
        form {
            margin-top: 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            background: #fcfdff;
        }
        .field { margin-bottom: 14px; }
        .field label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; }
        input[type="text"] {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            font-size: 15px;
        }
        .consent {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 14px;
            line-height: 1.45;
        }
        .error-text { color: #b91c1c; font-size: 13px; margin-top: 6px; }
        .actions { margin-top: 18px; }
        button {
            border: 0;
            border-radius: 10px;
            padding: 12px 18px;
            background: linear-gradient(90deg, var(--brand) 0%, #3525a9 100%);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <header class="head">
                <h1>PurpleHeros Signature Request</h1>
                <p>Secure document acknowledgement and internal signing</p>
            </header>

            <div class="content">
                @if (session('success'))
                    <div class="msg ok">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="msg error">{{ session('error') }}</div>
                @endif

                <div class="meta">
                    <div class="meta-box">
                        <div class="label">Employee</div>
                        <div class="value">{{ $signatureRequest->onboarding?->user?->name ?: $signatureRequest->user_id }}</div>
                    </div>
                    <div class="meta-box">
                        <div class="label">Document Type</div>
                        <div class="value">{{ ucfirst($signatureRequest->document_type) }}</div>
                    </div>
                    <div class="meta-box">
                        <div class="label">Status</div>
                        <div class="value">
                            <span class="status-pill status-{{ strtolower($signatureRequest->status) }}">
                                {{ strtoupper($signatureRequest->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="meta-box">
                        <div class="label">Expires</div>
                        <div class="value">{{ $signatureRequest->expires_at?->format('d M Y H:i') ?: 'No expiry' }}</div>
                    </div>
                    <div class="meta-box">
                        <div class="label">Current Stage</div>
                        <div class="value">{{ $activeSigner?->role_label ?: 'Viewer' }}</div>
                    </div>
                </div>

                @if ($documentUrl)
                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="doc-link">Open document preview</a>
                @endif

                @if ($signedArchiveDownloadUrl)
                    <a href="{{ $signedArchiveDownloadUrl }}" class="doc-link">Download signed archive PDF</a>
                @endif

                <div style="margin-bottom: 14px;">
                    <div class="label" style="margin-bottom: 8px;">Signing Actors</div>
                    <div style="border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid var(--border);">Order</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid var(--border);">Role</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid var(--border);">Signer</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid var(--border);">Field</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid var(--border);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($signatureRequest->signers as $signer)
                                    <tr>
                                        <td style="padding: 8px; border-bottom: 1px solid #edf2f7;">{{ (int) $signer->sign_order }}</td>
                                        <td style="padding: 8px; border-bottom: 1px solid #edf2f7;">{{ $signer->role_label ?: 'Signer' }}</td>
                                        <td style="padding: 8px; border-bottom: 1px solid #edf2f7;">{{ $signer->signer_name ?: $signer->signer_email }}</td>
                                        <td style="padding: 8px; border-bottom: 1px solid #edf2f7;">
                                            {{ $signer->signature_field_key ?: 'SIGNATURE' }} (Pg {{ (int) ($signer->page_number ?: 1) }})
                                            <br>
                                            <span style="font-size: 11px; color: #64748b;">
                                                X={{ $signer->position_x ?? '-' }}, Y={{ $signer->position_y ?? '-' }}, W={{ $signer->field_width ?? '-' }}, H={{ $signer->field_height ?? '-' }}
                                            </span>
                                        </td>
                                        <td style="padding: 8px; border-bottom: 1px solid #edf2f7;">{{ strtoupper($signer->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($signatureRequest->status === 'signed')
                    <div class="msg ok">This document has already been signed on {{ $signatureRequest->signed_at?->format('d M Y H:i') }}.</div>
                @elseif (in_array($signatureRequest->status, ['expired', 'cancelled'], true))
                    <div class="msg error">This signature request is {{ $signatureRequest->status }} and can no longer be signed.</div>
                @elseif (! $activeSigner)
                    <div class="msg error">This link is view-only. Use the signer-stage email link to complete signature actions.</div>
                @elseif (! $canSign)
                    <div class="msg error">This stage is waiting on an earlier signer or has already been completed.</div>
                @else
                    <form method="post" action="{{ route('signature/request/submit', ['token' => $activeSigner?->token ?: $signatureRequest->token]) }}">
                        @csrf
                        <div class="field">
                            <label for="typed_signature_name">Type your full name to sign</label>
                            <input id="typed_signature_name" name="typed_signature_name" type="text" value="{{ old('typed_signature_name', $activeSigner?->signer_name ?: $signatureRequest->signer_name) }}" required maxlength="120" />
                            @error('typed_signature_name')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field consent">
                            <input id="consent" name="consent" type="checkbox" value="1" {{ old('consent') ? 'checked' : '' }} required />
                            <label for="consent">
                                I confirm I reviewed this document and agree to sign it electronically on PurpleHeros.
                                My acknowledgement, timestamp, and device metadata will be logged for audit.
                            </label>
                        </div>
                        @error('consent')
                            <div class="error-text">{{ $message }}</div>
                        @enderror

                        <div class="actions">
                            <button type="submit">Sign Document</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
