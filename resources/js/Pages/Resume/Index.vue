<template>
  <AppLayout :title="pageTitle">
    <template #header-actions>
      <a :href="exportUrl('excel')" class="btn-secondary">Export Excel</a>
      <a :href="exportUrl('pdf')" class="btn-primary">Export PDF</a>
    </template>

    <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
        <div>
          <label class="label">Kategori {{ resume.category_label }}</label>
          <select v-model="filters.kategori_id" class="input">
            <option value="">Semua {{ resume.category_label }}</option>
            <option v-for="item in resume.filter_options?.categories || []" :key="item.id" :value="item.id">{{ item.nama }}</option>
          </select>
        </div>

        <div>
          <label class="label">Tahun</label>
          <select v-model="filters.tahun" class="input">
            <option value="">Semua tahun</option>
            <option v-for="year in resume.filter_options?.years || []" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>

        <div>
          <label class="label">Mode Periode</label>
          <div class="flex rounded-lg border border-gray-200 overflow-hidden">
            <button
              v-for="mode in (resume.filter_options?.period_modes || defaultPeriodModes)"
              :key="mode.value"
              type="button"
              class="flex-1 py-2 text-xs font-medium transition-colors"
              :class="filters.period_mode === mode.value
                ? 'bg-[#062A57] text-white'
                : 'bg-white text-gray-600 hover:bg-gray-50'"
              @click="onPeriodModeChange(mode.value)"
            >
              {{ mode.label }}
            </button>
          </div>
        </div>

        <div>
          <label class="label">{{ periodModeLabel }}</label>
          <select v-model="filters.period_value" class="input">
            <option value="">Semua {{ periodModeLabel }}</option>
            <option v-for="opt in currentPeriodOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <div>
          <label class="label">Status</label>
          <select v-model="filters.status" class="input">
            <option value="">Semua status</option>
            <option v-for="status in resume.filter_options?.statuses || []" :key="status.value" :value="status.value">{{ status.label }}</option>
          </select>
        </div>

        <div v-if="isSuperadmin && resumeType !== 'user'">
          <label class="label">User</label>
          <select v-model="filters.user_id" class="input">
            <option value="">Semua user</option>
            <option v-for="user in resume.filter_options?.users || []" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
        </div>

        <div>
          <label class="label">Pencarian</label>
          <input v-model="filters.q" type="text" class="input" placeholder="Cari data..." @keyup.enter="applyFilters" />
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button class="btn-primary" @click="applyFilters">Terapkan Filter</button>
        <button class="btn-secondary" @click="resetFilters">Reset</button>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div class="relative overflow-hidden rounded-2xl bg-[#062A57] p-5 text-white">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Total Perencanaan</p>
        <p class="mt-4 text-3xl font-bold">Rp {{ compactMillion(resume.summary?.total_anggaran) }} M</p>
        <p class="mt-2 text-xs text-slate-300">Total anggaran kegiatan</p>
      </div>

      <div class="rounded-2xl border border-[#A48A28] bg-white p-5 shadow-sm">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Realisasi</p>
        <p class="mt-4 text-3xl font-bold text-[#0A2A53]">Rp {{ compactMillion(resume.summary?.total_realisasi) }} M</p>
        <p class="mt-2 text-xs text-[#8B7423]">Serapan {{ formatPercent(resume.summary?.persentase_serapan) }}%</p>
      </div>

      <div class="rounded-2xl border border-[#E6EBF2] bg-[#F8FAFC] p-5">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Sisa Anggaran</p>
        <p class="mt-4 text-3xl font-bold text-[#0A2A53]">Rp {{ compactMillion(resume.summary?.sisa_anggaran) }} M</p>
        <p class="mt-2 text-xs text-slate-500">Filter kategori + periode aktif</p>
      </div>

      <div class="rounded-2xl border border-[#E8EDF5] bg-[#F5F7FB] p-5">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">{{ resumeType === 'user' ? 'Total User' : 'Total Kegiatan' }}</p>
        <p class="mt-4 text-3xl font-bold text-[#0A2A53]">{{ resumeType === 'user' ? (resume.summary?.total_user ?? 0) : (resume.summary?.total_kegiatan ?? 0) }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ resumeType === 'user' ? 'User aktif sesuai filter' : 'Data sesuai filter' }}</p>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
        <div>
          <h3 class="font-semibold text-gray-800">{{ tableTitle }}</h3>
          <p class="mt-1 text-sm text-gray-500">Ringkasan per kategori dengan total perencanaan dan realisasi.</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b border-gray-100 bg-gray-50 text-gray-600">
            <tr>
              <th class="w-12 px-2 py-3 text-center font-medium">Detail</th>
              <th class="px-4 py-3 text-left font-medium">Kategori</th>
              <th v-if="resumeType === 'user'" class="px-4 py-3 text-left font-medium">Divisi</th>
              <th v-if="resumeType === 'program'" class="px-4 py-3 text-left font-medium">Penginput</th>
              <th class="px-4 py-3 text-right font-medium">Program</th>
              <th class="px-4 py-3 text-right font-medium">Kegiatan</th>
              <th class="px-4 py-3 text-right font-medium">Perencanaan</th>
              <th class="px-4 py-3 text-right font-medium">Realisasi</th>
              <th class="px-4 py-3 text-right font-medium">Sisa</th>
              <th class="px-4 py-3 text-right font-medium">Serapan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="row in currentSummaryRows" :key="row.id">
              <tr class="hover:bg-gray-50">
                <td class="px-2 py-3 text-center">
                  <button
                    type="button"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                    :title="isExpanded(row.id) ? 'Sembunyikan detail' : 'Lihat detail'"
                    @click="toggleRow(row.id)"
                  >
                    <svg class="h-4 w-4 transition-transform" :class="isExpanded(row.id) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.512a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ row.nama }}</td>
                <td v-if="resumeType === 'user'" class="px-4 py-3 text-gray-700">{{ row.divisi || '-' }}</td>
                <td v-if="resumeType === 'program'" class="px-4 py-3 text-gray-700 text-sm">{{ row.user_name || '-' }}</td>
                <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">{{ row.jumlah_program ?? '-' }}</td>
                <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">{{ row.jumlah_kegiatan ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-700">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.total_anggaran) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-700">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.total_realisasi) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3" :class="Number(row.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.sisa_anggaran) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">{{ formatPercent(row.persentase_serapan) }}%</td>
              </tr>

              <tr v-if="shouldShowDetail(row.id)" class="bg-slate-50/70">
                <td :colspan="summaryTableColspan" class="px-4 py-4">
                  <div v-if="drilldownByCategory(row.id).length" class="space-y-3">
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                      <div class="max-h-[480px] overflow-auto">
                        <table class="min-w-full text-xs">
                          <thead class="sticky top-0 z-10 bg-slate-50 text-slate-500 shadow-[0_1px_0_0_#e2e8f0]">
                            <tr>
                              <th class="px-4 py-2.5 text-left font-semibold">Nama</th>
                              <th class="px-4 py-2.5 text-left font-semibold">Tipe</th>
                              <th class="px-4 py-2.5 text-left font-semibold">Diinput Oleh</th>
                              <th class="px-4 py-2.5 text-right font-semibold whitespace-nowrap">Perencanaan</th>
                              <th class="px-4 py-2.5 text-right font-semibold whitespace-nowrap">Realisasi</th>
                              <th class="px-4 py-2.5 text-right font-semibold whitespace-nowrap">Sisa</th>
                              <th class="px-4 py-2.5 text-right font-semibold whitespace-nowrap">Serapan</th>
                              <th class="px-4 py-2.5 text-left font-semibold">Evidence</th>
                              <th class="px-4 py-2.5 text-left font-semibold">Laporan</th>
                              <th class="px-4 py-2.5 text-left font-semibold">Keterangan</th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-slate-100">
                            <template v-for="program in drilldownByCategory(row.id)" :key="`${row.id}-${program.id}`">
                              <tr class="bg-slate-100/90">
                                <td class="px-4 py-2.5 font-semibold text-slate-800">{{ program.nama }}</td>
                                <td class="px-4 py-2.5 text-slate-600">Program · {{ program.jumlah_kegiatan }} kegiatan</td>
                                <td class="px-4 py-2.5 text-slate-700 font-medium">
                                  <span class="inline-flex items-center gap-1">
                                    <span class="material-icons text-[13px] text-slate-400">person</span>
                                    {{ program.user_name || '-' }}
                                  </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-700">
                                  <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                    <span class="text-left">Rp</span>
                                    <span>{{ formatNumber(program.total_anggaran) }}</span>
                                  </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-700">
                                  <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                    <span class="text-left">Rp</span>
                                    <span>{{ formatNumber(program.total_realisasi) }}</span>
                                  </span>
                                </td>
                                <td class="px-4 py-2.5" :class="Number(program.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                                  <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                    <span class="text-left">Rp</span>
                                    <span>{{ formatNumber(program.sisa_anggaran) }}</span>
                                  </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold" :class="serapanClass(program.persentase_serapan)">{{ formatPercent(program.persentase_serapan) }}%</td>
                                <td class="px-4 py-2.5 text-slate-400">-</td>
                                <td class="px-4 py-2.5 text-slate-400">-</td>
                                <td class="px-4 py-2.5 text-slate-400">-</td>
                              </tr>
                              <template v-for="k in program.kegiatans" :key="k.id">
                                <tr class="bg-white hover:bg-slate-50/80">
                                  <td class="py-2 pl-2 pr-4 font-medium text-slate-700">
                                    <span class="inline-flex items-center gap-2 border-l-2 border-slate-200 pl-3">
                                      <span class="h-2 w-2 rounded-full border border-slate-300 bg-slate-100"></span>
                                      <span>{{ k.nama }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2 text-slate-400 italic text-[11px]">Kegiatan</td>
                                  <td class="px-4 py-2 text-slate-400 text-[11px]">{{ k.user_name || '-' }}</td>
                                  <td class="px-4 py-2.5 text-slate-600">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(k.anggaran) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2.5 text-slate-600">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(k.realisasi) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2.5" :class="Number(k.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(k.sisa_anggaran) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2 text-right" :class="serapanClass(k.persentase_serapan)">{{ formatPercent(k.persentase_serapan) }}%</td>
                                  <td class="px-4 py-2 text-slate-400 text-[11px]">-</td>
                                  <td class="px-4 py-2 text-slate-400 text-[11px]">-</td>
                                  <td class="px-4 py-2 text-slate-600 text-[11px]">
                                    <Link
                                      :href="route('kegiatan.show', k.id)"
                                      class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100"
                                    >
                                      Lihat Detail
                                    </Link>
                                  </td>
                                </tr>
                                <tr
                                  v-for="(lokasi, lokasiIndex) in (k.lokasis || [])"
                                  :key="`${k.id}-lokasi-${lokasi.id ?? lokasiIndex}`"
                                  class="bg-slate-50/70"
                                >
                                  <td class="py-2 pl-10 pr-4 text-slate-600">
                                    <span class="inline-flex items-center gap-2 border-l-2 border-dashed border-slate-300 pl-3">
                                      <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                      <span>Lokasi: {{ lokasi.nama || '-' }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2 text-slate-400 italic text-[11px]">Lokasi</td>
                                  <td class="px-4 py-2 text-slate-400 text-[11px]">{{ k.user_name || '-' }}</td>
                                  <td class="px-4 py-2.5 text-slate-600">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(lokasi.anggaran) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2.5 text-slate-600">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(lokasi.realisasi) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2.5" :class="Number(lokasi.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                                    <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums whitespace-nowrap">
                                      <span class="text-left">Rp</span>
                                      <span>{{ formatNumber(lokasi.sisa_anggaran) }}</span>
                                    </span>
                                  </td>
                                  <td class="px-4 py-2 text-right" :class="serapanClass(lokasi.persentase_serapan)">{{ formatPercent(lokasi.persentase_serapan) }}%</td>
                                  <td class="px-4 py-2 text-slate-600 text-[11px]">
                                    <button
                                      v-if="(lokasi.evidence_files || []).length"
                                      type="button"
                                      class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
                                      title="Lihat evidence"
                                      @click="openFilePopup('Evidence', lokasi.evidence_files, lokasi.nama)"
                                    >
                                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 4.5c4.77 0 8.4 3.62 9.58 5.02a.78.78 0 010 .96C18.4 11.88 14.77 15.5 10 15.5S1.6 11.88.42 10.48a.78.78 0 010-.96C1.6 8.12 5.23 4.5 10 4.5zm0 2a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" />
                                      </svg>
                                    </button>
                                    <span v-else>-</span>
                                  </td>
                                  <td class="px-4 py-2 text-slate-600 text-[11px]">
                                    <button
                                      v-if="(lokasi.laporan_files || []).length"
                                      type="button"
                                      class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
                                      title="Lihat laporan"
                                      @click="openFilePopup('Laporan', lokasi.laporan_files, lokasi.nama)"
                                    >
                                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 4.5c4.77 0 8.4 3.62 9.58 5.02a.78.78 0 010 .96C18.4 11.88 14.77 15.5 10 15.5S1.6 11.88.42 10.48a.78.78 0 010-.96C1.6 8.12 5.23 4.5 10 4.5zm0 2a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" />
                                      </svg>
                                    </button>
                                    <span v-else>-</span>
                                  </td>
                                  <td class="px-4 py-2 text-slate-600 text-[11px] max-w-[260px]">
                                    <span class="line-clamp-3" :title="lokasi.keterangan || '-'">{{ lokasi.keterangan || '-' }}</span>
                                  </td>
                                </tr>
                              </template>
                            </template>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <p v-else class="py-4 text-center text-sm text-slate-400">Belum ada detail program atau kegiatan.</p>
                </td>
              </tr>
            </template>
            <tr v-if="!currentSummaryRows.length">
              <td :colspan="summaryTableColspan" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada data pada filter ini.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="filePopup.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeFilePopup">
      <div class="w-full max-w-5xl rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
          <div>
            <h4 class="text-sm font-semibold text-gray-800">{{ filePopup.title }}</h4>
            <p class="text-xs text-gray-500">Lokasi: {{ filePopup.lokasi || '-' }}</p>
          </div>
          <button type="button" class="rounded-md border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50" @click="closeFilePopup">
            Tutup
          </button>
        </div>

        <div class="max-h-[75vh] overflow-auto p-5">
          <div v-if="filePopup.files.length" class="grid grid-cols-1 gap-4 lg:grid-cols-[300px_1fr]">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Daftar File</p>
              <ul class="space-y-2">
                <li v-for="file in filePopup.files" :key="file.id">
                  <button
                    type="button"
                    class="w-full rounded-lg border px-3 py-2 text-left text-xs transition"
                    :class="filePopup.selectedFileId === file.id ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'"
                    @click="selectPopupFile(file)"
                  >
                    <div class="truncate font-medium">{{ file.file_name }}</div>
                  </button>
                </li>
              </ul>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-3">
              <div class="mb-3 flex items-center justify-between gap-2">
                <p class="truncate text-sm font-semibold text-gray-800">{{ selectedPopupFile?.file_name || '-' }}</p>
                <a
                  v-if="selectedPopupFile"
                  :href="route('files.download', selectedPopupFile.id)"
                  class="rounded-md border border-blue-300 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                >
                  Download
                </a>
              </div>

              <div class="min-h-[420px] rounded-lg border border-gray-200 bg-gray-50 p-2">
                <img
                  v-if="isImageFile(selectedPopupFile)"
                  :src="route('files.preview', selectedPopupFile.id)"
                  :alt="selectedPopupFile?.file_name || 'Preview'"
                  class="h-[420px] w-full object-contain"
                />
                <iframe
                  v-else-if="isPdfFile(selectedPopupFile)"
                  :src="route('files.preview', selectedPopupFile.id)"
                  class="h-[420px] w-full"
                />
                <div v-else class="flex h-[420px] items-center justify-center text-sm text-gray-500">
                  Preview tidak tersedia untuk tipe file ini.
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400">Belum ada file.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  resume: {
    type: Object,
    required: true,
  },
})

