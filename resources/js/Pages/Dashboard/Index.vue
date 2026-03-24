<template>
  <AppLayout title="Dashboard">
    <!-- CDO Stats -->
    <template v-if="isSuperadmin">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Total Program</p>
          <p class="text-3xl font-bold text-blue-700 mt-1">{{ stats.total_program }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Total Kegiatan</p>
          <p class="text-3xl font-bold text-blue-700 mt-1">{{ stats.total_kegiatan }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Menunggu Verifikasi</p>
          <p class="text-3xl font-bold text-yellow-600 mt-1">{{ stats.kegiatan_status?.diajukan ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Disetujui</p>
          <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.kegiatan_status?.disetujui ?? 0 }}</p>
        </div>
      </div>

      <!-- Status Breakdown -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <h3 class="font-semibold text-gray-800 mb-4">Status Kegiatan</h3>
          <div class="space-y-3">
            <div v-for="(count, status) in stats.kegiatan_status" :key="status"
              class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :class="statusDot(status)" />
                <span class="text-sm text-gray-600 capitalize">{{ status }}</span>
              </div>
              <span class="font-medium">{{ count }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <h3 class="font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
          <div class="space-y-3 max-h-64 overflow-y-auto">
            <div v-for="log in stats.recent_logs" :key="log.id" class="flex gap-3 text-sm">
              <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-blue-700 text-xs font-bold">{{ log.user?.name?.[0] }}</span>
              </div>
              <div>
                <p class="text-gray-700">{{ log.description }}</p>
                <p class="text-gray-400 text-xs">{{ log.user?.name }} · {{ formatDate(log.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Divisi Stats -->
    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Total Kegiatan Saya</p>
          <p class="text-3xl font-bold text-blue-700 mt-1">{{ stats.total_kegiatan }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Perlu Revisi</p>
          <p class="text-3xl font-bold text-yellow-600 mt-1">{{ stats.kegiatan_status?.revisi ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
          <p class="text-sm text-gray-500">Disetujui</p>
          <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.kegiatan_status?.disetujui ?? 0 }}</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-6 py-4 border-b">
          <h3 class="font-semibold text-gray-800">Kegiatan Terbaru</h3>
        </div>
        <div class="divide-y">
          <div v-for="k in stats.kegiatan_list" :key="k.id" class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-800">{{ k.nama }}</p>
                <p class="text-sm text-gray-500">{{ k.program?.nama }}</p>
              </div>
              <div class="text-right">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusBadge(k.status)">
                  {{ k.status }}
                </span>
                <p class="text-sm text-gray-500 mt-1">{{ k.progress }}%</p>
              </div>
            </div>
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full" :style="{ width: k.progress + '%' }" />
            </div>
          </div>
        </div>
      </div>
    </template>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ stats: Object })

const page = usePage()
const isSuperadmin = computed(() => page.props.auth.user?.is_superadmin)

function statusDot(status) {
  return { draft: 'bg-gray-400', diajukan: 'bg-blue-500', revisi: 'bg-yellow-500',
           disetujui: 'bg-green-500', ditolak: 'bg-red-500', selesai: 'bg-purple-500' }[status] || 'bg-gray-400'
}

function statusBadge(status) {
  return { draft: 'bg-gray-100 text-gray-700', diajukan: 'bg-blue-100 text-blue-700',
           revisi: 'bg-yellow-100 text-yellow-700', disetujui: 'bg-green-100 text-green-700',
           ditolak: 'bg-red-100 text-red-700', selesai: 'bg-purple-100 text-purple-700' }[status] || 'bg-gray-100 text-gray-700'
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
