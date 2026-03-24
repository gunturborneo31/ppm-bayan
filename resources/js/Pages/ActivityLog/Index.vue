<template>
  <AppLayout title="Activity Log">
    <div class="bg-white rounded-xl shadow-sm border">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Riwayat Aktivitas</h3>
        <p class="text-sm text-gray-500">{{ logs.total }} entri</p>
      </div>
      <div class="divide-y">
        <div v-for="log in logs.data" :key="log.id" class="px-6 py-4 flex gap-4">
          <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
            :class="actionColor(log.action)">
            <span class="text-xs font-bold text-white">{{ log.user?.name?.[0]?.toUpperCase() || '?' }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-medium text-gray-800 text-sm">{{ log.user?.name || 'System' }}</span>
              <span class="px-1.5 py-0.5 rounded text-xs font-medium" :class="actionBadge(log.action)">
                {{ log.action }}
              </span>
              <span class="text-xs text-gray-500 capitalize">{{ log.module }}</span>
            </div>
            <p class="text-sm text-gray-600 mt-0.5">{{ log.description }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ formatDate(log.created_at) }}</p>
          </div>
        </div>
        <div v-if="!logs.data?.length" class="px-6 py-8 text-center text-gray-400">
          Belum ada aktivitas.
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="logs.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Halaman {{ logs.current_page }} dari {{ logs.last_page }}
        </p>
        <div class="flex gap-2">
          <Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50">
            ← Sebelumnya
          </Link>
          <Link v-if="logs.next_page_url" :href="logs.next_page_url" class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50">
            Berikutnya →
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ logs: Object })

function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function actionColor(action) {
  return { create: 'bg-green-500', update: 'bg-blue-500', delete: 'bg-red-500',
           approve: 'bg-green-600', reject: 'bg-red-600', revisi: 'bg-yellow-500',
           submit: 'bg-blue-400', upload: 'bg-purple-500' }[action] || 'bg-gray-400'
}
function actionBadge(action) {
  return { create: 'bg-green-100 text-green-700', update: 'bg-blue-100 text-blue-700',
           delete: 'bg-red-100 text-red-700', approve: 'bg-green-100 text-green-700',
           reject: 'bg-red-100 text-red-700', revisi: 'bg-yellow-100 text-yellow-700',
           submit: 'bg-blue-100 text-blue-700', upload: 'bg-purple-100 text-purple-700' }[action] || 'bg-gray-100 text-gray-700'
}
</script>
