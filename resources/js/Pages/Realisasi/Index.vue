<template>
  <AppLayout title="Input Realisasi">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Realisasi</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Kegiatan</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Periode</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Realisasi Output</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Realisasi Biaya</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Progress</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="r in realisasis" :key="r.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-800">{{ r.kegiatan?.nama }}</p>
              <p class="text-xs text-gray-500">{{ r.kegiatan?.divisi?.nama }}</p>
            </td>
            <td class="px-4 py-3">{{ r.periode?.tahun }} {{ r.periode?.triwulan }}</td>
            <td class="px-4 py-3 text-right">{{ r.realisasi_output }}</td>
            <td class="px-4 py-3 text-right">{{ fmt(r.realisasi_biaya) }}</td>
            <td class="px-4 py-3 min-w-[120px]">
              <div class="flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full bg-blue-500 rounded-full" :style="{ width: r.progress + '%' }" />
                </div>
                <span class="text-xs text-gray-600 w-10 text-right">{{ r.progress }}%</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(r)" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                <button @click="confirmDelete(r)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="realisasis.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada realisasi.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="editing ? 'Edit Realisasi' : 'Tambah Realisasi'" @close="closeModal" max-width="xl">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Kegiatan</label>
          <select v-model="form.kegiatan_id" class="input" :disabled="!!editing" :class="{'input-error': form.errors.kegiatan_id}">
            <option value="">-- Pilih Kegiatan (Disetujui) --</option>
            <option v-for="k in kegiatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
          <p v-if="form.errors.kegiatan_id" class="err">{{ form.errors.kegiatan_id }}</p>
        </div>
        <div v-if="!editing">
          <label class="label">Periode</label>
          <select v-model="form.periode_id" class="input" :class="{'input-error': form.errors.periode_id}">
            <option value="">-- Pilih Periode --</option>
            <option v-for="p in periodes" :key="p.id" :value="p.id">{{ p.tahun }} {{ p.triwulan }}</option>
          </select>
          <p v-if="form.errors.periode_id" class="err">{{ form.errors.periode_id }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Realisasi Output</label>
            <input v-model="form.realisasi_output" class="input" :class="{'input-error': form.errors.realisasi_output}" type="number" step="0.01" />
            <p v-if="form.errors.realisasi_output" class="err">{{ form.errors.realisasi_output }}</p>
          </div>
          <div>
            <label class="label">Realisasi Biaya (Rp)</label>
            <input v-model="form.realisasi_biaya" class="input" :class="{'input-error': form.errors.realisasi_biaya}" type="number" step="0.01" />
            <p v-if="form.errors.realisasi_biaya" class="err">{{ form.errors.realisasi_biaya }}</p>
          </div>
        </div>
        <div>
          <label class="label">Keterangan</label>
          <textarea v-model="form.keterangan" class="input h-20 resize-none" rows="3" />
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirm -->
    <Modal :show="showDeleteModal" title="Hapus Realisasi" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus data realisasi ini?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteRealisasi" class="btn-danger">Hapus</button>
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

const props = defineProps({
  realisasis: Array,
  kegiatans:  Array,
  periodes:   Array,
})

const showModal       = ref(false)
const showDeleteModal = ref(false)
const editing         = ref(null)
const deleting        = ref(null)

const form = useForm({
  kegiatan_id: '', periode_id: '', realisasi_output: '', realisasi_biaya: '', keterangan: '',
})

function fmt(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') }

function openCreate() { editing.value = null; form.reset(); showModal.value = true }
function openEdit(r) {
  editing.value         = r
  form.kegiatan_id      = r.kegiatan_id
  form.periode_id       = r.periode_id
  form.realisasi_output = r.realisasi_output
  form.realisasi_biaya  = r.realisasi_biaya
  form.keterangan       = r.keterangan || ''
  showModal.value       = true
}
function closeModal() { showModal.value = false; form.clearErrors() }

function save() {
  if (editing.value) {
    form.put(route('realisasi.update', editing.value.id), { onSuccess: closeModal })
  } else {
    form.post(route('realisasi.store'), { onSuccess: closeModal })
  }
}

function confirmDelete(r) { deleting.value = r; showDeleteModal.value = true }
function deleteRealisasi() {
  router.delete(route('realisasi.destroy', deleting.value.id), {
    onSuccess: () => { showDeleteModal.value = false }
  })
}
</script>
