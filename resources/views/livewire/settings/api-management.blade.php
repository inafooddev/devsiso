<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" x-data="{ activeTab: 'endpoints' }">
    <x-slot name="title">Pengaturan API</x-slot>

    @if (session()->has('success'))
        <div class="alert alert-success shrink-0 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 lg:gap-6 shrink-0">
        <!-- KPI: Total Endpoints -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Endpoints</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-chart-bar class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ $totalEndpoints }}</div>
        </div>
        
        <!-- KPI: Total Clients -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-secondary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total API Clients</h3>
                <div class="w-8 h-8 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-users class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-secondary">{{ $totalClients }}</div>
        </div>

        <!-- KPI: Active Tokens -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Active Tokens</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-key class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ $totalTokens }}</div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col gap-4 bg-base-200/30">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
                <div class="shrink-0">
                    <h2 class="text-base md:text-lg font-bold">API Management</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5" x-text="activeTab === 'endpoints' ? 'Daftar endpoint yang tersedia' : 'Kelola identitas klien dan hak akses token'"></p>
                </div>
                
                {{-- Tabs Menu --}}
                <div role="tablist" class="tabs tabs-boxed bg-base-300/50 p-1 shrink-0">
                    <a role="tab" class="tab font-semibold transition-colors duration-200" :class="{ 'bg-primary text-primary-content shadow': activeTab === 'endpoints' }" @click="activeTab = 'endpoints'">Available APIs</a>
                    <a role="tab" class="tab font-semibold transition-colors duration-200" :class="{ 'bg-primary text-primary-content shadow': activeTab === 'builder' }" @click="activeTab = 'builder'">Dynamic API Builder</a>
                    <a role="tab" class="tab font-semibold transition-colors duration-200" :class="{ 'bg-primary text-primary-content shadow': activeTab === 'clients' }" @click="activeTab = 'clients'">Manage API Clients</a>
                    <a role="tab" class="tab font-semibold transition-colors duration-200" :class="{ 'bg-primary text-primary-content shadow': activeTab === 'panduan' }" @click="activeTab = 'panduan'">Panduan</a>
                </div>
            </div>

            {{-- Action Bar (Only for Clients Tab) --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full" x-show="activeTab === 'clients'" x-transition>
                <form wire:submit.prevent="createClient" class="flex flex-wrap items-center gap-2 w-full sm:w-auto bg-base-100 p-2 rounded-lg border border-base-300 shadow-sm">
                    <input type="text" wire:model="newClientName" class="input input-sm input-bordered w-full sm:w-64" placeholder="Client Name (e.g. Postman)" required />
                    <button type="submit" class="btn btn-sm btn-primary">
                        <x-heroicon-s-plus-circle class="w-4 h-4" />
                        Create Client
                    </button>
                    @error('newClientName') <span class="text-error text-xs ml-2 w-full">{{ $message }}</span> @enderror
                </form>
            </div>

            {{-- Action Bar (Only for Builder Tab) --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full" x-show="activeTab === 'builder'" x-transition x-cloak>
                <button wire:click="openBuilderModal" class="btn btn-sm btn-primary">
                    <x-heroicon-s-plus class="w-4 h-4"/> Buat API Baru
                </button>
            </div>

        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            
            {{-- TAB: ENDPOINTS --}}
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap" x-show="activeTab === 'endpoints'">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-20">Method</th>
                        <th>Endpoint URL</th>
                        <th>Required Ability</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($this->availableEndpoints as $endpoint)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td><span class="badge badge-success font-bold text-xs">{{ $endpoint['method'] }}</span></td>
                        <td class="font-mono text-xs">{{ url($endpoint['url']) }}</td>
                        <td><span class="badge badge-outline badge-sm">{{ $endpoint['ability'] }}</span></td>
                        <td class="text-sm text-base-content/80">{{ $endpoint['description'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-base-content/50 py-4 italic">No API endpoints configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- TAB: CLIENTS --}}
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap" x-show="activeTab === 'clients'" x-cloak>
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">ID</th>
                        <th>Client Name</th>
                        <th>Active Tokens</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($clients as $client)
                    <tr class="hover:bg-base-200/50 transition-colors group">
                        <td class="font-bold">{{ $client->id }}</td>
                        <td class="font-bold text-primary">{{ $client->name }}</td>
                        <td class="whitespace-normal">
                            @forelse ($client->tokens as $token)
                                <div class="bg-base-200 border border-base-300 p-2 rounded mb-2 text-xs w-max max-w-full">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <strong class="text-secondary">{{ $token->name }}</strong>
                                            <span class="text-base-content/50 ml-2">Created: {{ $token->created_at->format('Y-m-d H:i') }}</span>
                                            @if($token->plain_text_token)
                                            <div class="mt-2 mb-1 font-mono text-[10px] sm:text-xs bg-base-300 p-1.5 px-3 rounded-lg break-all select-all border border-base-content/20 flex items-center justify-between gap-3 group" x-data="{ copied: false }">
                                                <span class="text-primary font-bold">{{ $token->plain_text_token }}</span>
                                                <button @click="navigator.clipboard.writeText('{{ $token->plain_text_token }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })" 
                                                        class="btn btn-xs btn-ghost btn-square shrink-0 group-hover:bg-primary/10" 
                                                        :class="copied ? 'text-success' : 'text-base-content/50 hover:text-primary'"
                                                        title="Copy Token">
                                                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2 2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                    <svg x-show="copied" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                        <button wire:click="revokeToken({{ $client->id }}, {{ $token->id }})" class="btn btn-xs btn-error btn-outline" onclick="confirm('Revoke token {{ $token->name }}?') || event.stopImmediatePropagation()">Revoke</button>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($token->abilities as $ability)
                                            <span class="badge badge-xs badge-info">{{ $ability }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <span class="text-xs text-base-content/50 italic">No active tokens</span>
                            @endforelse
                        </td>
                        <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openGenerateModal({{ $client->id }}, '{{ addslashes($client->name) }}')" class="btn btn-sm btn-ghost text-primary hover:bg-primary/10 btn-square" title="Generate Token">
                                    <x-heroicon-s-key class="w-4 h-4"/>
                                </button>
                                <button wire:click="deleteClient({{ $client->id }})" class="btn btn-sm btn-ghost text-error hover:bg-error/10 btn-square" title="Hapus Client" onclick="confirm('Delete client? ALL tokens will be revoked immediately.') || event.stopImmediatePropagation()">
                                    <x-heroicon-s-trash class="w-4 h-4"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-base-content/50 py-8 italic">No API clients found. Create one first.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- TAB: BUILDER --}}
            <div x-show="activeTab === 'builder'" x-cloak class="p-4">
                <!-- warning -->
                <div class="alert alert-warning shadow-sm mb-4 border border-warning/30">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6"/>
                    <div>
                        <h3 class="font-bold text-sm">SECURITY WARNING</h3>
                        <div class="text-xs">Menjalankan raw SQL query sangat berisiko. Pastikan hanya Super Admin yang bisa mengakses fitur ini.</div>
                    </div>
                </div>

                <div class="border border-base-300 rounded-lg overflow-hidden">
                    <table class="table table-sm table-zebra table-pin-rows w-full">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80">
                            <tr>
                                <th class="w-1/4">Endpoint</th>
                                <th class="w-1/4">Description</th>
                                <th class="w-2/4">Query</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($dynamicApis as $api)
                            <tr>
                                <td class="align-top">
                                    <div class="font-mono text-xs mb-1">
                                        <span class="badge badge-success font-bold text-[10px]">{{ $api->method ?? 'GET' }}</span>
                                    </div>
                                    <div class="font-bold text-primary">/api/{{ $api->endpoint }}</div>
                                </td>
                                <td class="align-top text-sm">{{ $api->description }}</td>
                                <td class="align-top">
                                    <div class="bg-base-300 p-3 rounded-lg text-xs font-mono max-h-32 overflow-y-auto whitespace-pre-wrap border border-base-content/10">{{ $api->sql_query }}</div>
                                </td>
                                <td class="align-top text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button wire:click="editDynamicApi({{ $api->id }})" class="btn btn-sm btn-ghost text-info btn-square" title="Edit">
                                            <x-heroicon-s-pencil class="w-4 h-4"/>
                                        </button>
                                        <button wire:click="deleteDynamicApi({{ $api->id }})" class="btn btn-sm btn-ghost text-error btn-square" title="Delete" onclick="confirm('Yakin ingin menghapus endpoint API ini?') || event.stopImmediatePropagation()">
                                            <x-heroicon-s-trash class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-base-content/50 italic">
                                    <x-heroicon-o-circle-stack class="w-12 h-12 mx-auto mb-3 opacity-30"/>
                                    Belum ada Dynamic API yang dibuat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB: PANDUAN --}}
            <div x-show="activeTab === 'panduan'" x-cloak class="p-6 overflow-y-auto bg-base-200/30">
                <div class="prose prose-sm md:prose-base max-w-none text-base-content">
                    <h2 class="text-2xl font-bold border-b border-base-300 pb-2 mb-6">Panduan Pembuatan Dynamic API</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-bold text-primary flex items-center gap-2 mb-3">
                                <x-heroicon-o-book-open class="w-5 h-5"/> 1. Membuat API GET (Ambil Data)
                            </h3>
                            <p class="text-sm mb-3">Gunakan method <strong>GET</strong> untuk mengambil data dari database. SQL Query wajib diawali dengan <code>SELECT</code>.</p>
                            <div class="bg-base-300 p-4 rounded-xl border border-base-content/10 mb-4">
                                <p class="text-xs font-bold text-base-content/50 uppercase mb-2">Contoh SQL Query:</p>
                                <pre class="bg-base-100 p-3 rounded text-xs font-mono">SELECT * FROM users WHERE status = 'active'</pre>
                            </div>

                            <h4 class="font-bold text-sm mb-2 text-secondary">Parameter Binding (Filter Data)</h4>
                            <p class="text-sm mb-2">Anda bisa membuat parameter dinamis menggunakan awalan titik dua (<code>:</code>). Parameter ini akan dibaca otomatis dari URL.</p>
                            <div class="bg-base-300 p-4 rounded-xl border border-base-content/10">
                                <pre class="bg-base-100 p-3 rounded text-xs font-mono mb-2">SELECT id, name FROM users WHERE id = :id_user</pre>
                                <p class="text-xs text-base-content/70"><strong>Cara memanggil di Postman/Browser:</strong><br/>
                                <code class="text-primary font-bold">GET /api/endpoint?id_user=123</code></p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-success flex items-center gap-2 mb-3">
                                <x-heroicon-o-pencil-square class="w-5 h-5"/> 2. Membuat API POST/PUT/DELETE
                            </h3>
                            <p class="text-sm mb-3">Gunakan method ini untuk mengubah isi database (Insert, Update, Delete).</p>
                            <div class="bg-base-300 p-4 rounded-xl border border-base-content/10 mb-4">
                                <p class="text-xs font-bold text-base-content/50 uppercase mb-2">Contoh SQL Insert (POST):</p>
                                <pre class="bg-base-100 p-3 rounded text-xs font-mono mb-2">INSERT INTO products (name, price) VALUES (:name, :price)</pre>
                                <p class="text-xs text-base-content/70"><strong>Kirim Body (JSON) di Postman:</strong></p>
                                <pre class="bg-base-100 p-3 rounded text-xs font-mono mt-1">{
  "name": "Kopi Susu",
  "price": 15000
}</pre>
                            </div>
                        </div>
                    </div>

                    <div class="divider my-8"></div>

                    <div class="alert alert-info shadow-sm bg-info/10 text-info-content border border-info/30 rounded-xl">
                        <x-heroicon-s-shield-check class="w-8 h-8 text-info shrink-0"/>
                        <div>
                            <h3 class="font-bold text-lg text-info">Keamanan & Token Authorization</h3>
                            <p class="text-sm mt-1">Seluruh API yang Anda buat menggunakan Dynamic Builder secara otomatis dilindungi oleh sistem token <strong>Sanctum</strong>.</p>
                            <ul class="list-disc pl-5 mt-2 text-sm space-y-1">
                                <li>Semua URL memiliki prefix <code>/api/</code></li>
                                <li>Klien wajib menyertakan Header: <code>Authorization: Bearer {TOKEN}</code></li>
                                <li>Token harus memiliki hak akses (ability) <code>manage:api</code> atau di-generate secara spesifik jika ada pengaturan tambahan nantinya.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Generate Token Modal -->
    @if ($showGenerateModal)
    <div class="modal modal-open bg-base-300/80 backdrop-blur-sm">
        <div class="modal-box border border-base-300">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <x-heroicon-s-key class="w-5 h-5 text-primary"/>
                Generate Token: <span class="text-primary">{{ $newClientForToken }}</span>
            </h3>
            <div class="divider mt-2 mb-4"></div>
            <p class="text-sm mb-4">Pilih hak akses (abilities) yang diizinkan untuk token ini:</p>
            
            <div class="flex flex-col gap-2">
                @foreach($this->uniqueAbilities as $abilityKey => $abilityLabel)
                <label class="label cursor-pointer justify-start gap-4 p-2 hover:bg-base-200 rounded border border-base-200">
                    <input type="checkbox" wire:model="abilities.{{ $abilityKey }}" class="checkbox checkbox-sm checkbox-primary" />
                    <span class="label-text font-semibold">{{ $abilityLabel }}</span> 
                </label>
                @endforeach
            </div>
            
            <div class="modal-action">
                <button wire:click="closeGenerateModal" class="btn btn-ghost">Batal</button>
                <button wire:click="generateToken" class="btn btn-primary">Generate Token</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Token Result Modal -->
    @if ($showTokenModal)
    <div class="modal modal-open bg-base-300/80 backdrop-blur-sm">
        <div class="modal-box shadow-2xl border border-success/30">
            <h3 class="font-bold text-xl text-success flex items-center gap-2">
                <x-heroicon-s-check-circle class="w-6 h-6"/>
                Token Generated Successfully!
            </h3>
            <div class="divider mt-2 mb-4"></div>
            <p class="text-sm mb-4">
                Please copy this token now. For your security, <strong class="text-error">it won't be shown again</strong>.
                You can use this token for the client: <strong class="text-primary">{{ $newClientForToken }}</strong>
            </p>
            
            <div class="bg-base-300 p-4 rounded-xl font-mono text-sm shadow-inner mb-6 border border-base-content/10 relative group flex items-center justify-between gap-4" x-data="{ copied: false }">
                <div class="absolute -top-3 left-3 bg-base-100 text-[10px] px-2 font-bold rounded-full border border-base-content/10">Personal Access Token</div>
                
                <div class="break-all select-all flex-1">{{ $newTokenPlaintext }}</div>
                
                <button @click="navigator.clipboard.writeText('{{ $newTokenPlaintext }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })" 
                        class="btn btn-sm btn-ghost btn-square shrink-0" 
                        :class="copied ? 'text-success' : 'text-base-content/70 hover:text-base-content'"
                        title="Copy to clipboard">
                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2 2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    <svg x-show="copied" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeTokenModal" class="btn btn-primary w-full">I have copied the token</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Builder Modal -->
    @if($showBuilderModal)
    <div class="modal modal-open bg-base-300/80 backdrop-blur-sm z-50">
        <div class="modal-box max-w-4xl border border-base-300">
            <h3 class="font-bold text-xl mb-6 border-b border-base-200 pb-3 flex items-center gap-2">
                <x-heroicon-o-command-line class="w-6 h-6 text-primary"/>
                {{ $isEditBuilder ? 'Edit API Endpoint' : 'Buat API Endpoint Baru' }}
            </h3>
            
            <form wire:submit.prevent="saveDynamicApi">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">HTTP Method</span></label>
                        <select wire:model="builderMethod" class="select select-bordered w-full font-bold text-primary">
                            <option value="GET">GET (Select)</option>
                            <option value="POST">POST (Insert/Execute)</option>
                            <option value="PUT">PUT (Update)</option>
                            <option value="DELETE">DELETE (Delete)</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Endpoint Name</span></label>
                        <div class="flex">
                            <span class="bg-base-200 px-3 flex items-center border border-r-0 border-base-content/20 rounded-l-lg font-mono text-sm">/api/</span>
                            <input type="text" wire:model="builderEndpoint" class="input input-bordered w-full rounded-l-none font-mono text-primary font-bold" placeholder="laporan-harian" />
                        </div>
                        @error('builderEndpoint') <span class="text-error text-xs mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Description</span></label>
                        <input type="text" wire:model="builderDescription" class="input input-bordered w-full" placeholder="Penjelasan API..." />
                        @error('builderDescription') <span class="text-error text-xs mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-control mb-6">
                    <label class="label">
                        <span class="label-text font-bold flex items-center gap-1"><x-heroicon-o-circle-stack class="w-4 h-4"/> SQL Query (Raw)</span>
                    </label>
                    <textarea wire:model="builderSqlQuery" class="textarea textarea-bordered h-64 font-mono text-sm leading-relaxed" placeholder="SELECT&#10;  a.id,&#10;  a.name&#10;FROM my_table a&#10;WHERE a.status = 'active'"></textarea>
                    @error('builderSqlQuery') <span class="text-error text-xs mt-2 font-bold bg-error/10 p-2 rounded">{{ $message }}</span> @enderror
                    <span class="label-text-alt text-warning mt-2 font-semibold flex items-center gap-1">
                        <x-heroicon-s-information-circle class="w-4 h-4"/>
                        Jika GET, Query MUST begin with SELECT. Jika POST/PUT/DELETE, raw statement akan dieksekusi menggunakan DB::statement().
                    </span>
                </div>

                <div class="modal-action border-t border-base-200 pt-4">
                    <button type="button" wire:click="closeBuilderModal" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <x-heroicon-s-document-check class="w-5 h-5"/>
                        Simpan API
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
