@extends('layouts.app')
@section('title', 'الحضور والانصراف')

@section('content')
<div class="max-w-md mx-auto space-y-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        {{-- تُرسم الحالة (دخول/خروج) من الجافاسكريبت حتى تعمل بدون إنترنت --}}
        <div id="att-root" class="min-h-[12rem] flex items-center justify-center text-slate-400 text-sm">جارٍ التحميل...</div>
    </div>

    <p class="text-xs text-slate-400 text-center leading-6">
        إذا انقطع الإنترنت يبقى الحضور والانصراف شغّال: الوقت بيتسجل على جهازك لحظة الضغط وبيتزامن تلقائياً لما يرجع الاتصال.
    </p>
</div>
@endsection

@push('scripts')
<script>
    AttendanceOffline.mountPage({
        serverOpen: @json($openSession ? [
            'uuid' => $openSession->client_uuid,
            'started_at' => $openSession->started_at->toIso8601String(),
        ] : null),
    });
</script>
@endpush
