<template>
  <div class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
    <transition-group name="toast" tag="div" class="space-y-2">
      <div v-for="t in toasts" :key="t.id"
        class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-sm text-sm max-w-sm"
        :class="cls(t.type)">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="t.type==='success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          <path v-else-if="t.type==='error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p>{{ t.message }}</p>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const toasts = ref([])

function add(message, type = 'success') {
  const id = Date.now()
  toasts.value.push({ id, message, type })
  setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id) }, 4000)
}

function cls(type) {
  return { success: 'bg-white text-green-700 border border-green-200',
           error:   'bg-white text-red-700 border border-red-200',
           warning: 'bg-white text-yellow-700 border border-yellow-200' }[type] || 'bg-white text-gray-700 border border-gray-200'
}

watch(() => page.props.flash, (f) => {
  if (f?.success) add(f.success, 'success')
  if (f?.error)   add(f.error, 'error')
  if (f?.warning) add(f.warning, 'warning')
}, { deep: true, immediate: true })
</script>

<style>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>
