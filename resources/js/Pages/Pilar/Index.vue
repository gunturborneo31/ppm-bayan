<template>
  <AppLayout title="Manajemen Pilar">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Pilar</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Pilar</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Deskripsi</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in pilars" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ p.nama }}</td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ p.deskripsi }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(p)" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                <button @click="confirmDelete(p)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="pilars.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada pilar.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Pilar' : 'Tambah Pilar'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Nama Pilar</label>
          <input v-model="form.nama" class="input" :class="{'input-error': form.errors.nama}" type="text" />
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <div>
          <label class="label">Deskripsi</label>
          <textarea v-model="form.deskripsi" class="input h-20 resize-none" />
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
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
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ pilars: Array })
const showModal = ref(false), showDeleteModal = ref(false), editing = ref(null), deleting = ref(null)
const form = useForm({ nama: '', deskripsi: '' })

function openCreate() { editing.value = null; form.reset(); showModal.value = true }
function openEdit(p) { editing.value = p; form.nama = p.nama; form.deskripsi = p.deskripsi || ''; showModal.value = true }
function closeModal() { showModal.value = false; form.clearErrors() }
function save() {
  editing.value
    ? form.put(route('pilar.update', editing.value.id), { onSuccess: closeModal })
    : form.post(route('pilar.store'), { onSuccess: closeModal })
}
function confirmDelete(p) { deleting.value = p; showDeleteModal.value = true }
function deletePilar() { router.delete(route('pilar.destroy', deleting.value.id), { onSuccess: () => { showDeleteModal.value = false } }) }
</script>