const isSuperadmin = computed(() => Boolean(props.resume?.is_superadmin))
const resumeType = computed(() => props.resume?.resume_type || 'pilar')

const pageTitle = computed(() => {
  if (resumeType.value === 'program') return 'Resume Program'
  if (resumeType.value === 'divisi') return 'Resume Divisi'
  if (resumeType.value === 'user') return 'Resume User'
  return 'Resume Pilar'
})

const tableTitle = computed(() => {
  if (resumeType.value === 'program') return 'Rekap Resume Program'
  if (resumeType.value === 'divisi') return 'Rekap Resume Divisi'
  if (resumeType.value === 'user') return 'Rekap Resume User'
  return 'Rekap Resume Pilar'
})

const currentSummaryRows = computed(() => {
  if (resumeType.value === 'program') return props.resume?.program_summary || []
  if (resumeType.value === 'divisi') return props.resume?.divisi_summary || []
  if (resumeType.value === 'user') return props.resume?.user_summary || []
  return props.resume?.pilar_summary || []
})

const showToggleColumn = computed(() => true)
const summaryTableColspan = computed(() => {
  let cols = 8 // toggle + nama + program + kegiatan + perencanaan + realisasi + sisa + serapan
  if (resumeType.value === 'user') cols += 1 // divisi column
  if (resumeType.value === 'program') cols += 1 // penginput column
  return cols
})

