<template>
  <AppLayout title="Realisasi Kegiatan">
    <div class="space-y-5">
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <div v-if="successMessage" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <svg class="h-5 w-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="text-sm font-medium text-emerald-800">{{ successMessage }}</p>
          </div>
        </div>
      </transition>

      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <svg class="h-5 w-5 flex-shrink-0 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-10.5a.75.75 0 00-1.5 0v4a.75.75 0 001.5 0v-4zm0 6.5a.75.75 0 10-1.5 0 .75.75 0 001.5 0z" clip-rule="evenodd" />
            </svg>
            <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
          </div>
        </div>
      </transition>

      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <div v-if="warningMessage" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <svg class="h-5 w-5 flex-shrink-0 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.981-1.742 2.981H4.42c-1.53 0-2.492-1.647-1.743-2.98l5.58-9.92zM11 7a1 1 0 10-2 0v3a1 1 0 102 0V7zm-1 6a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 13z" clip-rule="evenodd" />
            </svg>
            <p class="text-sm font-medium text-amber-800">{{ warningMessage }}</p>
          </div>
        </div>
      </transition>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
          <div>
            <label class="label">Cari</label>
            <input
              v-model="searchQuery"
              type="text"
              class="input w-full"
              placeholder="Cari pilar, program, atau kegiatan..."
            />
          </div>

          <div>
            <label class="label">Filter Pilar</label>
            <select v-model="selectedPilarId" class="input w-full">
              <option value="">Semua pilar</option>
              <option v-for="item in pilarOptions" :key="item.id" :value="String(item.id)">
                {{ item.nama }}
              </option>
            </select>
          </div>

          <div>
            <label class="label">Filter Program</label>
            <select v-model="selectedProgramId" class="input w-full">
              <option value="">Semua program</option>
              <option v-for="item in programOptions" :key="item.id" :value="String(item.id)">
                {{ item.nama }}
              </option>
            </select>
          </div>

          <div>
            <label class="label">Filter Kegiatan</label>
            <select v-model="selectedKegiatanFilterId" class="input w-full">
              <option value="">Semua kegiatan</option>
              <option v-for="item in kegiatanFilterOptions" :key="item.id" :value="String(item.id)">
                {{ item.nama }}
              </option>
            </select>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p>
            Menampilkan <span class="font-semibold text-slate-800">{{ filteredKegiatans.length }}</span> kegiatan.
            <span v-if="isSuperadmin">Superadmin hanya dapat melihat data realisasi.</span>
            <span v-else>Data yang tampil mengikuti kegiatan milik user yang sedang login.</span>
          </p>
          <button
            type="button"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100"
            @click="resetFilters"
          >
            Reset Filter
          </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3">
          <div>
            <label class="label">Filter Capaian Uang</label>
            <select v-model="selectedAchievementFilter" class="input w-full min-w-[220px]">
              <option value="all">Semua</option>
              <option value="low">Rendah (&lt; 50%)</option>
              <option value="medium">Sedang (50% - 99.99%)</option>
              <option value="high">Baik (100% - 120%)</option>
              <option value="over">Melebihi (&gt; 120%)</option>
            </select>
          </div>
          <button
            type="button"
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
            @click="exportSummaryCsv"
          >
            Export Ringkasan (CSV)
          </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
          <span class="text-slate-500 font-medium">Legenda Capaian Uang:</span>
          <span class="rounded-full bg-red-50 px-2.5 py-1 font-semibold text-red-700">Rendah (&lt; 50%)</span>
          <span class="rounded-full bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">Sedang (50% - 99.99%)</span>
          <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">Baik (100% - 120%)</span>
          <span class="rounded-full bg-violet-50 px-2.5 py-1 font-semibold text-violet-700">Melebihi (&gt; 120%)</span>
        </div>
      </div>

      <div v-if="groupedKegiatans.length" class="space-y-6">
        <section
          v-for="pilar in groupedKegiatans"
          :key="`pilar-${pilar.id}`"
          class="rounded-2xl border p-4"
          :style="pilarCardStyle(pilar)"
        >
          <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white/90 px-4 py-3" :style="pilarHeaderStyle(pilar)">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pilar</p>
              <h3 class="text-lg font-bold" :style="{ color: pilarDisplayColor(pilar) }">{{ pilar.nama }}</h3>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Anggaran</p>
              <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(pilar.totalAnggaran) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Realisasi</p>
              <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(pilar.totalRealisasi) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Sisa</p>
              <p class="mt-1 text-sm font-semibold whitespace-nowrap" :class="pilar.sisaAnggaran < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(pilar.sisaAnggaran) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Capaian</p>
              <p class="mt-1 text-sm font-semibold text-slate-800">{{ Number(pilar.persentase || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</p>
            </div>
          </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ pilar.totalKegiatan }} kegiatan</span>
          </div>

          

          <div class="mt-4 space-y-4">
            <section
              v-for="program in pilar.programs"
              :key="`program-${program.id}`"
              class="rounded-2xl border border-slate-200 bg-white p-4"
            >
              <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Program</p>
                  <h4 class="text-base font-bold text-slate-800">{{ program.nama }}</h4>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <p class="text-[11px] uppercase tracking-wide text-slate-500">Anggaran</p>
                  <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(program.totalAnggaran) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <p class="text-[11px] uppercase tracking-wide text-slate-500">Realisasi</p>
                  <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(program.totalRealisasi) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <p class="text-[11px] uppercase tracking-wide text-slate-500">Sisa</p>
                  <p class="mt-1 text-sm font-semibold whitespace-nowrap" :class="program.sisaAnggaran < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(program.sisaAnggaran) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <p class="text-[11px] uppercase tracking-wide text-slate-500">Capaian</p>
                  <p class="mt-1 text-sm font-semibold text-slate-800">{{ Number(program.persentase || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</p>
                </div>
              </div>
                <div class="flex items-center gap-2">
                  <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 border border-slate-200">{{ program.kegiatans.length }} kegiatan</span>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="toggleProgramLokasiCollapse(program.kegiatans)"
                  >
                    <svg
                      class="h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                    </svg>
                    {{ allLokasiCollapsedInProgram(program.kegiatans) ? 'Tampilkan Semua Lokasi' : 'Sembunyikan Semua Lokasi' }}
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                    @click="toggleProgramCollapse(pilar.id, program.id)"
                  >
                    <svg
                      :class="['h-3.5 w-3.5 transition-transform', isProgramCollapsed(pilar.id, program.id) ? '-rotate-90' : 'rotate-0']"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    {{ isProgramCollapsed(pilar.id, program.id) ? 'Tampilkan' : 'Sembunyikan' }}
                  </button>
                </div>
              </div>

              <transition name="collapse-panel">
                <div v-if="!isProgramCollapsed(pilar.id, program.id)" class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white">
                  <div class="max-h-[420px] overflow-auto">
                    <table class="w-full min-w-[1200px] text-sm">
                    <thead>
                      <tr class="bg-orange-500 text-white text-xs font-medium">
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-left">Kode</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-left">Nama Kegiatan</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-right whitespace-nowrap">Anggaran</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-right whitespace-nowrap">Realisasi</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-right whitespace-nowrap">Sisa</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-center whitespace-nowrap">Capaian Uang</th>
                        <th class="sticky top-0 z-20 bg-orange-500 px-4 py-2 text-center">Aksi Cepat</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr :style="pilarHierarchyRowStyle(pilar)">
                        <td class="px-4 py-2 font-mono text-xs font-semibold" :style="{ color: pilarDisplayColor(pilar) }">PLR-{{ pilar.id }}</td>
                        <td class="px-4 py-2 font-semibold" :style="{ color: pilarDisplayColor(pilar) }">Pilar: {{ pilar.nama }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(pilar.totalAnggaran) }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(pilar.totalRealisasi) }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap" :class="pilar.sisaAnggaran < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(pilar.sisaAnggaran) }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ Number(pilar.persentase || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                        <td class="px-4 py-2 text-center text-xs text-slate-500">-</td>
                      </tr>

                      <tr class="bg-amber-50/70">
                        <td class="px-4 py-2 font-mono text-xs font-semibold text-amber-700">PRG-{{ program.id }}</td>
                        <td class="px-4 py-2">
                          <div class="border-l-2 border-amber-300 pl-3 font-semibold text-amber-800">Program: {{ program.nama }}</div>
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(program.totalAnggaran) }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(program.totalRealisasi) }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap" :class="program.sisaAnggaran < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(program.sisaAnggaran) }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ Number(program.persentase || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                        <td class="px-4 py-2 text-center text-xs text-slate-500">-</td>
                      </tr>

                      <template v-for="kegiatan in program.kegiatans" :key="`summary-group-${kegiatan.id}`">
                        <tr
                          :class="selectedKegiatanDetailId === kegiatan.id ? 'bg-blue-100/70' : 'bg-blue-50/60'"
                        >
                          <td class="px-4 py-2 font-mono text-xs font-semibold text-blue-700">KGT-{{ kegiatan.id }}</td>
                          <td class="px-4 py-2">
                            <div class="border-l-2 border-blue-200 pl-3">
                              <p class="font-medium text-blue-900">Kegiatan: {{ kegiatan.nama }}</p>
                              <p class="text-xs text-blue-700/80">{{ kegiatan.divisi?.nama || '-' }}</p>
                            </div>
                          </td>
                          <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).anggaran) }}</td>
                          <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).realisasiUang) }}</td>
                          <td class="px-4 py-2 text-right whitespace-nowrap" :class="kegiatanPerformance(kegiatan).sisaUang < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(kegiatanPerformance(kegiatan).sisaUang) }}</td>
                          <td class="px-4 py-2 text-center font-semibold" :class="metricToneClass(kegiatanPerformance(kegiatan).capaianUang)">{{ Number(kegiatanPerformance(kegiatan).capaianUang || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                          <td class="px-4 py-2">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                              <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100"
                                @click="toggleKegiatanLokasiCollapse(kegiatan.id)"
                              >
                                <svg
                                  :class="['h-3.5 w-3.5 transition-transform', isKegiatanLokasiCollapsed(kegiatan.id) ? '-rotate-90' : 'rotate-0']"
                                  fill="none"
                                  stroke="currentColor"
                                  viewBox="0 0 24 24"
                                >
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ isKegiatanLokasiCollapsed(kegiatan.id) ? 'Tampilkan Lokasi' : 'Sembunyikan Lokasi' }}
                              </button>
                              <button
                                type="button"
                                class="rounded-md border border-blue-300 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                @click="showKegiatanDetail(kegiatan.id)"
                              >
                                Detail
                              </button>
                              <button
                                v-if="!isSuperadmin"
                                type="button"
                                class="rounded-md border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100"
                                @click="showIsiRealisasi(kegiatan.id)"
                              >
                                Isi Realisasi
                              </button>
                            </div>
                          </td>
                        </tr>

                        <transition-group name="lokasi-row">
                          <tr
                            v-for="lokasi in (kegiatan.lokasis || [])"
                            v-if="!isKegiatanLokasiCollapsed(kegiatan.id)"
                            :key="`summary-lokasi-${kegiatan.id}-${lokasi.id}`"
                            class="bg-slate-50"
                          >
                            <td class="px-4 py-2 font-mono text-[11px] text-slate-500">LKS-{{ lokasi.id }}</td>
                            <td class="px-4 py-2 text-slate-700">
                              <div class="ml-6 flex items-center gap-2 border-l-2 border-slate-200 pl-3">
                                <span class="inline-block h-px w-3 bg-slate-300"></span>
                                <span><span class="text-slate-500">Lokasi:</span> {{ lokasi.lokasi || '-' }}</span>
                              </div>
                            </td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(lokasiPerformance(lokasi).anggaran) }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(lokasiPerformance(lokasi).realisasiUang) }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap" :class="lokasiPerformance(lokasi).sisaUang < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(lokasiPerformance(lokasi).sisaUang) }}</td>
                            <td class="px-4 py-2 text-center font-semibold" :class="metricToneClass(lokasiPerformance(lokasi).capaianUang)">{{ Number(lokasiPerformance(lokasi).capaianUang || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                            <td class="px-4 py-2 text-center text-xs text-slate-500">Di bawah {{ kegiatan.nama }}</td>
                          </tr>
                        </transition-group>

                        <tr
                          v-if="isKegiatanLokasiCollapsed(kegiatan.id) && (kegiatan.lokasis || []).length"
                          class="bg-slate-50/70"
                        >
                          <td colspan="7" class="px-4 py-2 text-center text-xs text-slate-500">
                            {{ (kegiatan.lokasis || []).length }} lokasi disembunyikan.
                          </td>
                        </tr>
                      </template>

                      <tr v-if="!(program.kegiatans || []).length" class="bg-white">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-400">Belum ada kegiatan pada program ini.</td>
                      </tr> 
                    </tbody>
                    </table>
                  </div>
                </div>
              </transition>

              <div v-if="!isProgramCollapsed(pilar.id, program.id) && !selectedKegiatanInProgram(program.kegiatans)" class="mt-3 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-5 text-sm text-gray-500">
                Pilih tombol "Detail" atau "Isi Realisasi" pada tabel kegiatan untuk membuka popup realisasi.
              </div>

              <transition name="modal-fade">
                <div v-if="!isProgramCollapsed(pilar.id, program.id) && selectedKegiatanInProgram(program.kegiatans)" class="fixed inset-0 z-40 p-4 sm:p-6">
                  <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="closeKegiatanModal"></div>
                  <div class="relative mx-auto flex max-h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_32px_120px_rgba(15,23,42,0.45)]">
                  <section
                    v-for="kegiatan in program.kegiatans"
                    :key="kegiatan.id"
                    v-show="selectedKegiatanDetailId === kegiatan.id"
                    class="flex min-h-0 flex-1 flex-col overflow-hidden bg-white"
                  >
          <div class="bg-gradient-to-r from-blue-900 to-cyan-700 px-6 py-5 text-white">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="w-full">
                <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.18em] text-white">
                  <span>{{ kegiatan.program?.pilar?.nama || '-' }}</span>
                  <span>•</span>
                  <span>{{ kegiatan.program?.nama || '-' }}</span>
                  <span>•</span>
                  <span>{{ kegiatan.divisi?.nama || '-' }}</span>
                </div>
                <div class="w-full gap-5 flex justify-between mt-3">
                    <div>
                        <h3 class="mt-2 text-2xl font-bold">{{ kegiatan.nama }}</h3>
                    <p class="mt-1 text-sm text-blue-50">{{ kegiatan.deskripsi || 'Tidak ada deskripsi kegiatan.' }}</p>
                    </div>
                    <div class="flex flex-wrap items-start justify-end gap-2">
                      <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-xs font-semibold transition"
                        :class="kegiatanModalTab === 'detail' ? 'border-white bg-white text-blue-900' : 'border-white/35 bg-white/10 text-white hover:bg-white/20'"
                        @click="setKegiatanModalTab('detail')"
                      >
                        Detail
                      </button>
                      <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-xs font-semibold transition"
                        :class="kegiatanModalTab === 'input' ? 'border-white bg-white text-blue-900' : 'border-white/35 bg-white/10 text-white hover:bg-white/20'"
                        @click="setKegiatanModalTab('input')"
                      >
                        Input
                      </button>
                      <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-xs font-semibold transition"
                        :class="kegiatanModalTab === 'history' ? 'border-white bg-white text-blue-900' : 'border-white/35 bg-white/10 text-white hover:bg-white/20'"
                        @click="setKegiatanModalTab('history', kegiatan)"
                      >
                        Riwayat
                      </button>
                      <button
                        type="button"
                        class="rounded-full border border-white/35 bg-white/10 px-4 py-2 text-xs font-semibold text-white transition hover:bg-white/20"
                        @click="closeKegiatanModal"
                      >
                        Tutup
                      </button>
                    </div>
                    </div>
              </div>
              
            </div>
          </div>

          <div class="flex-1 overflow-auto px-6 py-5">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Target Output</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ formatNumber(kegiatanPerformance(kegiatan).targetOutput) }} {{ kegiatan.satuan || '' }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Anggaran</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).anggaran) }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Realisasi</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).realisasiUang) }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Capaian Uang</p>
                <p class="mt-1 text-sm font-semibold" :class="metricToneClass(kegiatanPerformance(kegiatan).capaianUang)">
                  {{ Number(kegiatanPerformance(kegiatan).capaianUang || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%
                </p>
              </div>
            </div>

            <div v-if="kegiatanModalTab === 'detail'" class="mt-5 overflow-auto max-h-[420px] rounded-xl border border-slate-200 bg-white">
              <table class="min-w-[1280px] w-full text-sm">
                <thead class="bg-slate-100 text-slate-600">
                  <tr>
                    <th class="sticky top-0 left-0 z-30 min-w-[90px] bg-slate-100 px-3 py-2 text-left font-semibold">Level</th>
                    <th class="sticky top-0 left-[90px] z-30 min-w-[220px] bg-slate-100 px-3 py-2 text-left font-semibold">Nama</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Target Output</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Anggaran</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Realisasi Output</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Realisasi Uang</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Sisa Output</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Sisa Uang</th>
                    <th class="sticky top-0 z-20 px-3 py-2 text-right font-semibold whitespace-nowrap">Capaian Output</th>
                    <th class="sticky top-0 right-0 z-30 bg-slate-100 px-3 py-2 text-right font-semibold whitespace-nowrap">Capaian Uang</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr class="bg-blue-50/50">
                    <td class="sticky left-0 z-10 bg-blue-50/90 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-blue-700">Kegiatan</td>
                    <td class="sticky left-[90px] z-10 bg-blue-50/90 px-3 py-2 font-semibold text-slate-800">{{ kegiatan.nama }}</td>
                    <td class="px-3 py-2 text-right">{{ formatNumber(kegiatanPerformance(kegiatan).targetOutput) }} {{ kegiatan.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).anggaran) }}</td>
                    <td class="px-3 py-2 text-right">{{ formatNumber(kegiatanPerformance(kegiatan).realisasiOutput) }} {{ kegiatan.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(kegiatanPerformance(kegiatan).realisasiUang) }}</td>
                    <td class="px-3 py-2 text-right" :class="kegiatanPerformance(kegiatan).sisaOutput < 0 ? 'text-red-600' : 'text-emerald-700'">{{ formatNumber(kegiatanPerformance(kegiatan).sisaOutput) }} {{ kegiatan.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap" :class="kegiatanPerformance(kegiatan).sisaUang < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(kegiatanPerformance(kegiatan).sisaUang) }}</td>
                    <td class="px-3 py-2 text-right font-semibold" :class="metricToneClass(kegiatanPerformance(kegiatan).capaianOutput)">{{ Number(kegiatanPerformance(kegiatan).capaianOutput || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                    <td class="sticky right-0 z-10 bg-blue-50/90 px-3 py-2 text-right font-semibold" :class="metricToneClass(kegiatanPerformance(kegiatan).capaianUang)">{{ Number(kegiatanPerformance(kegiatan).capaianUang || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                  </tr>

                  <tr
                    v-for="lokasi in (kegiatan.lokasis || [])"
                    :key="`summary-${lokasi.id}`"
                    :class="rowToneClass(lokasiPerformance(lokasi).capaianUang)"
                  >
                    <td class="sticky left-0 z-10 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</td>
                    <td class="sticky left-[90px] z-10 bg-white px-3 py-2 text-slate-700">{{ lokasi.lokasi || '-' }}</td>
                    <td class="px-3 py-2 text-right">{{ formatNumber(lokasiPerformance(lokasi).targetOutput) }} {{ lokasi.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(lokasiPerformance(lokasi).anggaran) }}</td>
                    <td class="px-3 py-2 text-right">{{ formatNumber(lokasiPerformance(lokasi).realisasiOutput) }} {{ lokasi.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">Rp {{ formatNumber(lokasiPerformance(lokasi).realisasiUang) }}</td>
                    <td class="px-3 py-2 text-right" :class="lokasiPerformance(lokasi).sisaOutput < 0 ? 'text-red-600' : 'text-emerald-700'">{{ formatNumber(lokasiPerformance(lokasi).sisaOutput) }} {{ lokasi.satuan || '' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap" :class="lokasiPerformance(lokasi).sisaUang < 0 ? 'text-red-600' : 'text-emerald-700'">Rp {{ formatNumber(lokasiPerformance(lokasi).sisaUang) }}</td>
                    <td class="px-3 py-2 text-right font-semibold" :class="metricToneClass(lokasiPerformance(lokasi).capaianOutput)">{{ Number(lokasiPerformance(lokasi).capaianOutput || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                    <td class="sticky right-0 z-10 px-3 py-2 text-right font-semibold" :class="[metricToneClass(lokasiPerformance(lokasi).capaianUang), stickyRightCellToneClass(lokasiPerformance(lokasi).capaianUang)]">{{ Number(lokasiPerformance(lokasi).capaianUang || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="kegiatanModalTab !== 'detail'" class="mt-6 space-y-5">
              <article
                v-for="lokasi in (kegiatan.lokasis || [])"
                :key="lokasi.id"
                class="rounded-2xl border border-gray-200 bg-slate-50/70 p-5"
              >
                <div class="border-b border-gray-200 pb-4">
                  <div>
                    <p class="text-xs capitalize tracking-[0.18em] text-slate-500">Lokasi</p>
                    <h4 class="mt-1 text-lg font-semibold text-slate-800">{{ lokasi.lokasi || '-' }}</h4>
                  </div>
                  <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">Target {{ formatNumber(lokasiPerformance(lokasi).targetOutput) }} {{ lokasi.satuan || '' }}</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">Anggaran Rp {{ formatNumber(lokasiPerformance(lokasi).anggaran) }}</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">Realisasi Rp {{ formatNumber(lokasiPerformance(lokasi).realisasiUang) }}</span>
                    <span class="rounded-full px-2.5 py-1 font-medium" :class="lokasiPerformance(lokasi).sisaUang < 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'">Sisa Rp {{ formatNumber(lokasiPerformance(lokasi).sisaUang) }}</span>
                  </div>
                </div>

                <div v-if="kegiatanModalTab === 'input' && !isSuperadmin" class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                  <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-12 ">
                    <div class="lg:col-span-2">
                      <label class="label">Tanggal </label>
                      <input
                        v-model="rows[rowKey(kegiatan.id, lokasi.id)].tanggal_realisasi"
                        type="date"
                        class="input"
                      />
                    </div>
                    <div class="lg:col-span-2">
                      <label class="label">Realisasi Output</label>
                      <input
                        type="text"
                        class="input text-right"
                        :value="rows[rowKey(kegiatan.id, lokasi.id)].realisasi_output"
                        @input="onTextInput(kegiatan.id, lokasi.id, 'realisasi_output', $event)"
                        placeholder="0"
                      />
                    </div>
                    <div class="lg:col-span-3">
                      <label class="label">Realisasi Biaya</label>
                      <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs font-semibold text-slate-500">Rp</span>
                        <input
                          type="text"
                          class="input pl-9 text-right"
                          :value="rows[rowKey(kegiatan.id, lokasi.id)].realisasi_biaya"
                          @input="onTextInput(kegiatan.id, lokasi.id, 'realisasi_biaya', $event)"
                          placeholder="0"
                        />
                      </div>
                    </div>

                    <div class="lg:col-span-1">
                      <label class="label">Evidence</label>
                      <input
                        type="file"
                        multiple
                        class="input text-[9px]"
                        @change="onFileChange(kegiatan.id, lokasi.id, 'evidence_files', $event)"
                      />
                      <p class="mt-1 text-[11px] text-slate-500">{{ fileCountLabel(kegiatan.id, lokasi.id, 'evidence_files') }}</p>
                    </div>

                    <div class="lg:col-span-1">
                      <label class="label">Laporan</label>
                      <input
                        type="file"
                        multiple
                        class="input text-[9px]"
                        @change="onFileChange(kegiatan.id, lokasi.id, 'laporan_files', $event)"
                      />
                      <p class="mt-1 text-[11px] text-slate-500">{{ fileCountLabel(kegiatan.id, lokasi.id, 'laporan_files') }}</p>
                    </div>

                    <div class="lg:col-span-3">
                      <label class="label">Keterangan</label>
                      <textarea
                        v-model="rows[rowKey(kegiatan.id, lokasi.id)].keterangan"
                        class="input min-h-[44px]"
                        placeholder="Keterangan realisasi terbaru"
                        rows="3"
                      ></textarea>
                    </div>

                  </div>

                  <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <div v-if="rows[rowKey(kegiatan.id, lokasi.id)].evidence_files?.length" class="rounded-lg border border-blue-100 bg-blue-50/60 p-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Preview Evidence</p>
                      <div class="mt-2 flex flex-wrap gap-2">
                        <div v-for="(file, idx) in rows[rowKey(kegiatan.id, lokasi.id)].evidence_files" :key="`ev-${idx}`" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1 text-[11px]">
                          <span class="max-w-[180px] truncate text-blue-700 font-medium" :title="file.name">{{ file.name }}</span>
                          <button
                            type="button"
                            class="rounded-full border border-blue-200 px-2 py-0.5 text-[10px] font-semibold text-blue-700 hover:bg-blue-100"
                            @click="previewSelectedFile(file)"
                          >
                            Preview
                          </button>
                        </div>
                      </div>
                    </div>
                    <div v-if="rows[rowKey(kegiatan.id, lokasi.id)].laporan_files?.length" class="rounded-lg border border-emerald-100 bg-emerald-50/60 p-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Preview Laporan</p>
                      <div class="mt-2 flex flex-wrap gap-2">
                        <div v-for="(file, idx) in rows[rowKey(kegiatan.id, lokasi.id)].laporan_files" :key="`lap-${idx}`" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1 text-[11px]">
                          <span class="max-w-[180px] truncate text-emerald-700 font-medium" :title="file.name">{{ file.name }}</span>
                          <button
                            type="button"
                            class="rounded-full border border-emerald-200 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-100"
                            @click="previewSelectedFile(file)"
                          >
                            Preview
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                      {{ rows[rowKey(kegiatan.id, lokasi.id)].editing_id ? 'Mode edit realisasi.' : 'Input ini akan disimpan sebagai realisasi baru.' }}
                    </div>
                    <div class="flex gap-2">
                      <button
                        v-if="rows[rowKey(kegiatan.id, lokasi.id)].editing_id"
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="resetRow(kegiatan.id, lokasi.id)"
                      >
                        Batal Edit
                      </button>
                      <button
                        type="button"
                        class="rounded-lg bg-[#0A3C73] px-4 py-2 text-sm font-semibold text-white hover:bg-[#082f59] disabled:opacity-50"
                        :disabled="processing[rowKey(kegiatan.id, lokasi.id)]"
                        @click="saveRow(kegiatan, lokasi)"
                      >
                        {{ processing[rowKey(kegiatan.id, lokasi.id)] ? 'Menyimpan...' : (rows[rowKey(kegiatan.id, lokasi.id)].editing_id ? 'Perbarui Realisasi' : 'Simpan Realisasi') }}
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else-if="kegiatanModalTab === 'input'" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-500">
                  Superadmin hanya dapat melihat data realisasi pada halaman ini.
                </div>

                <div v-if="kegiatanModalTab === 'history'" class="mt-5 rounded-2xl border border-slate-200 bg-white">
                  <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                    <button
                      type="button"
                      @click="toggleHistoryCollapse(lokasi.id)"
                      class="flex-1 text-left flex items-center justify-between hover:bg-slate-50 rounded-lg px-2 py-1 transition-colors"
                    >
                      <div>
                        <h5 class="text-sm font-semibold text-slate-800">Riwayat Realisasi</h5>
                        <p class="text-xs text-slate-500">Diurutkan dari tanggal realisasi terbaru.</p>
                      </div>
                      <div class="flex items-center gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                          {{ historyFor(lokasi.id).length }} entri
                        </span>
                        <svg
                          :class="['w-5 h-5 transition-transform', collapsedHistory[Number(lokasi.id || 0)] ? 'rotate-180' : '']"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                      </div>
                    </button>
                  </div>

                  <div v-if="!collapsedHistory[Number(lokasi.id || 0)]" class="divide-y divide-slate-100">
                    <div v-if="historyFor(lokasi.id).length" class="divide-y divide-slate-100">
                      <div v-for="item in historyFor(lokasi.id)" :key="item.id" class="px-4 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-slate-800">{{ formatDate(item.tanggal_realisasi || item.created_at) }}</p>
                            <p class="mt-1 text-xs text-slate-500">Output {{ formatNumber(item.realisasi_output) }} · Biaya Rp {{ formatNumber(item.realisasi_biaya) }}</p>
                          </div>
                          <div v-if="!isSuperadmin" class="flex items-center gap-2">
                            <button
                              type="button"
                              class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                              @click="startEdit(kegiatan.id, lokasi.id, item)"
                            >
                              Edit
                            </button>
                            <button
                              type="button"
                              class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100"
                              @click="destroyRow(item.id)"
                            >
                              Hapus
                            </button>
                          </div>
                        </div>

                        <p class="mt-2 text-sm text-slate-600">{{ item.keterangan || '-' }}</p>

                        <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
                          <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Evidence</p>
                            <div class="mt-1 flex flex-wrap gap-2">
                              <a
                                v-for="file in fileList(item, 'evidence')"
                                :key="file.id"
                                :href="route('files.download', file.id)"
                                class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-medium text-blue-700 hover:bg-blue-100"
                              >
                                {{ file.file_name }}
                              </a>
                              <span v-if="!fileList(item, 'evidence').length" class="text-xs text-slate-400">-</span>
                            </div>
                          </div>

                          <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Laporan</p>
                            <div class="mt-1 flex flex-wrap gap-2">
                              <a
                                v-for="file in fileList(item, 'laporan')"
                                :key="file.id"
                                :href="route('files.download', file.id)"
                                class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700 hover:bg-emerald-100"
                              >
                                {{ file.file_name }}
                              </a>
                              <span v-if="!fileList(item, 'laporan').length" class="text-xs text-slate-400">-</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="px-4 py-6 text-center text-sm text-slate-400">
                      Belum ada realisasi untuk lokasi ini.
                    </div>
                  </div>
                </div>
              </article>

              <div v-if="!(kegiatan.lokasis || []).length" class="rounded-xl border border-dashed border-gray-300 px-4 py-5 text-sm text-gray-500">
                Kegiatan ini belum memiliki lokasi. Tambahkan lokasi pada perencanaan terlebih dahulu.
              </div>
            </div>
          </div>
                  </section>
                  </div>
                </div>
              </transition>
            </section>
          </div>
        </section>
      </div>

      <div v-else class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-gray-500">
        Tidak ada kegiatan yang cocok dengan filter saat ini.
      </div>
    </div>

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="previewPopup.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @click.self="closePreviewPopup">
        <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.35)]">
          <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Preview File</p>
              <h4 class="truncate text-base font-semibold text-slate-800">{{ previewPopup.name }}</h4>
            </div>
            <button type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100" @click="closePreviewPopup">
              Tutup
            </button>
          </div>

          <div class="min-h-[320px] flex-1 overflow-auto bg-slate-100 p-4">
            <img v-if="previewPopup.kind === 'image'" :src="previewPopup.url" :alt="previewPopup.name" class="mx-auto max-h-[72vh] rounded-xl bg-white object-contain shadow-sm" />
            <iframe v-else-if="previewPopup.kind === 'pdf'" :src="previewPopup.url" class="h-[72vh] w-full rounded-xl border border-slate-200 bg-white"></iframe>
            <div v-else class="flex h-[320px] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center">
              <div>
                <p class="text-sm font-medium text-slate-700">Preview langsung belum tersedia untuk file ini.</p>
                <p class="mt-1 text-xs text-slate-500">Buka file di tab baru untuk melihat isi lengkapnya.</p>
                <a :href="previewPopup.url" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                  Buka File
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  realisasis: {
    type: Array,
    default: () => [],
  },
  kegiatans: {
    type: Array,
    default: () => [],
  },
  programSpendings: {
    type: Array,
    default: () => [],
  },
  isSuperadmin: {
    type: Boolean,
    default: false,
  },
})

const searchQuery = ref('')
const selectedPilarId = ref('')
const selectedProgramId = ref('')
const selectedKegiatanFilterId = ref('')
const selectedAchievementFilter = ref('all')
const selectedKegiatanDetailId = ref(null)
const kegiatanModalTab = ref('detail')
const rows = reactive({})
const processing = reactive({})
const collapsedHistory = reactive({})
const collapsedPrograms = reactive({})
const collapsedKegiatanLokasis = reactive({})
const successMessage = ref('')
const errorMessage = ref('')
const warningMessage = ref('')
const previewPopup = reactive({
  open: false,
  name: '',
  url: '',
  kind: 'other',
})
const page = usePage()

const isSuperadmin = computed(() => props.isSuperadmin)
const currentUserName = computed(() => String(page.props?.auth?.user?.name || 'User'))
const userPerformanceLabel = computed(() => (isSuperadmin.value ? 'Semua User' : `User ${currentUserName.value}`))

const realisasiByKegiatan = computed(() => {
  const map = new Map()
  ;(props.realisasis || []).forEach((item) => {
    const kegiatanId = Number(item.kegiatan_id || 0)
    if (!kegiatanId) return
    const current = map.get(kegiatanId) || { output: 0, biaya: 0 }
    current.output += Number(item.realisasi_output || 0)
    current.biaya += Number(item.realisasi_biaya || 0)
    map.set(kegiatanId, current)
  })
  return map
})

const realisasiByLokasi = computed(() => {
  const map = new Map()
  ;(props.realisasis || []).forEach((item) => {
    const lokasiId = Number(item.kegiatan_lokasi_id || 0)
    if (!lokasiId) return
    const current = map.get(lokasiId) || { output: 0, biaya: 0 }
    current.output += Number(item.realisasi_output || 0)
    current.biaya += Number(item.realisasi_biaya || 0)
    map.set(lokasiId, current)
  })
  return map
})

const userPerformance = computed(() => {
  const anggaran = (props.kegiatans || []).reduce((sum, kegiatan) => sum + Number(kegiatan.rencana_biaya || 0), 0)
  const realisasi = (props.kegiatans || []).reduce(
    (sum, kegiatan) => sum + Number(realisasiByKegiatan.value.get(Number(kegiatan.id || 0))?.biaya || 0),
    0
  )
  return {
    anggaran,
    realisasi,
    sisa: anggaran - realisasi,
    persentase: anggaran > 0 ? (realisasi / anggaran) * 100 : 0,
  }
})

const pilarOptions = computed(() => {
  const map = new Map()

  ;(props.kegiatans || []).forEach((item) => {
    const pilar = item.program?.pilar
    if (pilar?.id && !map.has(Number(pilar.id))) {
      map.set(Number(pilar.id), {
        id: Number(pilar.id),
        nama: pilar.nama,
      })
    }
  })

  return Array.from(map.values()).sort((left, right) => left.nama.localeCompare(right.nama))
})

const programOptions = computed(() => {
  const map = new Map()

  ;(props.kegiatans || []).forEach((item) => {
    const program = item.program
    const pilarId = String(program?.pilar?.id || '')

    if (selectedPilarId.value && pilarId !== selectedPilarId.value) {
      return
    }

    if (program?.id && !map.has(Number(program.id))) {
      map.set(Number(program.id), {
        id: Number(program.id),
        nama: program.nama,
      })
    }
  })

  return Array.from(map.values()).sort((left, right) => left.nama.localeCompare(right.nama))
})

const kegiatanFilterOptions = computed(() => {
  return (props.kegiatans || [])
    .filter((item) => {
      const pilarId = String(item.program?.pilar?.id || '')
      const programId = String(item.program?.id || '')

      if (selectedPilarId.value && pilarId !== selectedPilarId.value) return false
      if (selectedProgramId.value && programId !== selectedProgramId.value) return false

      return true
    })
    .map((item) => ({
      id: item.id,
      nama: item.nama,
    }))
    .sort((left, right) => left.nama.localeCompare(right.nama))
})

const filteredKegiatans = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  return (props.kegiatans || []).filter((item) => {
    const pilarId = String(item.program?.pilar?.id || '')
    const programId = String(item.program?.id || '')
    const kegiatanId = String(item.id || '')

    if (selectedPilarId.value && pilarId !== selectedPilarId.value) return false
    if (selectedProgramId.value && programId !== selectedProgramId.value) return false
    if (selectedKegiatanFilterId.value && kegiatanId !== selectedKegiatanFilterId.value) return false

    const capaianUang = kegiatanPerformance(item).capaianUang
    const matchesAchievement = matchesAchievementFilter(capaianUang)
    if (!matchesAchievement) return false

    if (!keyword) return true

    const haystack = [item.program?.pilar?.nama, item.program?.nama, item.nama, item.divisi?.nama]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(keyword)
  })
})

const groupedKegiatans = computed(() => {
  const pilarMap = new Map()

  ;(filteredKegiatans.value || []).forEach((kegiatan) => {
    const pilarId = Number(kegiatan.program?.pilar?.id || 0)
    const pilarNama = kegiatan.program?.pilar?.nama || 'Tanpa Pilar'
    const pilarWarna = kegiatan.program?.pilar?.warna || ''
    const programId = Number(kegiatan.program?.id || 0)
    const programNama = kegiatan.program?.nama || 'Tanpa Program'
    const kegiatanAnggaran = Number(kegiatan.rencana_biaya || 0)
    const kegiatanRealisasi = Number(realisasiByKegiatan.value.get(Number(kegiatan.id))?.biaya || 0)

    if (!pilarMap.has(pilarId)) {
      pilarMap.set(pilarId, {
        id: pilarId,
        nama: pilarNama,
        warna: pilarWarna,
        totalKegiatan: 0,
        totalAnggaran: 0,
        totalRealisasi: 0,
        programsMap: new Map(),
      })
    }

    const pilarGroup = pilarMap.get(pilarId)
    pilarGroup.totalKegiatan += 1
    pilarGroup.totalAnggaran += kegiatanAnggaran
    pilarGroup.totalRealisasi += kegiatanRealisasi

    if (!pilarGroup.programsMap.has(programId)) {
      pilarGroup.programsMap.set(programId, {
        id: programId,
        nama: programNama,
        totalAnggaran: 0,
        totalRealisasi: 0,
        kegiatans: [],
      })
    }

    const programGroup = pilarGroup.programsMap.get(programId)
    programGroup.totalAnggaran += kegiatanAnggaran
    programGroup.totalRealisasi += kegiatanRealisasi
    programGroup.kegiatans.push(kegiatan)
  })

  return Array.from(pilarMap.values())
    .map((pilar) => ({
      id: pilar.id,
      nama: pilar.nama,
      warna: pilar.warna,
      totalKegiatan: pilar.totalKegiatan,
      totalAnggaran: pilar.totalAnggaran,
      totalRealisasi: pilar.totalRealisasi,
      sisaAnggaran: pilar.totalAnggaran - pilar.totalRealisasi,
      persentase: pilar.totalAnggaran > 0 ? (pilar.totalRealisasi / pilar.totalAnggaran) * 100 : 0,
      programs: Array.from(pilar.programsMap.values())
        .map((program) => ({
          ...program,
          sisaAnggaran: program.totalAnggaran - program.totalRealisasi,
          persentase: program.totalAnggaran > 0 ? (program.totalRealisasi / program.totalAnggaran) * 100 : 0,
        }))
        .sort((left, right) => left.nama.localeCompare(right.nama)),
    }))
    .sort((left, right) => left.nama.localeCompare(right.nama))
})

const historiesByLokasi = computed(() => {
  const map = new Map()

  ;(props.realisasis || []).forEach((item) => {
    const lokasiId = Number(item.kegiatan_lokasi_id || 0)
    if (!lokasiId) return

    if (!map.has(lokasiId)) {
      map.set(lokasiId, [])
    }

    map.get(lokasiId).push(item)
  })

  map.forEach((items, lokasiId) => {
    map.set(
      lokasiId,
      items.sort((left, right) => {
        const leftTime = new Date(left.tanggal_realisasi || left.created_at || 0).getTime()
        const rightTime = new Date(right.tanggal_realisasi || right.created_at || 0).getTime()
        if (leftTime === rightTime) return Number(right.id || 0) - Number(left.id || 0)
        return rightTime - leftTime
      })
    )
  })

  return map
})

watch(
  () => props.kegiatans,
  () => {
    ;(props.kegiatans || []).forEach((kegiatan) => {
      ;(kegiatan.lokasis || []).forEach((lokasi) => {
        ensureRow(kegiatan.id, lokasi.id)
        const collapseKey = Number(lokasi.id || 0)
        if (collapseKey && collapsedHistory[collapseKey] === undefined) {
          collapsedHistory[collapseKey] = true
        }
      })
    })
  },
  { immediate: true, deep: true }
)

watch(selectedPilarId, () => {
  if (selectedProgramId.value && !programOptions.value.some((item) => String(item.id) === selectedProgramId.value)) {
    selectedProgramId.value = ''
  }

  if (selectedKegiatanFilterId.value && !kegiatanFilterOptions.value.some((item) => String(item.id) === selectedKegiatanFilterId.value)) {
    selectedKegiatanFilterId.value = ''
  }
})

watch(selectedProgramId, () => {
  if (selectedKegiatanFilterId.value && !kegiatanFilterOptions.value.some((item) => String(item.id) === selectedKegiatanFilterId.value)) {
    selectedKegiatanFilterId.value = ''
  }
})

watch(filteredKegiatans, (items) => {
  const exists = (items || []).some((item) => Number(item.id) === Number(selectedKegiatanDetailId.value || 0))
  if (!exists) {
    closeKegiatanModal()
  }
})

function ensureRow(kegiatanId, lokasiId) {
  const key = rowKey(kegiatanId, lokasiId)
  if (!rows[key]) {
    rows[key] = defaultRowState()
  }
  return rows[key]
}

function defaultRowState() {
  return {
    editing_id: null,
    tanggal_realisasi: new Date().toISOString().slice(0, 10),
    realisasi_output: '',
    realisasi_biaya: '',
    keterangan: '',
    evidence_files: [],
    laporan_files: [],
  }
}

function rowKey(kegiatanId, lokasiId) {
  return `${kegiatanId}:${lokasiId}`
}

function onTextInput(kegiatanId, lokasiId, field, event) {
  const value = event.target instanceof HTMLInputElement ? event.target.value : ''
  const clean = String(value || '').replace(/\D/g, '')
  ensureRow(kegiatanId, lokasiId)[field] = clean ? Number(clean).toLocaleString('id-ID') : ''
}

function onFileChange(kegiatanId, lokasiId, field, event) {
  const files = event.target instanceof HTMLInputElement ? Array.from(event.target.files || []) : []
  ensureRow(kegiatanId, lokasiId)[field] = files
}

function unformatNumber(value) {
  if (!value) return null
  const clean = String(value).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.]/g, '')
  return clean ? Number(clean) : null
}

function formatNumber(value) {
  return Number(value || 0).toLocaleString('id-ID')
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

function escapeCsv(value) {
  const text = String(value ?? '')
  if (/[",\n]/.test(text)) {
    return `"${text.replace(/"/g, '""')}"`
  }
  return text
}

function achievementStatus(percent) {
  const value = Number(percent || 0)
  if (value < 50) return 'low'
  if (value < 100) return 'medium'
  if (value <= 120) return 'high'
  return 'over'
}

function matchesAchievementFilter(percent) {
  const mode = selectedAchievementFilter.value
  if (mode === 'all') return true
  return achievementStatus(percent) === mode
}

function metricToneClass(percent) {
  const status = achievementStatus(percent)
  if (status === 'low') return 'text-red-700'
  if (status === 'medium') return 'text-amber-700'
  if (status === 'high') return 'text-emerald-700'
  return 'text-violet-700'
}

function rowToneClass(percent) {
  const status = achievementStatus(percent)
  if (status === 'low') return 'bg-red-50/40'
  if (status === 'medium') return 'bg-amber-50/40'
  if (status === 'high') return 'bg-emerald-50/30'
  return 'bg-violet-50/30'
}

function stickyRightCellToneClass(percent) {
  const status = achievementStatus(percent)
  if (status === 'low') return 'bg-red-50/90'
  if (status === 'medium') return 'bg-amber-50/90'
  if (status === 'high') return 'bg-emerald-50/90'
  return 'bg-violet-50/90'
}

function programCollapseKey(pilarId, programId) {
  return `${Number(pilarId || 0)}:${Number(programId || 0)}`
}

function isProgramCollapsed(pilarId, programId) {
  return Boolean(collapsedPrograms[programCollapseKey(pilarId, programId)])
}

function toggleProgramCollapse(pilarId, programId) {
  const key = programCollapseKey(pilarId, programId)
  collapsedPrograms[key] = !collapsedPrograms[key]
}

function showKegiatanDetail(kegiatanId) {
  selectedKegiatanDetailId.value = Number(kegiatanId || 0) || null
  kegiatanModalTab.value = 'detail'
}

function showIsiRealisasi(kegiatanId) {
  selectedKegiatanDetailId.value = Number(kegiatanId || 0) || null
  kegiatanModalTab.value = 'input'
}

function closeKegiatanModal() {
  selectedKegiatanDetailId.value = null
  kegiatanModalTab.value = 'detail'
}

function setKegiatanModalTab(tab, kegiatan = null) {
  kegiatanModalTab.value = tab

  if (tab === 'history' && kegiatan) {
    ;(kegiatan.lokasis || []).forEach((lokasi) => {
      const key = Number(lokasi.id || 0)
      if (key) {
        collapsedHistory[key] = false
      }
    })
  }
}

function kegiatanLokasiCollapseKey(kegiatanId) {
  return Number(kegiatanId || 0)
}

function isKegiatanLokasiCollapsed(kegiatanId) {
  return Boolean(collapsedKegiatanLokasis[kegiatanLokasiCollapseKey(kegiatanId)])
}

function toggleKegiatanLokasiCollapse(kegiatanId) {
  const key = kegiatanLokasiCollapseKey(kegiatanId)
  collapsedKegiatanLokasis[key] = !collapsedKegiatanLokasis[key]
}

function allLokasiCollapsedInProgram(kegiatans) {
  const items = (kegiatans || []).filter((item) => (item.lokasis || []).length)
  if (!items.length) return false
  return items.every((item) => isKegiatanLokasiCollapsed(item.id))
}

function toggleProgramLokasiCollapse(kegiatans) {
  const items = (kegiatans || []).filter((item) => (item.lokasis || []).length)
  const shouldCollapse = !allLokasiCollapsedInProgram(items)
  items.forEach((item) => {
    collapsedKegiatanLokasis[kegiatanLokasiCollapseKey(item.id)] = shouldCollapse
  })
}

function selectedKegiatanInProgram(kegiatans) {
  return (kegiatans || []).some((item) => Number(item.id) === Number(selectedKegiatanDetailId.value || 0))
}

function exportSummaryCsv() {
  const header = [
    'Pilar',
    'Program',
    'Level',
    'Nama',
    'Target Output',
    'Anggaran',
    'Realisasi Output',
    'Realisasi Uang',
    'Sisa Output',
    'Sisa Uang',
    'Capaian Output (%)',
    'Capaian Uang (%)',
  ]

  const rowsCsv = [header.join(',')]

  groupedKegiatans.value.forEach((pilar) => {
    pilar.programs.forEach((program) => {
      program.kegiatans.forEach((kegiatan) => {
        const kp = kegiatanPerformance(kegiatan)
        rowsCsv.push(
          [
            pilar.nama,
            program.nama,
            'Kegiatan',
            kegiatan.nama,
            kp.targetOutput,
            kp.anggaran,
            kp.realisasiOutput,
            kp.realisasiUang,
            kp.sisaOutput,
            kp.sisaUang,
            Number(kp.capaianOutput || 0).toFixed(2),
            Number(kp.capaianUang || 0).toFixed(2),
          ]
            .map(escapeCsv)
            .join(',')
        )

        ;(kegiatan.lokasis || []).forEach((lokasi) => {
          const lp = lokasiPerformance(lokasi)
          rowsCsv.push(
            [
              pilar.nama,
              program.nama,
              'Lokasi',
              lokasi.lokasi || '-',
              lp.targetOutput,
              lp.anggaran,
              lp.realisasiOutput,
              lp.realisasiUang,
              lp.sisaOutput,
              lp.sisaUang,
              Number(lp.capaianOutput || 0).toFixed(2),
              Number(lp.capaianUang || 0).toFixed(2),
            ]
              .map(escapeCsv)
              .join(',')
          )
        })
      })
    })
  })

  const csv = `\uFEFF${rowsCsv.join('\n')}`
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  const today = new Date().toISOString().slice(0, 10)

  link.href = url
  link.setAttribute('download', `ringkasan-realisasi-${today}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

function sanitizeHexColor(value, fallback = '#64748B') {
  const text = String(value || '').trim()
  return /^#[0-9A-Fa-f]{6}$/.test(text) ? text.toUpperCase() : fallback
}

function hexToRgba(hex, alpha) {
  const safeHex = sanitizeHexColor(hex)
  const normalizedAlpha = Number.isFinite(alpha) ? Math.min(Math.max(alpha, 0), 1) : 1
  const parsed = safeHex.replace('#', '')
  const r = Number.parseInt(parsed.slice(0, 2), 16)
  const g = Number.parseInt(parsed.slice(2, 4), 16)
  const b = Number.parseInt(parsed.slice(4, 6), 16)
  return `rgba(${r}, ${g}, ${b}, ${normalizedAlpha})`
}

function pilarDisplayColor(pilar) {
  return sanitizeHexColor(pilar?.warna)
}

function pilarCardStyle(pilar) {
  const base = pilarDisplayColor(pilar)
  return {
    borderColor: hexToRgba(base, 0.35),
    background: `linear-gradient(135deg, ${hexToRgba(base, 0.16)} 0%, ${hexToRgba(base, 0.06)} 100%)`,
  }
}

function pilarHeaderStyle(pilar) {
  const base = pilarDisplayColor(pilar)
  return {
    borderColor: hexToRgba(base, 0.28),
  }
}

function pilarHierarchyRowStyle(pilar) {
  const base = pilarDisplayColor(pilar)
  return {
    backgroundColor: hexToRgba(base, 0.16),
  }
}

function historyFor(lokasiId) {
  return historiesByLokasi.value.get(Number(lokasiId || 0)) || []
}

function latestHistoryFor(lokasiId) {
  return historyFor(lokasiId)[0] || null
}

function fileList(item, kategori) {
  return (item.files || []).filter((file) => file.kategori === kategori)
}

function fileCountLabel(kegiatanId, lokasiId, field) {
  const count = ensureRow(kegiatanId, lokasiId)[field]?.length || 0
  return count ? `${count} file dipilih` : 'Belum ada file baru dipilih'
}

function previewSelectedFile(file) {
  if (!(file instanceof File)) return

  closePreviewPopup()

  previewPopup.name = file.name
  previewPopup.url = URL.createObjectURL(file)
  previewPopup.kind = previewKind(file)
  previewPopup.open = true
}

function previewKind(file) {
  const mime = String(file?.type || '').toLowerCase()

  if (mime.startsWith('image/')) return 'image'
  if (mime === 'application/pdf') return 'pdf'

  return 'other'
}

function closePreviewPopup() {
  if (previewPopup.url) {
    URL.revokeObjectURL(previewPopup.url)
  }

  previewPopup.open = false
  previewPopup.name = ''
  previewPopup.url = ''
  previewPopup.kind = 'other'
}

function startEdit(kegiatanId, lokasiId, item) {
  const row = ensureRow(kegiatanId, lokasiId)
  row.editing_id = item.id
  row.tanggal_realisasi = item.tanggal_realisasi || new Date().toISOString().slice(0, 10)
  row.realisasi_output = item.realisasi_output ? formatNumber(item.realisasi_output) : ''
  row.realisasi_biaya = item.realisasi_biaya ? formatNumber(item.realisasi_biaya) : ''
  row.keterangan = item.keterangan || ''
  row.evidence_files = []
  row.laporan_files = []
  kegiatanModalTab.value = 'input'
}

function resetRow(kegiatanId, lokasiId) {
  rows[rowKey(kegiatanId, lokasiId)] = defaultRowState()
}

function kegiatanPerformance(kegiatan) {
  const targetOutput = Number(kegiatan?.target_output || 0)
  const anggaran = Number(kegiatan?.rencana_biaya || 0)
  const aggregate = realisasiByKegiatan.value.get(Number(kegiatan?.id || 0)) || { output: 0, biaya: 0 }
  const realisasiOutput = Number(aggregate.output || 0)
  const realisasiUang = Number(aggregate.biaya || 0)

  return {
    targetOutput,
    anggaran,
    realisasiOutput,
    realisasiUang,
    sisaOutput: targetOutput - realisasiOutput,
    sisaUang: anggaran - realisasiUang,
    capaianOutput: targetOutput > 0 ? (realisasiOutput / targetOutput) * 100 : 0,
    capaianUang: anggaran > 0 ? (realisasiUang / anggaran) * 100 : 0,
  }
}

function lokasiPerformance(lokasi) {
  const targetOutput = Number(lokasi?.target_output || 0)
  const anggaran = Number(lokasi?.rencana_biaya || 0)
  const aggregate = realisasiByLokasi.value.get(Number(lokasi?.id || 0)) || { output: 0, biaya: 0 }
  const realisasiOutput = Number(aggregate.output || 0)
  const realisasiUang = Number(aggregate.biaya || 0)

  return {
    targetOutput,
    anggaran,
    realisasiOutput,
    realisasiUang,
    sisaOutput: targetOutput - realisasiOutput,
    sisaUang: anggaran - realisasiUang,
    capaianOutput: targetOutput > 0 ? (realisasiOutput / targetOutput) * 100 : 0,
    capaianUang: anggaran > 0 ? (realisasiUang / anggaran) * 100 : 0,
  }
}

function toggleHistoryCollapse(lokasiId) {
  const key = Number(lokasiId || 0)
  collapsedHistory[key] = !collapsedHistory[key]
}

function resetFilters() {
  searchQuery.value = ''
  selectedPilarId.value = ''
  selectedProgramId.value = ''
  selectedKegiatanFilterId.value = ''
  selectedAchievementFilter.value = 'all'
}

function saveRow(kegiatan, lokasi) {
  const key = rowKey(kegiatan.id, lokasi.id)
  const row = ensureRow(kegiatan.id, lokasi.id)
  const formData = new FormData()

  errorMessage.value = ''
  warningMessage.value = ''

  formData.append('kegiatan_id', String(kegiatan.id))
  formData.append('kegiatan_lokasi_id', String(lokasi.id))
  formData.append('tanggal_realisasi', row.tanggal_realisasi || '')
  formData.append('realisasi_output', String(unformatNumber(row.realisasi_output) ?? ''))
  formData.append('realisasi_biaya', String(unformatNumber(row.realisasi_biaya) ?? ''))
  formData.append('keterangan', row.keterangan || '')
  ;(row.evidence_files || []).forEach((file) => formData.append('evidence_files[]', file))
  ;(row.laporan_files || []).forEach((file) => formData.append('laporan_files[]', file))

  processing[key] = true

  const options = {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      resetRow(kegiatan.id, lokasi.id)
      successMessage.value = row.editing_id ? 'Realisasi berhasil diperbarui!' : 'Realisasi berhasil disimpan!'
      errorMessage.value = ''
      warningMessage.value = ''
      setTimeout(() => {
        successMessage.value = ''
      }, 4000)
    },
    onError: (errors) => {
      const firstError = Object.values(errors || {}).find((msg) => {
        if (Array.isArray(msg)) return msg.length > 0
        return Boolean(msg)
      })

      if (Array.isArray(firstError)) {
        errorMessage.value = String(firstError[0] || 'Data realisasi gagal disimpan.')
      } else {
        errorMessage.value = String(firstError || 'Data realisasi gagal disimpan.')
      }
    },
    onFinish: () => {
      const flashWarning = page.props.flash?.warning
      if (flashWarning) {
        warningMessage.value = String(flashWarning)
      }
      processing[key] = false
    },
  }

  if (row.editing_id) {
    formData.append('_method', 'PUT')
    router.post(route('realisasi.update', row.editing_id), formData, options)
    return
  }

  router.post(route('realisasi.store'), formData, options)
}

function destroyRow(realisasiId) {
  if (!window.confirm('Hapus realisasi ini?')) return

  router.delete(route('realisasi.destroy', realisasiId), {
    preserveScroll: true,
  })
}
</script>

<style scoped>
.input {
  border-width: 2px;
}

.collapse-panel-enter-active,
.collapse-panel-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.collapse-panel-enter-from,
.collapse-panel-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.collapse-panel-enter-to,
.collapse-panel-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.lokasi-row-enter-active,
.lokasi-row-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.lokasi-row-enter-from,
.lokasi-row-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.lokasi-row-enter-to,
.lokasi-row-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.22s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-to,
.modal-fade-leave-from {
  opacity: 1;
}
</style>
