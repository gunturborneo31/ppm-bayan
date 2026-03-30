<template>
  <AppLayout title="Dashboard">
    <div class="mb-6 bg-white rounded-xl p-4 shadow-sm border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div v-if="isSuperadmin">
          <label class="label">Filter User</label>
          <select v-model="filters.user_id" class="input">
            <option value="">-- Semua User --</option>
            <option v-for="u in stats.filter_options?.users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div v-if="isSuperadmin">
          <label class="label">Filter Divisi</label>
          <select v-model="filters.divisi_id" class="input" :disabled="!isSuperadmin">
            <option value="">-- Semua Divisi --</option>
            <option v-for="d in stats.filter_options?.divisis" :key="d.id" :value="d.id">{{ d.nama }}</option>
          </select>
        </div>
        <div>
          <label class="label">Filter Pilar</label>
          <select v-model="filters.pilar_id" class="input">
            <option value="">-- Semua Pilar --</option>
            <option v-for="p in stats.filter_options?.pilars" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button @click="applyFilters" class="btn-primary px-4">Terapkan Filter</button>
          <button v-if="filters.user_id || filters.divisi_id || filters.pilar_id" @click="clearFilters" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition-colors text-sm font-medium">Reset</button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
      <div class="relative overflow-hidden rounded-2xl p-5 bg-[#062A57] text-white min-h-[170px]">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Total Pagu {{ stats.chart_year }}</p>
        <div class="mt-4 flex items-start gap-2">
          <span class="text-4xl font-bold leading-none">Rp {{ compactMiliar(stats.summary?.total_pagu) }}</span>
          <span class="text-sm font-semibold text-slate-300 mt-2">Miliar</span>
        </div>
        <div class="absolute -right-2 -bottom-4 w-20 h-20 rounded-2xl border border-white/20 bg-[#0A3A74]" />
        <div class="absolute right-6 bottom-6 w-8 h-8 rounded-xl border-2 border-white/20 flex items-center justify-center">
          <span class="w-2 h-2 rounded-full bg-[#1E4B80]" />
        </div>
      </div>

      <div class="rounded-2xl p-5 bg-[#F5F7FB] border border-[#E8EDF5] min-h-[170px]">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Rencana</p>
        <div class="mt-4 flex items-start gap-2 text-[#0A2A53]">
          <span class="text-4xl font-bold leading-none">Rp {{ compactMiliar(stats.summary?.total_rencana_biaya) }}</span>
          <span class="text-sm font-semibold text-slate-500 mt-2">Miliar</span>
        </div>
        <div class="mt-5 flex items-center gap-3">
          <div class="w-24 h-1.5 rounded-full bg-[#D9E1EC] overflow-hidden">
            <div class="h-full rounded-full bg-[#0A3C73]" :style="{ width: `${safePercent(stats.summary?.persentase_realisasi)}%` }" />
          </div>
          <span class="text-xs font-semibold text-slate-600">{{ formatPercent(stats.summary?.persentase_realisasi) }}%</span>
        </div>
      </div>

      <div class="rounded-2xl p-5 bg-white border border-[#A48A28] min-h-[170px] shadow-sm">
        <div class="flex items-center justify-between">
          <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Realisasi</p>
          <span class="w-2.5 h-2.5 rounded-full bg-[#8B7423]" />
        </div>
        <div class="mt-4 flex items-start gap-2 text-[#0A2A53]">
          <span class="text-4xl font-bold leading-none">Rp {{ compactMiliar(stats.summary?.total_realisasi_biaya) }}</span>
          <span class="text-sm font-semibold text-slate-500 mt-2">Miliar</span>
        </div>
        <p class="mt-5 text-xs font-medium text-[#8B7423]">Serapan {{ formatPercent(stats.summary?.persentase_realisasi) }}% dari total rencana</p>
      </div>

      <div class="rounded-2xl p-5 bg-[#F8FAFC] border border-[#E6EBF2] min-h-[170px]">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Sisa Anggaran</p>
        <div class="mt-4 flex items-start gap-2 text-[#0A2A53]">
          <span class="text-4xl font-bold leading-none">Rp {{ compactMiliar(stats.summary?.sisa_anggaran) }}</span>
          <span class="text-sm font-semibold text-slate-500 mt-2">Miliar</span>
        </div>
        <p class="mt-5 text-xs text-slate-500">Porsi sisa dana: {{ formatPercent(stats.summary?.persentase_sisa) }}%</p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-800">Realisasi Berdasarkan Bulan (Jan - Des)</h3>
          <span class="text-xs text-gray-400">{{ stats.chart_year }}</span>
        </div>
        <div class="h-64 flex items-end gap-2">
          <div v-for="row in stats.monthly_realisasi || []" :key="row.label" class="flex-1 flex flex-col items-center justify-end gap-2">
            <div class="w-full bg-orange-100 rounded-t-md relative" style="height: 180px;">
              <div class="absolute bottom-0 w-full bg-orange-500 rounded-t-md" :style="{ height: `${scaledHeight(row.total, maxMonthlyValue)}%` }" />
            </div>
            <span class="text-[11px] text-gray-600">{{ row.label }}</span>
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Program</p>
            <p class="text-2xl font-bold mt-1.5 text-orange-600">{{ stats.total_program ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Kegiatan</p>
            <p class="text-2xl font-bold mt-1.5 text-orange-600">{{ stats.total_kegiatan ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Kegiatan Butuh Verifikasi</p>
            <p class="text-2xl font-bold mt-1.5 text-amber-600">{{ stats.kegiatan_status?.diajukan ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Kegiatan Selesai</p>
            <p class="text-2xl font-bold mt-1.5 text-emerald-600">{{ stats.kegiatan_status?.selesai ?? 0 }}</p>
          </div>
        </div>

        <div class="bg-[#052A57] rounded-xl shadow-sm p-5 text-white">
          <h3 class="font-semibold text-base mb-4">Capaian per TW</h3>
          <div class="space-y-3">
            <div v-for="row in stats.quarterly_capaian || []" :key="row.label">
              <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-wide">
                <span class="text-slate-200">{{ row.label }}</span>
                <span v-if="Number(row.persentase || 0) > 0" class="text-white">{{ formatPercent(row.persentase) }}%</span>
                <span v-else class="text-slate-400">PLANNED</span>
              </div>
              <div class="mt-1.5 h-1.5 rounded-full bg-[#1A446E] overflow-hidden">
                <div class="h-full rounded-full bg-[#F2D052]" :style="{ width: `${safePercent(row.persentase)}%` }" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-[#F7F9FC] rounded-2xl shadow-sm border border-[#E8EDF5] p-5 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-[#16375E]">Realisasi per Pilar Utama</h3>
        <span class="text-xs text-slate-400">Urut anggaran terbesar</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="(row, idx) in (stats.pilar_summary || []).slice(0, 8)"
          :key="row.id"
          class="bg-[#EEF2F8] rounded-xl p-4 border border-[#E5EBF4]"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold" :class="pilarIconClass(idx)">
              {{ row.icon }}
            </div>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md" :class="pilarBadgeClass(row.persentase)">
              {{ formatPercent(row.persentase) }}%
            </span>
          </div>

          <p class="text-sm font-semibold text-[#16375E]">{{ row.nama }}</p>
          <div class="mt-1 space-y-1">
            <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
              <span>Anggaran</span>
              <span class="grid grid-cols-[18px_minmax(0,1fr)] items-center gap-1 min-w-[110px] text-right tabular-nums">
                <span class="text-left">Rp</span>
                <span>{{ compactThousand(row.total_anggaran) }} K</span>
              </span>
            </div>
            <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
              <span>Realisasi</span>
              <span class="grid grid-cols-[18px_minmax(0,1fr)] items-center gap-1 min-w-[110px] text-right tabular-nums">
                <span class="text-left">Rp</span>
                <span>{{ compactThousand(row.total_realisasi) }} K</span>
              </span>
            </div>
            <div class="flex items-center justify-between gap-3 text-xs" :class="Number(row.sisa || 0) < 0 ? 'text-red-600' : 'text-slate-500'">
              <span>Sisa</span>
              <span class="grid grid-cols-[18px_minmax(0,1fr)] items-center gap-1 min-w-[110px] text-right tabular-nums">
                <span class="text-left">Rp</span>
                <span>{{ compactThousand(row.sisa) }} K</span>
              </span>
            </div>
          </div>

          <div class="mt-3 h-1 rounded-full bg-[#D8E1ED] overflow-hidden">
            <div class="h-full rounded-full" :class="pilarBarClass(idx)" :style="{ width: `${safePercent(row.persentase)}%` }" />
          </div>
        </div>
      </div>

      <p v-if="!(stats.pilar_summary || []).length" class="text-center text-sm text-gray-400 py-6">
        Belum ada data pilar untuk ditampilkan.
      </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800">Data Tabel Per Divisi</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[920px]">
          <thead class="bg-gray-50 border-b border-gray-100 text-gray-600">
            <tr>
              <th class="text-left px-6 py-3 font-medium">Nama Divisi</th>
              <th class="text-right px-6 py-3 font-medium">Jumlah Program</th>
              <th class="text-right px-6 py-3 font-medium">Jumlah Kegiatan</th>
              <th class="text-right px-6 py-3 font-medium">Total Anggaran</th>
              <th class="text-right px-6 py-3 font-medium">Total Realisasi</th>
              <th class="text-right px-6 py-3 font-medium">Persentase Serapan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in (stats.divisi_summary || [])" :key="row.id" class="hover:bg-gray-50">
              <td class="px-6 py-3 font-medium text-gray-800">{{ row.nama }}</td>
              <td class="px-6 py-3 text-right text-gray-700">{{ row.jumlah_program }}</td>
              <td class="px-6 py-3 text-right text-gray-700">{{ row.jumlah_kegiatan }}</td>
              <td class="px-6 py-3 text-gray-700">
                <span class="inline-grid grid-cols-[18px_minmax(0,1fr)] items-center gap-1 min-w-[150px] ml-auto text-right tabular-nums">
                  <span class="text-left">Rp</span>
                  <span>{{ formatNumber(row.total_anggaran) }}</span>
                </span>
              </td>
              <td class="px-6 py-3 text-gray-700">
                <span class="inline-grid grid-cols-[18px_minmax(0,1fr)] items-center gap-1 min-w-[150px] ml-auto text-right tabular-nums">
                  <span class="text-left">Rp</span>
                  <span>{{ formatNumber(row.total_realisasi) }}</span>
                </span>
              </td>
              <td class="px-6 py-3 text-right">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700">
                  {{ formatPercent(row.persentase_serapan) }}%
                </span>
              </td>
            </tr>
            <tr v-if="!(stats.divisi_summary || []).length">
              <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data divisi untuk ditampilkan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ stats: Object })

const page = usePage()
const isSuperadmin = computed(() => props.stats?.is_superadmin || page.props.auth.user?.is_superadmin)
const maxMonthlyValue = computed(() => {
  const values = (props.stats?.monthly_realisasi || []).map((row) => Number(row.total || 0))
  return Math.max(1, ...values)
})

const filters = ref({
  user_id: page.props.stats?.selected_filters?.user_id || '',
  divisi_id: page.props.stats?.selected_filters?.divisi_id || '',
  pilar_id: page.props.stats?.selected_filters?.pilar_id || '',
})

function applyFilters() {
  const params = {}
  if (filters.value.user_id) params.user_id = filters.value.user_id
  if (filters.value.divisi_id) params.divisi_id = filters.value.divisi_id
  if (filters.value.pilar_id) params.pilar_id = filters.value.pilar_id
  router.get(route('dashboard'), params)
}

function clearFilters() {
  filters.value = { user_id: '', divisi_id: '', pilar_id: '' }
  router.get(route('dashboard'))
}

function safePercent(v) {
  return Math.max(0, Math.min(100, Number(v || 0)))
}

function formatPercent(v) {
  return Number(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
}

function scaledHeight(value, maxValue) {
  const ratio = maxValue > 0 ? Number(value || 0) / maxValue : 0
  return Math.max(2, Math.min(100, ratio * 100))
}

function compactMiliar(v) {
  const value = Number(v || 0) / 1000000000
  return value.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
}

function compactThousand(v) {
  const value = Number(v || 0) / 1000
  return value.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
}

function pilarBarClass(index) {
  return ['bg-[#0F4680]', 'bg-[#9A7B05]', 'bg-[#C32727]', 'bg-[#0F4680]'][index % 4]
}

function pilarIconClass(index) {
  return [
    'bg-[#E7EFF9] text-[#0F4680]',
    'bg-[#F8F1D8] text-[#9A7B05]',
    'bg-[#FBE3E3] text-[#C32727]',
    'bg-[#E7EFF9] text-[#0F4680]',
  ][index % 4]
}

function pilarBadgeClass(percent) {
  const value = Number(percent || 0)
  if (value >= 100) return 'bg-[#FBE3E3] text-[#C32727]'
  if (value >= 90) return 'bg-[#F8F1D8] text-[#9A7B05]'
  return 'bg-[#E7EFF9] text-[#0F4680]'
}

function formatNumber(v) {
  return Number(v || 0).toLocaleString('id-ID')
}
</script>