const expandedRows = ref({})
const summaryDrilldown = computed(() => props.resume?.summary_drilldown || {})
const filePopup = ref({
  open: false,
  title: '',
  lokasi: '',
  files: [],
  selectedFileId: null,
})
const selectedPopupFile = computed(() => filePopup.value.files.find((item) => item.id === filePopup.value.selectedFileId) || null)

const resumeIndexRoute = computed(() => {
  if (resumeType.value === 'program') return 'resume.program.index'
  if (resumeType.value === 'divisi') return 'resume.divisi.index'
  if (resumeType.value === 'user') return 'resume.user.index'
  return 'resume.pilar.index'
})

const filters = ref({
  kategori_id: props.resume?.selected_filters?.kategori_id || '',
  user_id: props.resume?.selected_filters?.user_id || '',
  status: props.resume?.selected_filters?.status || '',
  tahun: props.resume?.selected_filters?.tahun || '',
  period_mode: props.resume?.selected_filters?.period_mode || 'bulan',
  period_value: props.resume?.selected_filters?.period_value !== undefined
    ? (props.resume?.selected_filters?.period_value ?? '')
    : '',
  q: props.resume?.selected_filters?.q || '',
})

const defaultPeriodModes = [
  { value: 'bulan', label: 'Bulan' },
  { value: 'triwulan', label: 'TW' },
  { value: 'semester', label: 'Semester' },
]

