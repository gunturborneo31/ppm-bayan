<template>
  <AppLayout title="Manajemen Pengumuman">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Pengumuman</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Judul</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Prioritas</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Terbit</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="item in pengumumans" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-800">{{ item.judul }}</p>
              <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ item.isi || '-' }}</p>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="priorityClass(item.prioritas)">{{ item.prioritas }}</span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-600">{{ formatDate(item.published_at) }}</td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(item)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(item)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="pengumumans.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada pengumuman.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Pengumuman' : 'Tambah Pengumuman'" @close="closeModal" max-width="2xl">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Judul</label>
          <input v-model="form.judul" class="input" :class="{'input-error': form.errors.judul}" type="text" />
          <p v-if="form.errors.judul" class="err">{{ form.errors.judul }}</p>
        </div>
        <div>
          <label class="label">Isi Pengumuman</label>
          <textarea v-model="form.isi" class="input h-24 resize-none" />
          <p v-if="form.errors.isi" class="err">{{ form.errors.isi }}</p>
        </div>
        <div>
          <label class="label">Link Gambar (opsional)</label>
          <input v-model="form.image" class="input" :class="{'input-error': form.errors.image}" type="text" placeholder="https://..." />
          <p v-if="form.errors.image" class="err">{{ form.errors.image }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Prioritas</label>
            <select v-model="form.prioritas" class="input" :class="{'input-error': form.errors.prioritas}">
              <option value="penting">penting</option>
              <option value="info">info</option>
              <option value="umum">umum</option>
            </select>
            <p v-if="form.errors.prioritas" class="err">{{ form.errors.prioritas }}</p>
          </div>
          <div>
            <label class="label">Tanggal Terbit (opsional)</label>
            <input v-model="form.published_at" class="input" :class="{'input-error': form.errors.published_at}" type="datetime-local" />
            <p v-if="form.errors.published_at" class="err">{{ form.errors.published_at }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
          <label for="is_active" class="text-sm text-gray-700">Aktifkan pengumuman</label>
        </div>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Pengumuman" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus pengumuman <strong>{{ deleting?.judul }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deletePengumuman" class="btn-danger">Hapus</button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

defineProps({ pengumumans: Array })

const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleting = ref(null)

const form = useForm({
  judul: '',
  isi: '',
  image: '',
  prioritas: 'umum',
  is_active: true,
  published_at: '',
})

function toLocalDateTimeValue(value) {
  if (!value) return ''
  return String(value).slice(0, 16)
}

function formatDate(value) {
  if (!value) return '-'
  const dt = new Date(value)
  if (Number.isNaN(dt.getTime())) return '-'
  return dt.toLocaleString('id-ID')
}

function priorityClass(level) {
  return {
    penting: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
    umum: 'bg-gray-100 text-gray-700',
  }[level] || 'bg-gray-100 text-gray-700'
}

function openCreate() {
  editing.value = null
  form.reset()
  form.prioritas = 'umum'
  form.is_active = true
  form.published_at = ''
  showModal.value = true
}

function openEdit(item) {
  editing.value = item
  form.judul = item.judul
  form.isi = item.isi || ''
  form.image = item.image || ''
  form.prioritas = item.prioritas || 'umum'
  form.is_active = Boolean(item.is_active)
  form.published_at = toLocalDateTimeValue(item.published_at)
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.clearErrors()
}

function save() {
  if (editing.value) {
    form.put(route('pengumuman.update', editing.value.id), { onSuccess: closeModal })
    return
  }
  form.post(route('pengumuman.store'), { onSuccess: closeModal })
}

function confirmDelete(item) {
  deleting.value = item
  showDeleteModal.value = true
}

function deletePengumuman() {
  router.delete(route('pengumuman.destroy', deleting.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
    },
  })
}
</script>
