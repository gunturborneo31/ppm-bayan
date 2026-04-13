<template>
  <AppLayout title="Dasar Hukum Pelaksanaan">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Dasar Hukum</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Judul</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nomor Regulasi</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Dokumen</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="item in regulasis" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ item.judul }}</td>
            <td class="px-4 py-3 text-gray-700 text-xs">{{ item.nomor_regulasi }}</td>
            <td class="px-4 py-3 text-xs">
              <a v-if="item.link_dokumen" :href="item.link_dokumen" target="_blank" class="text-blue-600 hover:text-blue-700 hover:underline">Buka Dokumen</a>
              <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(item)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(item)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="regulasis.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada dasar hukum pelaksanaan.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Dasar Hukum' : 'Tambah Dasar Hukum'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Judul</label>
          <input v-model="form.judul" class="input" :class="{'input-error': form.errors.judul}" type="text" />
          <p v-if="form.errors.judul" class="err">{{ form.errors.judul }}</p>
        </div>
        <div>
          <label class="label">Nomor Regulasi</label>
          <input v-model="form.nomor_regulasi" class="input" :class="{'input-error': form.errors.nomor_regulasi}" type="text" />
          <p v-if="form.errors.nomor_regulasi" class="err">{{ form.errors.nomor_regulasi }}</p>
        </div>
        <div>
          <label class="label">Link Dokumen (opsional)</label>
          <input v-model="form.link_dokumen" class="input" :class="{'input-error': form.errors.link_dokumen}" type="text" placeholder="https://..." />
          <p v-if="form.errors.link_dokumen" class="err">{{ form.errors.link_dokumen }}</p>
        </div>
        <div>
          <label class="label">Upload Dokumen PDF (opsional)</label>
          <input class="input file:mr-4 file:rounded-lg file:border-0 file:bg-orange-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-700" type="file" accept="application/pdf" @input="onFileChange" />
          <p class="mt-1 text-xs text-gray-500">Jika file dipilih, hasil upload akan menggantikan link dokumen yang ada.</p>
          <p v-if="editing?.link_dokumen" class="mt-1 text-xs text-gray-500">
            Dokumen saat ini:
            <a :href="editing.link_dokumen" target="_blank" class="text-blue-600 hover:text-blue-700 hover:underline">Buka dokumen</a>
          </p>
          <p v-if="form.errors.dokumen_file" class="err">{{ form.errors.dokumen_file }}</p>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Dasar Hukum" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus dasar hukum <strong>{{ deleting?.judul }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteRegulasi" class="btn-danger">Hapus</button>
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

defineProps({ regulasis: Array })

const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleting = ref(null)

const form = useForm({
  judul: '',
  nomor_regulasi: '',
  link_dokumen: '',
  dokumen_file: null,
  _method: '',
})

function openCreate() {
  editing.value = null
  form.reset()
  form._method = ''
  showModal.value = true
}

function openEdit(item) {
  editing.value = item
  form.judul = item.judul
  form.nomor_regulasi = item.nomor_regulasi
  form.link_dokumen = item.link_dokumen || ''
  form.dokumen_file = null
  form._method = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.clearErrors()
  form.dokumen_file = null
}

function onFileChange(event) {
  form.dokumen_file = event.target.files[0] || null
}

function save() {
  if (editing.value) {
    form._method = 'put'
    form.post(route('regulasi.update', editing.value.id), {
      forceFormData: true,
      onSuccess: closeModal,
      onFinish: () => {
        form._method = ''
      },
    })
    return
  }
  form._method = ''
  form.post(route('regulasi.store'), {
    forceFormData: true,
    onSuccess: closeModal,
  })
}

function confirmDelete(item) {
  deleting.value = item
  showDeleteModal.value = true
}

function deleteRegulasi() {
  router.delete(route('regulasi.destroy', deleting.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
    },
  })
}
</script>
