<template>
  <AppLayout :title="program.nama">
    <template #header-actions>
      <Link :href="route('program.index')" class="btn-secondary text-sm">← Kembali</Link>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ program.nama }}</h2>
              <p class="text-sm text-gray-500 mt-1">{{ program.user?.name }}</p>
            </div>
          </div>

          <p v-if="program.deskripsi" class="mt-4 text-gray-600 text-sm">{{ program.deskripsi }}</p>

          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Target Output</p>
              <p class="font-semibold text-gray-800 mt-0.5">{{ program.target_output }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Satuan</p>
              <p class="font-semibold text-gray-800 mt-0.5">{{ program.satuan }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Rencana Biaya</p>
              <p class="font-semibold text-gray-800 mt-0.5 whitespace-nowrap">{{ fmt(program.rencana_biaya) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-xs text-gray-500">Sisa Anggaran</p>
              <p class="font-semibold mt-0.5 whitespace-nowrap" :class="program.sisa_anggaran < 0 ? 'text-red-600' : 'text-green-600'">
                {{ fmt(program.sisa_anggaran) }}
              </p>
            </div>
          </div>

          <div class="mt-4">
            <p class="text-xs font-medium text-gray-500 mb-2">Penggunaan Anggaran</p>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all" style="background:#D7561E" :style="{ width: (program.total_kegiatan_biaya / program.rencana_biaya * 100) + '%' }" />
            </div>
            <p class="mt-1 text-xs text-gray-600 whitespace-nowrap">
              {{ fmt(program.total_kegiatan_biaya) }} / {{ fmt(program.rencana_biaya) }}
            </p>
          </div>
        </div>

        <!-- Kegiatan Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Kegiatan ({{ program.kegiatans?.length || 0 }})</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Divisi</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">Rencana Biaya</th>
                <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">Progress</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="k in program.kegiatans" :key="k.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-600">{{ k.divisi?.nama }}</td>
                <td class="px-4 py-3">
                  <Link :href="route('kegiatan.show', k.id)" class="font-medium text-gray-800 hover:underline" style="color:#D7561E">
                    {{ k.nama }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ fmt(k.rencana_biaya) }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(k.status)">
                    {{ k.status_label }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <span class="font-semibold" style="color:#D7561E">{{ k.progress }}%</span>
                </td>
              </tr>
              <tr v-if="!program.kegiatans?.length">
                <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada kegiatan di program ini.</td>
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
              <dt class="text-gray-500">Dibuat</dt>
              <dd>{{ formatDate(program.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Diperbarui</dt>
              <dd>{{ formatDate(program.updated_at) }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Total Kegiatan</dt>
              <dd class="font-medium text-gray-800">{{ program.kegiatans?.length || 0 }}</dd>
            </div>
          </dl>
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

defineProps({ program: Object, logs: Array })

function fmt(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') }
function formatDate(d) { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
function statusBadge(s) {
  return { draft: 'bg-gray-100 text-gray-600', diajukan: 'bg-orange-100 text-orange-700',
           revisi: 'bg-yellow-100 text-yellow-700', disetujui: 'bg-green-100 text-green-700',
           ditolak: 'bg-red-100 text-red-700', selesai: 'bg-purple-100 text-purple-700' }[s] || 'bg-gray-100 text-gray-600'
}
</script>
