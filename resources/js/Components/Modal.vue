<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="$emit('close')">
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="$emit('close')" />
        <div class="relative bg-white rounded-xl shadow-2xl w-full" :class="maxWidthClass">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-6 py-4"><slot /></div>
          <div v-if="$slots.footer" class="px-6 py-4 border-t bg-gray-50 rounded-b-xl">
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
