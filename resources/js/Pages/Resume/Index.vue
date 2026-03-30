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
          <label class="label">Triwulan</label>
          <select v-model="filters.triwulan" class="input">
            <option value="">Semua triwulan</option>
            <option v-for="tw in resume.filter_options?.triwulans || []" :key="tw.value" :value="tw.value">{{ tw.label }}</option>
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
        <p class="mt-2 text-xs text-slate-500">Filter kategori + triwulan aktif</p>
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
                <td class="px-4 py-3 text-right text-gray-700">{{ row.jumlah_program ?? '-' }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ row.jumlah_kegiatan ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-700">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.total_anggaran) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-700">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.total_realisasi) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3" :class="Number(row.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                  <span class="ml-auto inline-grid w-full max-w-[190px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                    <span class="text-left">Rp</span>
                    <span>{{ formatNumber(row.sisa_anggaran) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-right text-gray-700">{{ formatPercent(row.persentase_serapan) }}%</td>
              </tr>

              <tr v-if="isExpanded(row.id)" class="bg-slate-50/70">
                <td :colspan="summaryTableColspan" class="px-4 py-4">
                  <div v-if="drilldownByCategory(row.id).length" class="space-y-3">
                    <div
                      v-for="program in drilldownByCategory(row.id)"
                      :key="`${row.id}-${program.id}`"
                      class="overflow-hidden rounded-xl border border-slate-200 bg-white"
                    >
                      <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2.5">
                        <p class="text-sm font-semibold text-slate-700">{{ program.nama }}</p>
                        <span class="text-xs font-medium text-slate-500">{{ program.jumlah_kegiatan }} kegiatan</span>
                      </div>
                      <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                          <thead class="bg-white text-slate-500">
                            <tr>
                              <th class="px-4 py-2 text-left font-medium">Kegiatan</th>
                              <th class="px-4 py-2 text-right font-medium">Perencanaan</th>
                              <th class="px-4 py-2 text-right font-medium">Realisasi</th>
                              <th class="px-4 py-2 text-right font-medium">Sisa</th>
                              <th class="px-4 py-2 text-right font-medium">Serapan</th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-slate-100">
                            <tr v-for="k in program.kegiatans" :key="k.id" class="hover:bg-slate-50">
                              <td class="px-4 py-2.5 font-medium text-slate-700">{{ k.nama }}</td>
                              <td class="px-4 py-2.5 text-slate-600">
                                <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                                  <span class="text-left">Rp</span>
                                  <span>{{ formatNumber(k.anggaran) }}</span>
                                </span>
                              </td>
                              <td class="px-4 py-2.5 text-slate-600">
                                <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                                  <span class="text-left">Rp</span>
                                  <span>{{ formatNumber(k.realisasi) }}</span>
                                </span>
                              </td>
                              <td class="px-4 py-2.5" :class="Number(k.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-emerald-600'">
                                <span class="ml-auto inline-grid w-full max-w-[180px] grid-cols-[24px_minmax(0,1fr)] items-center gap-1 text-right tabular-nums">
                                  <span class="text-left">Rp</span>
                                  <span>{{ formatNumber(k.sisa_anggaran) }}</span>
                                </span>
                              </td>
                              <td class="px-4 py-2.5 text-right text-slate-600">{{ formatPercent(k.persentase_serapan) }}%</td>
                            </tr>
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
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
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

const summaryTableColspan = computed(() => (resumeType.value === 'user' ? 9 : 8))

const expandedRows = ref({})
const summaryDrilldown = computed(() => props.resume?.summary_drilldown || {})

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
  triwulan: props.resume?.selected_filters?.triwulan || '',
  q: props.resume?.selected_filters?.q || '',
})

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
    triwulan: '',
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
  expandedRows.value[key] = !expandedRows.value[key]
}

function isExpanded(rowId) {
  return Boolean(expandedRows.value[String(rowId)])
}

function drilldownByCategory(rowId) {
  return summaryDrilldown.value?.[String(rowId)] || summaryDrilldown.value?.[rowId] || []
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
</script>
