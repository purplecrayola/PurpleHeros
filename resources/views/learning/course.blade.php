@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $course->title }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('learning/catalog') }}">Learning Catalog</a></li>
                            <li class="breadcrumb-item active">Course</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <form method="POST" action="{{ route('learning/course/start', ['id' => $course->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Start Course</button>
                        </form>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Mode:</strong> {{ ucfirst(str_replace('_', ' ', $course->delivery_mode)) }}</div>
                        <div class="col-md-3"><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $enrollment->status)) }}</div>
                        <div class="col-md-3"><strong>Completion:</strong> {{ number_format((float) $enrollment->completion_percent, 1) }}%</div>
                        <div class="col-md-3"><strong>Due:</strong> {{ $enrollment->due_at ? \Illuminate\Support\Carbon::parse($enrollment->due_at)->format('d M Y') : '-' }}</div>
                    </div>
                    @if($course->delivery_mode === 'virtual' && filled($course->join_link))
                        <div class="mt-3">
                            <a href="{{ $course->join_link }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Join Virtual Session</a>
                        </div>
                    @endif
                    @if($course->delivery_mode === 'physical' && filled($course->venue))
                        <div class="mt-3 text-muted"><strong>Venue:</strong> {{ $course->venue }}</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title mb-0">Learning Assets</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <th>Progress</th>
                                    <th>Open / Player</th>
                                    <th>Track Progress</th>
                                    <th>Bookmark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($course->assets as $asset)
                                    @php($assetPercent = (float) ($assetProgress[$asset->id] ?? 0))
                                    @php($assetUrl = $asset->external_url ?: ($asset->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($asset->file_path) : null))
                                    <tr>
                                        <td>
                                            <strong>{{ $asset->title }}</strong>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit((string) $asset->description, 90) }}</div>
                                        </td>
                                        <td>{{ strtoupper($asset->asset_type) }}</td>
                                        <td>{{ number_format($assetPercent, 1) }}%</td>
                                        <td>
                                            @if($asset->asset_type === 'audio' && $assetUrl)
                                                <audio
                                                    controls
                                                    preload="metadata"
                                                    src="{{ $assetUrl }}"
                                                    class="js-audio-telemetry"
                                                    data-asset-id="{{ $asset->id }}"
                                                    data-duration-seconds="{{ (int) ($asset->duration_seconds ?? 0) }}"
                                                    style="width: 240px;"
                                                ></audio>
                                            @elseif($asset->asset_type === 'pdf' && $assetUrl)
                                                <div class="mb-1">
                                                    <a href="{{ $assetUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Open PDF</a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-open-pdf-viewer"
                                                        data-asset-id="{{ $asset->id }}"
                                                    >
                                                        Open Viewer
                                                    </button>
                                                </div>
                                                <div
                                                    id="pdf-viewer-wrap-{{ $asset->id }}"
                                                    class="border rounded p-2 mt-2"
                                                    style="display:none; max-width: 380px;"
                                                >
                                                    <div class="d-flex align-items-center mb-2" style="gap: 6px;">
                                                        <button type="button" class="btn btn-sm btn-light js-pdf-prev" data-asset-id="{{ $asset->id }}">Prev</button>
                                                        <span class="small">Page <span id="pdf-page-{{ $asset->id }}">1</span> / <span id="pdf-total-{{ $asset->id }}">{{ (int) ($asset->pages_count ?: 1) }}</span></span>
                                                        <button type="button" class="btn btn-sm btn-light js-pdf-next" data-asset-id="{{ $asset->id }}">Next</button>
                                                    </div>
                                                    <canvas
                                                        id="pdf-canvas-{{ $asset->id }}"
                                                        class="border"
                                                        width="340"
                                                        data-pdf-url="{{ $assetUrl }}"
                                                        data-asset-id="{{ $asset->id }}"
                                                        style="max-width: 100%;"
                                                    ></canvas>
                                                </div>
                                            @elseif($assetUrl)
                                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary js-open-asset" data-asset-id="{{ $asset->id }}">Open</a>
                                            @else
                                                <span class="text-muted">No link/file</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('learning/enrollment/progress', ['id' => $enrollment->id]) }}" class="form-inline">
                                                @csrf
                                                <input type="hidden" name="learning_asset_id" value="{{ $asset->id }}">
                                                <input type="hidden" name="event_type" value="progress_update">
                                                <input type="number" min="0" max="100" step="0.01" name="progress_percent" value="{{ $assetPercent }}" class="form-control form-control-sm mr-2" style="width:92px;">
                                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('learning/enrollment/bookmark', ['id' => $enrollment->id]) }}" class="form-inline">
                                                @csrf
                                                <input type="hidden" name="learning_asset_id" value="{{ $asset->id }}">
                                                <input type="text" name="label" class="form-control form-control-sm mr-2" placeholder="Label" style="width:120px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Add</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @if(($bookmarks[$asset->id] ?? collect())->isNotEmpty())
                                        <tr>
                                            <td colspan="6" class="bg-light">
                                                <strong class="small">Bookmarks:</strong>
                                                @foreach(($bookmarks[$asset->id] ?? collect())->take(3) as $bookmark)
                                                    <span class="badge badge-light border">{{ $bookmark->label ?: 'Bookmark' }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No assets added to this course yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            if (window.pdfjsLib) {
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }
            const telemetryUrl = @json(route('learning/enrollment/telemetry', ['id' => $enrollment->id]));
            const csrfToken = @json(csrf_token());

            function sendTelemetry(payload) {
                fetch(telemetryUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).catch(() => {});
            }

            document.querySelectorAll('.js-open-asset').forEach((el) => {
                el.addEventListener('click', function () {
                    sendTelemetry({
                        learning_asset_id: Number(this.dataset.assetId),
                        event_type: 'open_asset',
                        progress_percent: null
                    });
                });
            });

            document.querySelectorAll('.js-audio-telemetry').forEach((audio) => {
                let lastSentAt = 0;
                audio.addEventListener('timeupdate', function () {
                    const now = Date.now();
                    if ((now - lastSentAt) < 12000) {
                        return;
                    }
                    lastSentAt = now;
                    const duration = Number(audio.dataset.durationSeconds || Math.floor(audio.duration || 0) || 0);
                    const position = Math.floor(audio.currentTime || 0);
                    const percent = duration > 0 ? Math.min(100, (position / duration) * 100) : null;
                    sendTelemetry({
                        learning_asset_id: Number(audio.dataset.assetId),
                        event_type: 'listen',
                        position_seconds: position,
                        duration_seconds: duration > 0 ? duration : null,
                        progress_percent: percent
                    });
                });
            });

            const pdfStates = {};
            async function setupPdfViewer(assetId) {
                if (pdfStates[assetId]) {
                    return pdfStates[assetId];
                }
                if (!window.pdfjsLib) {
                    return null;
                }

                const canvas = document.getElementById('pdf-canvas-' + assetId);
                if (!canvas) return null;
                const pdfUrl = canvas.dataset.pdfUrl;
                const loadingTask = window.pdfjsLib.getDocument(pdfUrl);
                const pdfDoc = await loadingTask.promise;
                const state = {
                    assetId: Number(assetId),
                    pdfDoc: pdfDoc,
                    pageNum: 1,
                    totalPages: Number(pdfDoc.numPages || 1),
                    canvas: canvas,
                    pageEl: document.getElementById('pdf-page-' + assetId),
                    totalEl: document.getElementById('pdf-total-' + assetId),
                };
                if (state.totalEl) {
                    state.totalEl.textContent = String(state.totalPages);
                }
                pdfStates[assetId] = state;
                await renderPdfPage(state, 1);
                return state;
            }

            async function renderPdfPage(state, pageNum) {
                const safePage = Math.max(1, Math.min(state.totalPages, pageNum));
                const page = await state.pdfDoc.getPage(safePage);
                const viewport = page.getViewport({ scale: 1.0 });
                const canvas = state.canvas;
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                await page.render({ canvasContext: context, viewport: viewport }).promise;
                state.pageNum = safePage;
                if (state.pageEl) {
                    state.pageEl.textContent = String(safePage);
                }
                sendTelemetry({
                    learning_asset_id: state.assetId,
                    event_type: 'view_page',
                    current_page: safePage,
                    total_pages: state.totalPages,
                    progress_percent: Math.min(100, (safePage / Math.max(1, state.totalPages)) * 100)
                });
            }

            document.querySelectorAll('.js-open-pdf-viewer').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    const assetId = this.dataset.assetId;
                    const wrap = document.getElementById('pdf-viewer-wrap-' + assetId);
                    if (!wrap) return;
                    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
                    if (wrap.style.display === 'block') {
                        await setupPdfViewer(assetId);
                    }
                });
            });

            document.querySelectorAll('.js-pdf-prev').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    const assetId = this.dataset.assetId;
                    const state = await setupPdfViewer(assetId);
                    if (!state) return;
                    await renderPdfPage(state, state.pageNum - 1);
                });
            });

            document.querySelectorAll('.js-pdf-next').forEach((btn) => {
                btn.addEventListener('click', async function () {
                    const assetId = this.dataset.assetId;
                    const state = await setupPdfViewer(assetId);
                    if (!state) return;
                    await renderPdfPage(state, state.pageNum + 1);
                });
            });
        })();
    </script>
@endsection
