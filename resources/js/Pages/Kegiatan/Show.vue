<template>
  <AppLayout :title="kegiatan.nama">
    <template #header-actions>
      <Link :href="route('kegiatan.index')" class="btn-secondary text-sm">← Kembali</Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ kegiatan.nama }}</h2>
              <p class="text-sm text-gray-500 mt-1">{{ kegiatan.program?.nama }} · {{ kegiatan.divisi?.nama }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-medium" :class="statusBadge(kegiatan.status)">
              {{ kegiatan.status_label }}
            </span>
          </div>

          <p v-if="kegiatan.deskripsi" class="mt-4 text-gray-600 text-sm">{{ kegiatan.deskripsi }}</p>

          <div v-if="kegiatan.catatan_revisi" class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
            <p class="text-sm font-medium text-yellow-800">Catatan Revisi:</p>
            <p class="text-sm text-yellow-700 mt-1">{{ kegiatan.catatan_revisi }}</p>
          </div>

          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Target Output</p>
              <p class="font-semibold text-gray-800 mt-0.5">{{ kegiatan.target_output }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Rencana Biaya</p>
              <p class="font-semibold text-gray-800 mt-0.5 whitespace-nowrap">{{ fmt(kegiatan.rencana_biaya) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Realisasi Output</p>
              <p class="font-semibold mt-0.5" style="color:#D7561E">
                {{ kegiatan.realisasis?.reduce((s,r) => s + Number(r.realisasi_output||0), 0) }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Progress</p>
              <p class="font-semibold mt-0.5" style="color:#D7561E">{{ kegiatan.progress }}%</p>
            </div>
          </div>

          <div class="mt-4">
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all" style="background:#D7561E" :style="{ width: kegiatan.progress + '%' }" />
            </div>
          </div>

          <!-- Pilars -->
          <div v-if="kegiatan.pilars?.length" class="mt-4 flex flex-wrap gap-2">
            <span v-for="p in kegiatan.pilars" :key="p.id"
              class="px-2 py-0.5 rounded-full text-xs border"
              style="background:rgba(215,86,30,0.08);color:#D7561E;border-color:rgba(215,86,30,0.2)">
              {{ p.nama }}
            </span>
          </div>
        </div>

        <!-- Realisasi Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Realisasi Per Triwulan</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Periode</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">Realisasi Output</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">Realisasi Biaya</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="r in kegiatan.realisasis" :key="r.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">{{ r.periode?.tahun }} {{ r.periode?.triwulan }}</td>
                <td class="px-4 py-3 text-right">{{ r.realisasi_output }}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">{{ fmt(r.realisasi_biaya) }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ r.keterangan }}</td>
              </tr>
              <tr v-if="!kegiatan.realisasis?.length">
                <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada realisasi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sidebar Info -->
      <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-800 mb-4">Informasi</h3>
          <dl class="space-y-3 text-sm">
            <div>
              <dt class="text-gray-500">Versi</dt>
              <dd class="font-medium">v{{ kegiatan.version }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Dibuat</dt>
              <dd>{{ formatDate(kegiatan.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Diperbarui</dt>
              <dd>{{ formatDate(kegiatan.updated_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Files -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">File Lampiran</h3>
          </div>
          <div class="divide-y">
            <div v-for="f in kegiatan.files" :key="f.id" class="px-4 py-3 flex items-center justify-between">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-700 truncate">{{ f.file_name }}</p>
                <p class="text-xs text-gray-400">{{ f.kategori }}</p>
              </div>
              <a :href="route('files.download', f.id)" class="text-xs ml-2 flex-shrink-0 transition-colors" style="color:#D7561E">
                Unduh
              </a>
            </div>
            <div v-if="!kegiatan.files?.length" class="px-4 py-4 text-center text-sm text-gray-400">
              Belum ada file.
            </div>
          </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Riwayat Aktivitas</h3>
          </div>
          <div class="divide-y max-h-96 overflow-y-auto">
            <div v-for="log in logs" :key="log.id" class="px-4 py-3 text-sm">
              <div class="flex gap-2">
                <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white" style="background:#D7561E">
                  {{ log.user?.name?.[0] }}
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-gray-800 font-medium leading-tight">{{ log.user?.name }}</p>
                  <p class="text-gray-600 text-xs mt-0.5">{{ log.description }}</p>
                  <p class="text-gray-400 text-xs mt-1">{{ formatDate(log.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-if="!logs?.length" class="px-4 py-4 text-center text-sm text-gray-400">
              Belum ada aktivitas.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ kegiatan: Object, logs: Array })

function fmt(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') }
function formatDate(d) { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
function statusBadge(s) {
  return { draft: 'bg-gray-100 text-gray-600', diajukan: 'bg-orange-100 text-orange-700',
           revisi: 'bg-yellow-100 text-yellow-700', disetujui: 'bg-green-100 text-green-700',
           ditolak: 'bg-red-100 text-red-700', selesai: 'bg-purple-100 text-purple-700' }[s] || 'bg-gray-100 text-gray-600'
}
</script>
