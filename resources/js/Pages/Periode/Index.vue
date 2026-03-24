<template>
  <AppLayout title="Manajemen Periode">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Periode</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Tahun</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Triwulan</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in periodes" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ p.tahun }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">{{ p.triwulan }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="p.status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                {{ p.status ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(p)" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                <button @click="confirmDelete(p)" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="periodes.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada periode.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Periode' : 'Tambah Periode'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Tahun</label>
            <input v-model="form.tahun" class="input" :class="{'input-error': form.errors.tahun}" type="number" min="2020" max="2099" />
            <p v-if="form.errors.tahun" class="err">{{ form.errors.tahun }}</p>
          </div>
          <div>
            <label class="label">Triwulan</label>
            <select v-model="form.triwulan" class="input" :class="{'input-error': form.errors.triwulan}">
              <option value="">-- Pilih --</option>
              <option value="Q1">Q1 (Jan–Mar)</option>
              <option value="Q2">Q2 (Apr–Jun)</option>
              <option value="Q3">Q3 (Jul–Sep)</option>
              <option value="Q4">Q4 (Okt–Des)</option>
            </select>
            <p v-if="form.errors.triwulan" class="err">{{ form.errors.triwulan }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="form.status" type="checkbox" id="status" class="rounded" />
          <label for="status" class="text-sm text-gray-700">Aktif</label>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">Simpan</button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Periode" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus periode ini?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deletePeriode" class="btn-danger">Hapus</button>
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

const props = defineProps({ periodes: Array })
const showModal = ref(false), showDeleteModal = ref(false), editing = ref(null), deleting = ref(null)
const form = useForm({ tahun: new Date().getFullYear(), triwulan: '', status: true })

function openCreate() { editing.value = null; form.reset(); form.tahun = new Date().getFullYear(); form.status = true; showModal.value = true }
function openEdit(p) { editing.value = p; form.tahun = p.tahun; form.triwulan = p.triwulan; form.status = p.status; showModal.value = true }
function closeModal() { showModal.value = false; form.clearErrors() }
function save() {
  editing.value
    ? form.put(route('periode.update', editing.value.id), { onSuccess: closeModal })
    : form.post(route('periode.store'), { onSuccess: closeModal })
}
function confirmDelete(p) { deleting.value = p; showDeleteModal.value = true }
function deletePeriode() { router.delete(route('periode.destroy', deleting.value.id), { onSuccess: () => { showDeleteModal.value = false } }) }
</script>