const periodModeLabel = computed(() => {
  if (filters.value.period_mode === 'triwulan') return 'Triwulan'
  if (filters.value.period_mode === 'semester') return 'Semester'
  return 'Bulan'
})

const currentPeriodOptions = computed(() => {
  const opts = props.resume?.filter_options?.period_options
  if (opts) return opts[filters.value.period_mode] || []
  if (filters.value.period_mode === 'triwulan') {
    return [
      { value: 1, label: 'Tw 1 (Jan–Mar)' },
      { value: 2, label: 'Tw 2 (Apr–Jun)' },
      { value: 3, label: 'Tw 3 (Jul–Sep)' },
      { value: 4, label: 'Tw 4 (Okt–Des)' },
    ]
  }
  if (filters.value.period_mode === 'semester') {
    return [
      { value: 1, label: 'Sem 1 (Jan–Jun)' },
      { value: 2, label: 'Sem 2 (Jul–Des)' },
    ]
  }
  return props.resume?.filter_options?.bulans || []
})

function onPeriodModeChange(mode) {
  filters.value.period_mode = mode
  filters.value.period_value = ''
}

function currentParams(extra = {}) {
  const params = {}

  Object.entries({ ...filters.value, ...extra }).forEach(([key, value]) => {
    if (String(value || '').trim() !== '') {
      params[key] = value
    }
  })

  return params
}

