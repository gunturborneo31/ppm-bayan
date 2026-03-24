<template>
  <AppLayout title="Program">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Program</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Program</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Satuan</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Rencana Biaya</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Terpakai</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Sisa</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Kegiatan</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in programs" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-800">{{ p.nama }}</p>
              <p class="text-xs text-gray-500">{{ p.deskripsi }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ p.satuan }}</td>
            <td class="px-4 py-3 text-right text-gray-700">{{ fmt(p.rencana_biaya) }}</td>
            <td class="px-4 py-3 text-right text-gray-700">{{ fmt(p.total_kegiatan_biaya) }}</td>
            <td class="px-4 py-3 text-right" :class="p.sisa_anggaran < 0 ? 'text-red-600 font-medium' : 'text-green-600'">
              {{ fmt(p.sisa_anggaran) }}
            </td>
            <td class="px-4 py-3 text-center">
              <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ p.kegiatans_count }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(p)" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                <button @click="confirmDelete(p)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="programs.length === 0">
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada program.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="editing ? 'Edit Program' : 'Tambah Program'" @close="closeModal" max-width="xl">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Nama Program</label>
          <input v-model="form.nama" class="input" :class="{'input-error': form.errors.nama}" type="text" />
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <div>
          <label class="label">Deskripsi</label>
          <textarea v-model="form.deskripsi" class="input h-20 resize-none" rows="3" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Target Output</label>
            <input v-model="form.target_output" class="input" :class="{'input-error': form.errors.target_output}" type="number" step="0.01" />
            <p v-if="form.errors.target_output" class="err">{{ form.errors.target_output }}</p>
          </div>
          <div>
            <label class="label">Satuan</label>
            <input v-model="form.satuan" class="input" :class="{'input-error': form.errors.satuan}" type="text" placeholder="Orang, Kegiatan, dll" />
            <p v-if="form.errors.satuan" class="err">{{ form.errors.satuan }}</p>
          </div>
        </div>
        <div>
          <label class="label">Rencana Biaya (Rp)</label>
          <input v-model="form.rencana_biaya" class="input" :class="{'input-error': form.errors.rencana_biaya}" type="number" step="0.01" />
          <p v-if="form.errors.rencana_biaya" class="err">{{ form.errors.rencana_biaya }}</p>
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
    <Modal :show="showDeleteModal" title="Hapus Program" @close="showDeleteModal = false">
      <p class="text-gray-600">Apakah Anda yakin ingin menghapus program <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteProgram" class="btn-danger">Hapus</button>
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

const props = defineProps({ programs: Array })

const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleting = ref(null)

const form = useForm({ nama: '', deskripsi: '', target_output: '', satuan: '', rencana_biaya: '' })

function fmt(v) {
  return 'Rp ' + Number(v || 0).toLocaleString('id-ID')
}

function openCreate() {
  editing.value = null
  form.reset()
  showModal.value = true
}

function openEdit(p) {
  editing.value = p
  form.nama = p.nama
  form.deskripsi = p.deskripsi || ''
  form.target_output = p.target_output
  form.satuan = p.satuan
  form.rencana_biaya = p.rencana_biaya
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.clearErrors()
}

function save() {
  if (editing.value) {
    form.put(route('program.update', editing.value.id), { onSuccess: closeModal })
  } else {
    form.post(route('program.store'), { onSuccess: closeModal })
  }
}

function confirmDelete(p) {
  deleting.value = p
  showDeleteModal.value = true
}

function deleteProgram() {
  router.delete(route('program.destroy', deleting.value.id), {
    onSuccess: () => { showDeleteModal.value = false }
  })
}
</script>

