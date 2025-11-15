@php($imgs = $images ?? [])
@if (!empty($imgs))
    <div style="display:flex;flex-wrap:wrap;gap:16px;">
        @foreach ($imgs as $url)
            <a href="{{ $url }}" target="_blank" style="display:inline-block;border:1px solid #e5e7eb;padding:8px;border-radius:10px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
                <img src="{{ $url }}" alt="Prescription" style="height:220px;object-fit:cover;border-radius:8px;">
            </a>
        @endforeach
    </div>
@else
    <div>No images attached to this request.</div>
@endif


