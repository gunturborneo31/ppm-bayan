<template>
  <AppLayout title="Manajemen Pilar">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Pilar</button>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Anggaran</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ amount(summary?.total_anggaran) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Terpakai</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">Rp {{ amount(summary?.total_terpakai) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Sisa</p>
        <p class="mt-2 text-2xl font-bold" :class="Number(summary?.total_sisa || 0) < 0 ? 'text-red-600' : 'text-green-600'">Rp {{ amount(summary?.total_sisa) }}</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">No Urut</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Icon</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Pilar</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Warna</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Deskripsi</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Anggaran Pilar</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Terpakai Program</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Sisa</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in pilars" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ Number(p.no_urut || 0) }}</td>
            <td class="px-4 py-3 text-xl leading-none">
              <span v-if="isMaterialIconName(p.icon)" class="material-icons text-[20px] text-gray-700">{{ p.icon }}</span>
              <span v-else>{{ p.icon || 'category' }}</span>
            </td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ p.nama }}</td>
            <td class="px-4 py-3">
              <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-2.5 py-1">
                <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: p.warna || '#64748b' }"></span>
                <span class="text-xs font-medium text-gray-600">{{ p.warna || '#64748b' }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ p.deskripsi }}</td>
            <td class="px-4 py-3 text-right text-sm font-medium text-gray-800 whitespace-nowrap">Rp {{ amount(p.rencana_biaya) }}</td>
            <td class="px-4 py-3 text-right text-sm text-gray-700 whitespace-nowrap">Rp {{ amount(p.total_program_biaya) }}</td>
            <td class="px-4 py-3 text-right text-sm font-medium whitespace-nowrap" :class="Number(p.sisa_anggaran || 0) < 0 ? 'text-red-600' : 'text-green-600'">Rp {{ amount(p.sisa_anggaran) }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(p)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(p)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="pilars.length === 0">
            <td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada pilar.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Pilar' : 'Tambah Pilar'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">No Urut</label>
          <input v-model.number="form.no_urut" class="input" :class="{'input-error': form.errors.no_urut}" type="number" min="0" />
          <p v-if="form.errors.no_urut" class="err">{{ form.errors.no_urut }}</p>
        </div>
        <div>
          <label class="label">Nama Pilar</label>
          <input v-model="form.nama" class="input" :class="{'input-error': form.errors.nama}" type="text" />
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <div>
          <label class="label">Deskripsi</label>
          <textarea v-model="form.deskripsi" class="input h-20 resize-none" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Warna Pilar</label>
            <div class="flex items-center gap-3">
              <input v-model="form.warna" class="h-10 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1" type="color" />
              <input v-model="form.warna" class="input" :class="{'input-error': form.errors.warna}" type="text" placeholder="#64748b" maxlength="7" />
            </div>
            <p v-if="form.errors.warna" class="err">{{ form.errors.warna }}</p>
            <button
              v-if="matchedPreset"
              type="button"
              @click="applyMatchedPreset()"
              class="mt-2 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100"
            >
              Gunakan warna default Dashboard Public: {{ matchedPreset.warna }}
            </button>
            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Preset Warna Dashboard Public</p>
              <div class="mt-2 flex flex-wrap gap-2">
                <button
                  v-for="preset in colorPresets"
                  :key="preset.nama"
                  type="button"
                  @click="form.warna = preset.warna"
                  class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:bg-white"
                  :title="preset.nama"
                >
                  <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: preset.warna }"></span>
                  {{ preset.nama }}
                </button>
              </div>
            </div>
          </div>
          <div>
            <label class="label">Icon Pilar (Material Icon atau emoji)</label>
            <input v-model="form.icon" class="input" :class="{'input-error': form.errors.icon}" type="text" placeholder="category / school / 🌱" maxlength="32" />
            <p v-if="form.errors.icon" class="err">{{ form.errors.icon }}</p>
            <button
              v-if="matchedIconPreset"
              type="button"
              @click="applyMatchedIconPreset()"
              class="mt-2 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100"
            >
              Pakai icon default Dashboard Public: {{ matchedIconPreset.icon }}
            </button>
            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600">
              <span class="text-gray-500">Preview:</span>
              <span v-if="isMaterialIconName(form.icon)" class="material-icons text-[16px]">{{ form.icon }}</span>
              <span v-else>{{ form.icon || 'category' }}</span>
            </div>
            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Opsi Icon Cepat</p>
              <input
                v-model="iconSearch"
                type="text"
                class="mt-2 w-full rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs placeholder-gray-400 focus:border-blue-400 focus:outline-none"
                placeholder="Cari icon..."
              />
              <div class="mt-2 flex flex-wrap gap-2">
                <button
                  v-for="option in filteredIconOptions"
                  :key="option.icon"
                  type="button"
                  @click="form.icon = option.icon"
                  class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:bg-white"
                  :title="`${option.label} - ${option.icon}`"
                >
                  <span v-if="isMaterialIconName(option.icon)" class="material-icons text-[14px]">{{ option.icon }}</span>
                  <span v-else>{{ option.icon }}</span>
                  {{ option.label }}
                </button>
                <p v-if="filteredIconOptions.length === 0" class="text-[11px] text-gray-400">Tidak ditemukan.</p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <label class="label">Anggaran Pilar (Rp)</label>
          <input :value="form.rencana_biaya" @input="onNumberInput('rencana_biaya', $event.target.value)" class="input text-right" :class="{'input-error': form.errors.rencana_biaya}" type="text" inputmode="numeric" />
          <p v-if="form.errors.rencana_biaya" class="err">{{ form.errors.rencana_biaya }}</p>
        </div>
        <div class="flex justify-between items-center">
          <button
            v-if="matchedPreset || matchedIconPreset"
            type="button"
            @click="applySyncDefault()"
            class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
          >
            <span class="material-icons text-[14px]">sync</span>
            Sinkronkan Warna &amp; Icon Default
          </button>
          <div class="flex gap-3 justify-end ml-auto">
            <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
          </div>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Pilar" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus pilar <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deletePilar" class="btn-danger">Hapus</button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ pilars: Array, summary: Object, colorPresets: Array, iconPresets: Array, iconOptions: Array })
