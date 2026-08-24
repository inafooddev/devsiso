{{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative" style="isolation: auto;" wire:loading.class="opacity-60 pointer-events-none">
            <div wire:loading wire:target="search, filter_type, setFilter, updatingSearch, updatingFilterType, filter_region_code, filter_area_code, filter_branch_name" 
                 class="absolute inset-0 flex items-center justify-center bg-base-100/70 z-30 backdrop-blur-[1px]">
                <div class="flex flex-col items-center gap-2">
                    <span class="loading loading-dots loading-lg text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/50">Memuat data...</span>
                </div>
            </div>
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Region</th>
                        <th>Cabang</th>
                        <th class="text-center">Custno</th>
                        <th>Customer</th>
                        <th class="text-center">Foto KTP</th>
                        <th class="text-center">Foto Toko</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Check SPM</th>
                        <th class="text-center">Validasi</th>
                        <th>Note SPM</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">

                @foreach ($outlets as $index => $row)
                    <tr class="group text-[11px] hover:relative hover:z-40" wire:key="rwo-{{ $row->id }}">
                        <td class="text-center">
                            <span class="font-semibold text-base-content/40">{{ $outlets->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div class="max-w-[120px]">
                                <span class="font-bold text-base-content/85 group-hover:text-primary transition-colors block truncate" title="{{ $row->region_name }}">
                                    {{ $row->region_name }}
                                </span>
                                <div class="text-[10px] text-base-content/40 font-semibold uppercase tracking-wider mt-0.5 truncate" title="{{ $row->area_name }}">
                                    {{ $row->area_name }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[160px]">
                                <span class="font-medium text-base-content/70 block truncate" title="{{ $row->branch_name }}">
                                    {{ $row->branch_name }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="badge badge-sm badge-outline border-base-300 text-secondary font-mono font-bold rounded-lg px-2 text-[11px]">
                                    {{ $row->customer_code }}
                                </span>
                                @if($row->eskalink_code)
                                    <span class="text-[9px] text-base-content/50 font-mono mt-0.5">{{ $row->eskalink_code }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[200px]">
                                <span class="font-bold text-base-content/80 block truncate uppercase" title="{{ $row->customer_name }}">{{ $row->customer_name }}</span>
                                <p class="text-[10px] text-base-content/40 truncate" title="{{ $row->alamat }}">{{ $row->alamat }}</p>
                            </div>
                        </td>

                          <td class="text-center">
                             @if($row->foto_ktp)
                                  <div class="flex justify-center">
                                      <div class="w-8 h-8 rounded-xl bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto KTP" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_ktp) }}', title: 'Foto KTP' })">
                                          <x-heroicon-s-photo class="w-5 h-5" />
                                      </div>
                                  </div>
                             @else
                                 <span class="text-[11px] text-base-content/30 italic">Tidak ada</span>
                             @endif
                          </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- GPS --}}
                                @if($row->foto_toko)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto GPS" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko) }}', title: 'Foto Toko (GPS)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Toko by GPS (Belum ada)" wire:click="openDetailModal({{ $row->id }})">G</div>
                                @endif

                                {{-- Depan --}}
                                @if($row->foto_toko2)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto Tampak Depan" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko2) }}', title: 'Foto Toko (Tampak Depan)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Tampak Depan (Belum ada)" wire:click="openDetailModal({{ $row->id }})">D</div>
                                @endif

                                {{-- Dalam --}}
                                @if($row->foto_toko3)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto Tampak Dalam" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko3) }}', title: 'Foto Toko (Tampak Dalam)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Tampak Dalam (Belum ada)" wire:click="openDetailModal({{ $row->id }})">Di</div>
                                @endif
                            </div>
                         </td>
                         <td class="text-center">
                              @if($row->status === 'Complete')
                                   <button wire:click="openStatusModal({{ $row->id }})"
                                           title="Klik untuk lihat detail kelengkapan"
                                           class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2 cursor-pointer hover:bg-success/25 hover:ring-1 hover:ring-success/30 transition-all duration-150">
                                       <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                       <span>Complete</span>
                                   </button>
                              @else
                                   <button wire:click="openStatusModal({{ $row->id }})"
                                           title="Klik untuk lihat detail kelengkapan"
                                           class="inline-flex items-center gap-1 text-[10px] font-bold text-warning bg-warning/15 rounded-lg py-1 px-2 cursor-pointer hover:bg-warning/25 hover:ring-1 hover:ring-warning/30 transition-all duration-150">
                                       <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                       <span>Not Complete</span>
                                   </button>
                              @endif
                          </td>
                          <td class="text-center">
                              @if($row->is_valid)
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2">
                                      <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                      <span>Sudah</span>
                                  </span>
                              @else
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-error bg-error/15 rounded-lg py-1 px-2">
                                      <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                      <span>Belum</span>
                                  </span>
                              @endif
                          </td>
                          <td class="text-center">
                              @if($row->isFinalized())
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2" title="Difinalisasi oleh Finance">
                                      <x-heroicon-s-lock-closed class="w-3.5 h-3.5" />
                                      <span>Final</span>
                                  </span>
                              @elseif($row->finance_noted_at)
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-warning bg-warning/15 rounded-lg py-1 px-2" title="Revisi/Catatan dari Finance">
                                      <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                      <span>Revisi</span>
                                  </span>
                              @else
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-base-content/40 bg-base-200 rounded-lg py-1 px-2">
                                      <x-heroicon-s-clock class="w-3.5 h-3.5" />
                                      <span>Menunggu</span>
                                  </span>
                              @endif
                          </td>
                         <td>
                             @if($row->keterangan)
                                 <div class="max-w-[120px] truncate text-[11px] text-base-content/60" title="{{ $row->keterangan }}">
                                     {{ $row->keterangan }}
                                 </div>
                             @else
                                 <span class="text-[11px] text-base-content/30 italic">-</span>
                             @endif
                         </td>
                         <td>
                             <div class="flex items-center justify-center gap-1">
                                <button wire:click="openDetailModal({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-secondary hover:bg-secondary/10 transition-all duration-200" title="Detail">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                @if($row->latitude && $row->longitude)
                                <a href="https://www.google.com/maps?q={{ (float)$row->latitude }},{{ (float)$row->longitude }}" target="_blank"
                                   class="btn btn-ghost btn-xs btn-square rounded-lg text-accent hover:bg-accent/10 transition-all duration-200" title="Buka Google Maps">
                                    <x-heroicon-s-map-pin class="w-4 h-4" />
                                </a>
                                @endif
                                
                                @if(!$row->isFinalized())
                                    @canEdit('rwo.index')
                                    <button wire:click="openEditModal({{ $row->id }})" 
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    @endcanEdit
                                    @canDelete('rwo.index')
                                    <button wire:click="confirmDelete({{ $row->id }})" 
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                    @endcanDelete
                                @endif

                                @if($row->isFinalized() && auth()->user()->hasRole('admin'))
                                <button wire:click="unfinalizeOutlet({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Buka Kunci (Admin)">
                                    <x-heroicon-s-lock-open class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if(count($outlets) === 0)
                    <tr>
                        <td colspan="11" class="text-center py-8 text-base-content/40">Tidak ada data RWO ditemukan.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>