function applyFilters() {
  expandedRows.value = {}
  router.get(route(resumeIndexRoute.value), currentParams(), {
    preserveScroll: true,
    preserveState: true,
  })
}

function resetFilters() {
  expandedRows.value = {}
  filters.value = {
    kategori_id: '',
    user_id: '',
    status: '',
    tahun: '',
    period_mode: 'bulan',
    period_value: '',
    q: '',
  }

  router.get(route(resumeIndexRoute.value), {}, {
    preserveScroll: true,
  })
}

function exportUrl(format) {
  return route('resume.export', {
    type: resumeType.value,
    ...currentParams({ format }),
  })
}

function toggleRow(rowId) {
  const key = String(rowId)
  expandedRows.value[key] = !isExpanded(rowId)
}

function isExpanded(rowId) {
  const key = String(rowId)
  // default is expanded; only false when explicitly set to false
  return !(key in expandedRows.value) || expandedRows.value[key] === true
}

function shouldShowDetail(rowId) {
  return isExpanded(rowId)
}

function drilldownByCategory(rowId) {
  return summaryDrilldown.value?.[String(rowId)] || summaryDrilldown.value?.[rowId] || []
}

function openFilePopup(type, files, lokasi) {
  const list = Array.isArray(files) ? files : []
  filePopup.value = {
    open: true,
    title: `${type} Lokasi`,
    lokasi: lokasi || '-',
    files: list,
    selectedFileId: list[0]?.id ?? null,
  }
}

