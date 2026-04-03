<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reference Check - PurpleHeros</title>
    <style>
        :root {
            --brand: #8A00FF;
            --bg: #f3f2fb;
            --card: #fff;
            --border: #d8deea;
            --muted: #64748b;
        }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: linear-gradient(180deg, #ece8fb, var(--bg)); color: #0f172a; }
        .wrap { width: min(860px, 94vw); margin: 36px auto; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(15,23,42,.07); }
        .head { padding: 22px; background: linear-gradient(90deg, var(--brand), #b84dff); color: #fff; }
        .head h1 { margin: 0; font-size: 28px; }
        .head p { margin: 8px 0 0; opacity: .95; }
        .body { padding: 22px; }
        .msg { border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; font-size: 14px; }
        .ok { background: #e7f8ef; border: 1px solid #b7e9cc; color: #05603a; }
        .err { background: #fff1ec; border: 1px solid #ffd9ca; color: #9a3412; }
        .meta { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 10px; margin-bottom: 14px; }
        .meta > div { border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px; background: #fbfcff; }
        .label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
        .value { margin-top: 4px; font-weight: 600; }
        form { border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
        .f { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        input, select, textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px; font-size: 14px; }
        textarea { min-height: 120px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .actions { margin-top: 12px; }
        button { border: 0; border-radius: 10px; padding: 11px 16px; color: #fff; background: linear-gradient(90deg, var(--brand), #3525a9); font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <header class="head">
                <h1>Reference Check</h1>
                <p>PurpleHeros secure referee response</p>
            </header>

            <div class="body">
                @if (session('success'))
                    <div class="msg ok">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="msg err">{{ session('error') }}</div>
                @endif

                <div class="meta">
                    <div>
                        <div class="label">Candidate</div>
                        <div class="value">{{ $employeeName }}</div>
                    </div>
                    <div>
                        <div class="label">Referee</div>
                        <div class="value">{{ $reference->referee_name }}</div>
                    </div>
                    <div>
                        <div class="label">Status</div>
                        <div class="value">{{ strtoupper($reference->request_status) }}</div>
                    </div>
                    <div>
                        <div class="label">Expires</div>
                        <div class="value">{{ $reference->request_expires_at?->format('d M Y H:i') ?: 'No expiry' }}</div>
                    </div>
                </div>

                @if (! $canSubmit)
                    <div class="msg err">This reference link is no longer active.</div>
                @else
                    <form method="post" action="{{ route('reference/check/submit', ['token' => $reference->request_token]) }}">
                        @csrf
                        <div class="row">
                            <div class="f">
                                <label for="known_duration">How long have you known the candidate?</label>
                                <input id="known_duration" type="text" name="known_duration" value="{{ old('known_duration') }}" placeholder="e.g. 3 years" />
                            </div>
                            <div class="f">
                                <label for="overall_rating">Overall rating (1-5)</label>
                                <select id="overall_rating" name="overall_rating" required>
                                    <option value="">Select rating</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" @selected((string) old('overall_rating') === (string) $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="f">
                                <label for="worked_together">Did you work directly with this candidate?</label>
                                <select id="worked_together" name="worked_together" required>
                                    <option value="">Choose</option>
                                    <option value="yes" @selected(old('worked_together') === 'yes')>Yes</option>
                                    <option value="no" @selected(old('worked_together') === 'no')>No</option>
                                </select>
                            </div>
                            <div class="f">
                                <label for="rehire_recommendation">Would you recommend for rehire?</label>
                                <select id="rehire_recommendation" name="rehire_recommendation" required>
                                    <option value="">Choose</option>
                                    <option value="yes" @selected(old('rehire_recommendation') === 'yes')>Yes</option>
                                    <option value="no" @selected(old('rehire_recommendation') === 'no')>No</option>
                                    <option value="unsure" @selected(old('rehire_recommendation') === 'unsure')>Unsure</option>
                                </select>
                            </div>
                        </div>

                        <div class="f">
                            <label for="comments">Comments</label>
                            <textarea id="comments" name="comments" placeholder="Share performance, conduct, and recommendation context">{{ old('comments') }}</textarea>
                        </div>

                        <div class="actions">
                            <button type="submit">Submit Reference</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