const showModal = ref(false), showDeleteModal = ref(false), editing = ref(null), deleting = ref(null)
const form = useForm({ no_urut: 0, nama: '', deskripsi: '', rencana_biaya: '', warna: '#64748b', icon: 'category' })
const iconSearch = ref('')
const colorPresets = computed(() => props.colorPresets || [])
const iconPresets = computed(() => props.iconPresets || [])
const iconOptions = computed(() => props.iconOptions || [])
const filteredIconOptions = computed(() => {
  const q = iconSearch.value.trim().toLowerCase()
  if (!q) return iconOptions.value
  return iconOptions.value.filter((o) =>
    o.label.toLowerCase().includes(q) || o.icon.toLowerCase().includes(q)
  )
})
const matchedPreset = computed(() => {
  const key = String(form.nama || '').trim().toLowerCase()
  if (!key) return null
  return colorPresets.value.find((item) => String(item.nama || '').trim().toLowerCase() === key) || null
})
const matchedIconPreset = computed(() => {
  const key = String(form.nama || '').trim().toLowerCase()
  if (!key) return null
  return iconPresets.value.find((item) => String(item.nama || '').trim().toLowerCase() === key) || null
})

function amount(v) { return Number(v || 0).toLocaleString('id-ID') }
function normalize(v) {
  if (v === null || v === undefined || v === '') return ''
  return String(v).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.]/g, '')
}
function onNumberInput(field, value) {
  const clean = String(value || '').replace(/\D/g, '')
  form[field] = clean ? Number(clean).toLocaleString('id-ID') : ''
}
function isMaterialIconName(icon) {
  return /^[a-z0-9_]+$/.test(String(icon || '').trim())
}
function applySyncDefault() {
  if (matchedPreset.value) form.warna = matchedPreset.value.warna
  if (matchedIconPreset.value) form.icon = matchedIconPreset.value.icon
}
function applyMatchedPreset() {
  if (!matchedPreset.value) return
  form.warna = matchedPreset.value.warna
}
function applyMatchedIconPreset() {
  if (!matchedIconPreset.value) return
  form.icon = matchedIconPreset.value.icon
}

function openCreate() {
  editing.value = null
  form.reset()
  form.no_urut = 0
  form.rencana_biaya = ''
  form.warna = '#64748b'
  form.icon = 'category'
  iconSearch.value = ''
  showModal.value = true
}
function openEdit(p) {
  editing.value = p
  form.no_urut = Number(p.no_urut || 0)
  form.nama = p.nama
  form.deskripsi = p.deskripsi || ''
  form.rencana_biaya = amount(p.rencana_biaya)
  form.warna = p.warna || '#64748b'
  form.icon = p.icon || 'category'
  iconSearch.value = ''
  showModal.value = true
}
function closeModal() { showModal.value = false; iconSearch.value = ''; form.clearErrors() }
function save() {
  form.transform((data) => ({
    ...data,
    no_urut: Number.isFinite(Number(data.no_urut)) ? Number(data.no_urut) : 0,
    rencana_biaya: normalize(data.rencana_biaya),
    warna: String(data.warna || '').trim(),
    icon: String(data.icon || '').trim(),
  }))

  editing.value
    ? form.put(route('pilar.update', editing.value.id), { onSuccess: closeModal })
    : form.post(route('pilar.store'), { onSuccess: closeModal })
}
function confirmDelete(p) { deleting.value = p; showDeleteModal.value = true }
function deletePilar() { router.delete(route('pilar.destroy', deleting.value.id), { onSuccess: () => { showDeleteModal.value = false } }) }
</script>
