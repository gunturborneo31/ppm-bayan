<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="flex w-full items-center justify-between rounded-xl border bg-white px-3 py-2.5 text-left text-sm transition"
      :class="buttonClass"
      :disabled="disabled"
      @click="toggleOpen"
    >
      <div class="min-w-0">
        <p v-if="selectedOption" class="truncate font-medium text-slate-800">{{ optionLabel(selectedOption) }}</p>
        <p v-else class="truncate text-slate-400">{{ placeholder }}</p>
      </div>
      <svg class="h-4 w-4 shrink-0 text-slate-500 transition" :class="isOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>

    <p v-if="errorMessage" class="mt-1 text-xs font-medium text-red-600">{{ errorMessage }}</p>

    <div
      v-if="isOpen"
      class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.18)]"
    >
      <div class="border-b border-slate-100 p-3">
        <div class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 104.26 8.98l2.63 2.63a.75.75 0 101.06-1.06l-2.63-2.63A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
            </svg>
          </span>
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100"
            :placeholder="searchPlaceholder"
            @keydown.down.prevent="moveHighlight(1)"
            @keydown.up.prevent="moveHighlight(-1)"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.esc.prevent="close"
          />
        </div>
      </div>

      <div class="max-h-64 overflow-y-auto p-2">
        <button
          v-for="(option, index) in filteredOptions"
          :key="optionKey(option, index)"
          type="button"
          class="flex w-full items-start justify-between gap-3 rounded-xl px-3 py-2.5 text-left transition"
          :class="optionClass(option, index)"
          @mouseenter="highlightedIndex = index"
          @click="selectOption(option)"
        >
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold">{{ optionLabel(option) }}</p>
            <p v-if="optionDescription(option)" class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ optionDescription(option) }}</p>
          </div>
          <span v-if="isSelected(option)" class="mt-0.5 shrink-0 text-blue-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.415 0l-3.25-3.25a1 1 0 011.414-1.42l2.543 2.544 6.543-6.544a1 1 0 011.415 0z" clip-rule="evenodd" />
            </svg>
          </span>
        </button>

        <div v-if="!filteredOptions.length" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-400">
          {{ emptyText }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: 'Pilih data',
  },
  searchPlaceholder: {
    type: String,
    default: 'Cari data...',
  },
  emptyText: {
    type: String,
    default: 'Data tidak ditemukan.',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  labelKey: {
    type: String,
    default: 'nama',
  },
  valueKey: {
    type: String,
    default: 'id',
  },
  descriptionKey: {
    type: String,
    default: 'deskripsi',
  },
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)
const searchInput = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const highlightedIndex = ref(0)

const selectedOption = computed(() => {
  return (props.options || []).find((option) => String(option?.[props.valueKey]) === String(props.modelValue ?? '')) || null
})

const filteredOptions = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()
  if (!keyword) {
    return props.options || []
  }

  return (props.options || []).filter((option) => {
    const label = optionLabel(option).toLowerCase()
    const description = optionDescription(option).toLowerCase()
    return label.includes(keyword) || description.includes(keyword)
  })
})

const buttonClass = computed(() => {
  if (props.disabled) {
    return 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400'
  }

  if (props.error) {
    return 'border-red-300 bg-red-50/60 text-red-700 hover:border-red-400'
  }

  return 'border-slate-300 text-slate-700 hover:border-slate-400 hover:bg-slate-50'
})

watch(isOpen, async (open) => {
  if (!open) {
    searchQuery.value = ''
    highlightedIndex.value = 0
    return
  }

  await nextTick()
  searchInput.value?.focus()
})

watch(filteredOptions, (options) => {
  if (!options.length) {
    highlightedIndex.value = 0
    return
  }

  if (highlightedIndex.value > options.length - 1) {
    highlightedIndex.value = options.length - 1
  }
})

function optionLabel(option) {
  return String(option?.[props.labelKey] || '')
}

function optionDescription(option) {
  return String(option?.[props.descriptionKey] || '')
}

function optionKey(option, index) {
  return `${option?.[props.valueKey] ?? 'opt'}-${index}`
}

function isSelected(option) {
  return String(option?.[props.valueKey]) === String(props.modelValue ?? '')
}

function optionClass(option, index) {
  if (isSelected(option)) {
    return 'bg-blue-50 text-blue-700'
  }

  if (index === highlightedIndex.value) {
    return 'bg-slate-100 text-slate-800'
  }

  return 'text-slate-700 hover:bg-slate-50'
}

function toggleOpen() {
  if (props.disabled) {
    return
  }

  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function selectOption(option) {
  emit('update:modelValue', option?.[props.valueKey] ?? null)
  close()
}

function moveHighlight(direction) {
  if (!filteredOptions.value.length) {
    return
  }

  const total = filteredOptions.value.length
  highlightedIndex.value = (highlightedIndex.value + direction + total) % total
}

function selectHighlighted() {
  if (!filteredOptions.value.length) {
    return
  }

  selectOption(filteredOptions.value[highlightedIndex.value])
}

function handleOutsideClick(event) {
  if (!root.value || root.value.contains(event.target)) {
    return
  }

  close()
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
})
</script>