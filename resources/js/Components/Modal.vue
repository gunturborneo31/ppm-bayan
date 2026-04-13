<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="$emit('close')">
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/20" @click="$emit('close')" />
        <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm w-full" :class="maxWidthClass">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-6 py-4"><slot /></div>
          <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  show:     { type: Boolean, default: false },
  title:    { type: String,  default: '' },
  maxWidth: { type: String,  default: 'lg' },
})
defineEmits(['close'])
const maxWidthClass = computed(() => ({
  sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg', xl: 'max-w-xl', '2xl': 'max-w-2xl',
}[props.maxWidth] || 'max-w-lg'))
</script>
