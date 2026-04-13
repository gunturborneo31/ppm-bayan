<template>
  <AppLayout title="Manajemen Periode">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Periode</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-auto max-h-[75vh]">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100 sticky top-0 z-10">
          <tr>
            <th class="sticky top-0 z-10 text-left px-4 py-3 font-medium text-gray-600 bg-gray-50">Tahun</th>
            <th class="sticky top-0 z-10 text-left px-4 py-3 font-medium text-gray-600 bg-gray-50">Bulan</th>
            <th class="sticky top-0 z-10 text-center px-4 py-3 font-medium text-gray-600 bg-gray-50">Status</th>
            <th class="sticky top-0 z-10 px-4 py-3 bg-gray-50" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in periodes" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ p.tahun }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(215,86,30,0.1);color:#D7561E">{{ p.bulan_label || p.triwulan }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="p.status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                {{ p.status ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(p)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(p)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
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
            <label class="label">Bulan</label>
            <select v-model="form.bulan" class="input" :class="{'input-error': form.errors.bulan}">
              <option value="">-- Pilih --</option>
              <option v-for="b in bulanOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
            </select>
            <p v-if="form.errors.bulan" class="err">{{ form.errors.bulan }}</p>
          </div>
        </div>
        <div>
          <label class="label">Status</label>
          <select v-model="form.status" class="input" :class="{'input-error': form.errors.status}">
            <option value="">-- Pilih Status --</option>
            <option :value="1">Aktif</option>
            <option :value="0">Nonaktif</option>
          </select>
          <p v-if="form.errors.status" class="err">{{ form.errors.status }}</p>
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

const props = defineProps({ periodes: Array, bulan_options: Array })
const showModal = ref(false), showDeleteModal = ref(false), editing = ref(null), deleting = ref(null)
const form = useForm({ tahun: new Date().getFullYear(), bulan: '', status: '' })
const bulanOptions = props.bulan_options || []

function openCreate() { editing.value = null; form.reset(); form.tahun = new Date().getFullYear(); form.bulan = ''; form.status = ''; showModal.value = true }
function openEdit(p) { editing.value = p; form.tahun = p.tahun; form.bulan = p.bulan; form.status = Number(p.status); showModal.value = true }
function closeModal() { showModal.value = false; form.clearErrors() }
function save() {
  editing.value
    ? form.put(route('periode.update', editing.value.id), { onSuccess: closeModal })
    : form.post(route('periode.store'), { onSuccess: closeModal })
}
function confirmDelete(p) { deleting.value = p; showDeleteModal.value = true }
function deletePeriode() { router.delete(route('periode.destroy', deleting.value.id), { onSuccess: () => { showDeleteModal.value = false } }) }
</script>
