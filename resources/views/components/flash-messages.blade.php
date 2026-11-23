@if (session('success'))
    <div data-auto-dismiss class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" role="alert">
        <span class="font-semibold">Success:</span> {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div data-auto-dismiss class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" role="alert">
        <span class="font-semibold">Error:</span> {{ session('error') }}
    </div>
@endif

@if (session('warning'))
    <div data-auto-dismiss class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm" role="alert">
        <span class="font-semibold">Notice:</span> {{ session('warning') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" role="alert">
        <p class="font-semibold">Please correct the following errors:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