function selectPopupFile(file) {
  filePopup.value.selectedFileId = file?.id ?? null
}

function isImageFile(file) {
  const type = String(file?.file_type || '').toLowerCase()
  const name = String(file?.file_name || '').toLowerCase()
  return type.startsWith('image/') || /\.(png|jpe?g|gif|webp)$/i.test(name)
}

function isPdfFile(file) {
  const type = String(file?.file_type || '').toLowerCase()
  const name = String(file?.file_name || '').toLowerCase()
  return type.includes('pdf') || /\.pdf$/i.test(name)
}

function closeFilePopup() {
  filePopup.value = {
    open: false,
    title: '',
    lokasi: '',
    files: [],
    selectedFileId: null,
  }
}

watch(
  () => props.resume?.selected_filters,
  () => {
    expandedRows.value = {}
  },
  { deep: true }
)

function compactMillion(value) {
  return (Number(value || 0) / 1000000).toLocaleString('id-ID', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
  })
}

function formatNumber(value) {
  return Number(value || 0).toLocaleString('id-ID')
}

function formatPercent(value) {
  return Number(value || 0).toLocaleString('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  })
}

function serapanClass(percent) {
  const v = Number(percent || 0)
  if (v >= 100) return 'text-emerald-600 font-semibold'
  if (v >= 80) return 'text-blue-600'
  if (v >= 50) return 'text-amber-600'
  return 'text-red-500'
}
</script>
