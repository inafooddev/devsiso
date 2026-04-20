<div>
    <x-slot name="title">Titik Distribusi</x-slot>

    {{-- 
      Container ini akan mengambil tinggi penuh layar (viewport height) 
      dikurangi tinggi navbar Anda (misal: 4rem atau 64px). 
      Sesuaikan 'calc(100vh - 4rem)' jika perlu.
    --}}
    <div class="relative" style="height: calc(100vh - 4rem);">
        @if($iframeUrl)
            <iframe
                src="{{ $iframeUrl }}"
                frameborder="0"
                width="100%"
                height="100%"
                allowtransparency
            ></iframe>
        @else
            <div class="flex items-center justify-center h-full">
                <div class="text-center text-gray-500">
                    <p class="text-lg font-semibold">Gagal memuat dashboard.</p>
                    <p>Pastikan METABASE_SITE_URL dan METABASE_SECRET_KEY sudah diatur.</p>
                </div>
            </div>
        @endif
    </div>
</div>