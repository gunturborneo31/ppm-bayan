<template>
  <AppLayout title="Manajemen Divisi">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Divisi</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Divisi</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Deskripsi</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Users</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="d in divisis" :key="d.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ d.nama }}</td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ d.deskripsi }}</td>
            <td class="px-4 py-3 text-center">{{ d.users_count }}</td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="d.status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                {{ d.status ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(d)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(d)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="divisis.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada divisi.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Divisi' : 'Tambah Divisi'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Nama Divisi</label>
          <input v-model="form.nama" class="input" :class="{'input-error': form.errors.nama}" type="text" />
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <div>
          <label class="label">Deskripsi</label>
          <textarea v-model="form.deskripsi" class="input h-20 resize-none" />
        </div>
        <div class="flex items-center gap-2">
          <input v-model="form.status" type="checkbox" id="status" class="rounded" />
          <label for="status" class="text-sm text-gray-700">Aktif</label>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Divisi" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus divisi <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteDivisi" class="btn-danger">Hapus</button>
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

const props = defineProps({ divisis: Array })
const showModal = ref(false), showDeleteModal = ref(false), editing = ref(null), deleting = ref(null)
const form = useForm({ nama: '', deskripsi: '', status: true })

function openCreate() { editing.value = null; form.reset(); form.status = true; showModal.value = true }
function openEdit(d) { editing.value = d; form.nama = d.nama; form.deskripsi = d.deskripsi || ''; form.status = d.status; showModal.value = true }
function closeModal() { showModal.value = false; form.clearErrors() }
function save() {
  editing.value
    ? form.put(route('divisi.update', editing.value.id), { onSuccess: closeModal })
    : form.post(route('divisi.store'), { onSuccess: closeModal })
}
function confirmDelete(d) { deleting.value = d; showDeleteModal.value = true }
function deleteDivisi() { router.delete(route('divisi.destroy', deleting.value.id), { onSuccess: () => { showDeleteModal.value = false } }) }
</script>
