<template>
  <AppLayout title="Pengaturan Sistem">
    <div class="space-y-6">
      <!-- Planning Lock Setting -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-800">Pengaturan Perencanaan</h2>
          <p class="text-sm text-gray-500 mt-1">Kelola akses pengguna untuk input perencanaan</p>
        </div>

        <div class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-medium text-gray-800">Kunci Perencanaan</h3>
              <p class="text-sm text-gray-500 mt-1">
                Ketika dikunci, hanya superadmin yang dapat menginput atau mengubah perencanaan
              </p>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right">
                <p class="text-sm font-medium" :class="planningLocked ? 'text-red-600' : 'text-green-600'">
                  {{ planningLocked ? 'DIKUNCI' : 'TERBUKA' }}
                </p>
              </div>
              <button
                @click="togglePlanningLock"
                :disabled="toggling"
                class="px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                :class="planningLocked 
                  ? 'bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50' 
                  : 'bg-green-100 text-green-700 hover:bg-green-200 disabled:opacity-50'"
              >
                {{ toggling ? 'Memproses...' : (planningLocked ? 'Buka Kunci' : 'Kunci') }}
              </button>
            </div>
          </div>

          <!-- Status Card -->
          <div class="mt-6 p-4 rounded-lg" :class="planningLocked ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'">
            <div class="flex items-start gap-3">
              <div class="text-2xl" :class="planningLocked ? 'text-red-600' : 'text-green-600'">
                {{ planningLocked ? '🔒' : '🔓' }}
              </div>
              <div>
                <p class="font-medium" :class="planningLocked ? 'text-red-900' : 'text-green-900'">
                  {{ planningLocked ? 'Perencanaan Terkunci' : 'Perencanaan Terbuka' }}
                </p>
                <p class="text-sm mt-1" :class="planningLocked ? 'text-red-700' : 'text-green-700'">
                  {{ planningLocked 
                    ? 'Hanya superadmin yang dapat menambah atau mengedit perencanaan. User divisi tidak dapat mengakses fitur input perencanaan.' 
                    : 'Semua user yang memiliki akses dapat menambah dan mengedit perencanaan.' 
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Informasi Tambahan -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex gap-3">
          <div class="text-blue-600 text-xl">ℹ️</div>
          <div>
            <h4 class="font-medium text-blue-900">Catatan</h4>
            <ul class="text-sm text-blue-800 mt-2 space-y-1 list-disc list-inside">
              <li>Semua perubahan pengaturan dicatat dalam activity log</li>
              <li>Superadmin selalu dapat menginput perencanaan terlepas dari status kunci</li>
              <li>User divisi hanya dapat mengedit kegiatan dalam status REVISI atau DRAFT</li>
              <li>Silakan hubungi developer untuk menambah pengaturan lainnya</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  settings: Array,
  planningLocked: Boolean,
})

const toggling = ref(false)
const planningLocked = ref(props.planningLocked)

const togglePlanningLock = () => {
  if (toggling.value) return

  toggling.value = true
  router.post(route('settings.togglePlanningLock'), {}, {
    onSuccess: () => {
      planningLocked.value = !planningLocked.value
      toggling.value = false
    },
    onError: () => {
      toggling.value = false
    },
  })
}
</script>
