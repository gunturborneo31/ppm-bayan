<template>
  <AppLayout title="Manajemen Lokasi">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Lokasi</button>
    </template>

    <div class="mb-4 bg-white rounded-xl border border-gray-100 p-4">
      <form @submit.prevent="applySearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input
          v-model="search.q"
          type="text"
          class="input"
          placeholder="Cari nama lokasi..."
        />
        <div class="flex gap-2">
          <button type="submit" class="btn-primary">Cari</button>
          <button type="button" class="btn-secondary" @click="resetSearch">Reset</button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Lokasi</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Dipakai di Kegiatan</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Dibuat</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="lokasi in lokasis" :key="lokasi.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ lokasi.nama }}</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex min-w-10 justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                {{ lokasi.kegiatan_lokasis_count || 0 }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ formatDate(lokasi.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(lokasi)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="openMerge(lokasi)" class="text-blue-600 hover:text-blue-700 text-xs">Gabung</button>
                <button @click="confirmDelete(lokasi)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="lokasis.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada lokasi.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Lokasi' : 'Tambah Lokasi'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Nama Lokasi</label>
          <input v-model="form.nama" class="input" :class="{ 'input-error': form.errors.nama }" type="text" />
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <p v-if="editing" class="text-xs text-gray-500">
          Perubahan nama akan ikut memperbarui lokasi yang sudah dipakai pada rincian kegiatan.
        </p>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Lokasi" @close="showDeleteModal = false">
      <div class="space-y-2">
        <p class="text-gray-600">Hapus lokasi <strong>{{ deleting?.nama }}</strong>?</p>
        <p v-if="(deleting?.kegiatan_lokasis_count || 0) > 0" class="text-xs text-amber-600">
          Lokasi yang masih dipakai pada kegiatan tidak bisa dihapus.
        </p>
      </div>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button
            @click="deleteLokasi"
            class="btn-danger"
            :disabled="(deleting?.kegiatan_lokasis_count || 0) > 0"
          >
            Hapus
          </button>
        </div>
      </template>
    </Modal>

    <Modal :show="showMergeModal" title="Gabung Lokasi" @close="closeMergeModal">
      <form @submit.prevent="mergeLokasi" class="space-y-4">
        <div>
          <label class="label">Lokasi Sumber</label>
          <input :value="merging?.nama || ''" class="input bg-gray-50" type="text" disabled />
        </div>

        <div>
          <label class="label">Gabungkan ke Lokasi</label>
          <select v-model="mergeForm.target_id" class="input" :class="{ 'input-error': mergeForm.errors.target_id }">
            <option value="">Pilih lokasi tujuan</option>
            <option
              v-for="target in mergeTargets"
              :key="target.id"
              :value="String(target.id)"
            >
              {{ target.nama }}
            </option>
          </select>
          <p v-if="mergeForm.errors.target_id" class="err">{{ mergeForm.errors.target_id }}</p>
        </div>

        <p class="text-xs text-gray-500">
          Semua rincian kegiatan pada lokasi sumber akan dipindahkan ke lokasi tujuan, lalu lokasi sumber dihapus.
        </p>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeMergeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="mergeForm.processing || !mergeForm.target_id" class="btn-primary">
            {{ mergeForm.processing ? 'Memproses...' : 'Gabungkan' }}
          </button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  lokasis: Array,
  filters: { type: Object, default: () => ({ q: '' }) },
})

const showModal = ref(false)
const showDeleteModal = ref(false)
const showMergeModal = ref(false)
const editing = ref(null)
const deleting = ref(null)
const merging = ref(null)

const search = useForm({
  q: props.filters?.q || '',
})

const form = useForm({ nama: '' })
const mergeForm = useForm({ target_id: '' })

const mergeTargets = computed(() =>
  (props.lokasis || []).filter((item) => Number(item.id) !== Number(merging.value?.id))
)

function applySearch() {
  search.get(route('lokasi.index'), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function resetSearch() {
  search.q = ''
  applySearch()
}

function openCreate() {
  editing.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

function openEdit(lokasi) {
  editing.value = lokasi
  form.nama = lokasi.nama
  form.clearErrors()
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.clearErrors()
}

function save() {
  const options = { onSuccess: closeModal }

  if (editing.value) {
    form.put(route('lokasi.update', editing.value.id), options)
    return
  }

  form.post(route('lokasi.store'), options)
}

function confirmDelete(lokasi) {
  deleting.value = lokasi
  showDeleteModal.value = true
}

function deleteLokasi() {
  if (!deleting.value || (deleting.value.kegiatan_lokasis_count || 0) > 0) {
    return
  }

  router.delete(route('lokasi.destroy', deleting.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      deleting.value = null
    },
  })
}

function openMerge(lokasi) {
  merging.value = lokasi
  mergeForm.reset()
  mergeForm.clearErrors()
  showMergeModal.value = true
}

function closeMergeModal() {
  showMergeModal.value = false
  mergeForm.clearErrors()
}

function mergeLokasi() {
  if (!merging.value) {
    return
  }

  mergeForm.post(route('lokasi.merge', merging.value.id), {
    onSuccess: () => {
      closeMergeModal()
      merging.value = null
    },
  })
}

function formatDate(value) {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}
</script>