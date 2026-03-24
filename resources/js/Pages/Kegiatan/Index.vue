<template>
  <AppLayout title="Kegiatan">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah Kegiatan</button>
    </template>

    <!-- Filter / Search -->
    <div class="mb-4 flex gap-3">
      <input v-model="search" type="text" placeholder="Cari kegiatan..." class="input max-w-xs" />
      <select v-model="filterStatus" class="input max-w-[160px]">
        <option value="">Semua Status</option>
        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
      </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Kegiatan</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Program</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Divisi</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Progress</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="k in filtered" :key="k.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-800">{{ k.nama }}</p>
              <p class="text-xs text-gray-500">v{{ k.version }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">{{ k.program?.nama }}</td>
            <td class="px-4 py-3 text-gray-600 text-xs">{{ k.divisi?.nama }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(k.status)">
                {{ k.status_label }}
              </span>
            </td>
            <td class="px-4 py-3 min-w-[100px]">
              <div class="flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full bg-blue-500 rounded-full transition-all" :style="{ width: k.progress + '%' }" />
                </div>
                <span class="text-xs text-gray-600 w-10 text-right">{{ k.progress }}%</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-1 justify-end flex-wrap">
                <Link :href="route('kegiatan.show', k.id)" class="text-blue-600 hover:text-blue-800 text-xs px-1">Detail</Link>
                <button v-if="canEdit(k)" @click="openEdit(k)" class="text-gray-600 hover:text-gray-800 text-xs px-1">Edit</button>
                <button v-if="canSubmit(k)" @click="submitKegiatan(k)" class="text-blue-600 hover:text-blue-800 text-xs px-1">Ajukan</button>
                <template v-if="isSuperadmin && k.status === 'diajukan'">
                  <button @click="approveKegiatan(k)" class="text-green-600 hover:text-green-800 text-xs px-1">Setujui</button>
                  <button @click="openReject(k)" class="text-red-600 hover:text-red-800 text-xs px-1">Tolak</button>
                </template>
                <button v-if="canDelete(k)" @click="confirmDelete(k)" class="text-red-600 hover:text-red-800 text-xs px-1">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="filtered.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada kegiatan.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="editing ? 'Edit Kegiatan' : 'Tambah Kegiatan'" @close="closeModal" max-width="2xl">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Program</label>
            <select v-model="form.program_id" class="input" :class="{'input-error': form.errors.program_id}">
              <option value="">-- Pilih Program --</option>
              <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.nama }}</option>
            </select>
            <p v-if="form.errors.program_id" class="err">{{ form.errors.program_id }}</p>
          </div>
          <div>
            <label class="label">Divisi</label>
            <select v-model="form.divisi_id" class="input" :class="{'input-error': form.errors.divisi_id}">
              <option value="">-- Pilih Divisi --</option>
              <option v-for="d in divisis" :key="d.id" :value="d.id">{{ d.nama }}</option>
            </select>
            <p v-if="form.errors.divisi_id" class="err">{{ form.errors.divisi_id }}</p>
          </div>
        </div>
        <div>
          <label class="label">Nama Kegiatan</label>
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
            <label class="label">Rencana Biaya (Rp)</label>
            <input v-model="form.rencana_biaya" class="input" :class="{'input-error': form.errors.rencana_biaya}" type="number" step="0.01" />
            <p v-if="form.errors.rencana_biaya" class="err">{{ form.errors.rencana_biaya }}</p>
          </div>
        </div>
        <div>
          <label class="label">Pilar</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="p in pilars" :key="p.id" class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" :value="p.id" v-model="form.pilar_ids" class="rounded" />
              <span class="text-sm">{{ p.nama }}</span>
            </label>
          </div>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Reject Modal -->
    <Modal :show="showRejectModal" title="Tolak / Kembalikan Revisi" @close="showRejectModal = false">
      <form @submit.prevent="submitReject" class="space-y-4">
        <div>
          <label class="label">Tipe</label>
          <select v-model="rejectForm.tipe" class="input">
            <option value="revisi">Kembalikan untuk Revisi</option>
            <option value="ditolak">Tolak</option>
          </select>
        </div>
        <div>
          <label class="label">Catatan</label>
          <textarea v-model="rejectForm.catatan_revisi" class="input h-24 resize-none" required />
          <p v-if="rejectForm.errors.catatan_revisi" class="err">{{ rejectForm.errors.catatan_revisi }}</p>
        </div>
        <div class="flex gap-3 justify-end">
          <button type="button" @click="showRejectModal = false" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="rejectForm.processing" class="btn-danger">Konfirmasi</button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirm -->
    <Modal :show="showDeleteModal" title="Hapus Kegiatan" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus kegiatan <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteKegiatan" class="btn-danger">Hapus</button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  kegiatans: Array,
  programs:  Array,
  divisis:   Array,
  pilars:    Array,
})

const page        = usePage()
const isSuperadmin = computed(() => page.props.auth.user?.is_superadmin)

const search       = ref('')
const filterStatus = ref('')
const statuses     = ['draft','diajukan','revisi','disetujui','ditolak','selesai']

const filtered = computed(() => {
  return props.kegiatans.filter(k => {
    const matchSearch = !search.value || k.nama.toLowerCase().includes(search.value.toLowerCase())
    const matchStatus = !filterStatus.value || k.status === filterStatus.value
    return matchSearch && matchStatus
  })
})

const showModal       = ref(false)
const showRejectModal = ref(false)
const showDeleteModal = ref(false)
const editing         = ref(null)
const rejecting       = ref(null)
const deleting        = ref(null)

const form = useForm({
  program_id: '', divisi_id: '', nama: '', deskripsi: '',
  target_output: '', rencana_biaya: '', pilar_ids: [],
})
const rejectForm = useForm({ tipe: 'revisi', catatan_revisi: '' })

function canEdit(k)   { return k.status !== 'disetujui' }
function canSubmit(k) { return ['draft','revisi'].includes(k.status) }
function canDelete(k) { return k.status !== 'disetujui' }

function statusBadge(s) {
  return { draft: 'bg-gray-100 text-gray-700', diajukan: 'bg-blue-100 text-blue-700',
           revisi: 'bg-yellow-100 text-yellow-700', disetujui: 'bg-green-100 text-green-700',
           ditolak: 'bg-red-100 text-red-700', selesai: 'bg-purple-100 text-purple-700' }[s] || 'bg-gray-100 text-gray-700'
}

function openCreate() {
  editing.value = null; form.reset(); showModal.value = true
}
function openEdit(k) {
  editing.value = k
  form.program_id    = k.program_id
  form.divisi_id     = k.divisi_id
  form.nama          = k.nama
  form.deskripsi     = k.deskripsi || ''
  form.target_output = k.target_output
  form.rencana_biaya = k.rencana_biaya
  form.pilar_ids     = k.pilars?.map(p => p.id) || []
  showModal.value    = true
}
function closeModal() { showModal.value = false; form.clearErrors() }

function save() {
  if (editing.value) {
    form.put(route('kegiatan.update', editing.value.id), { onSuccess: closeModal })
  } else {
    form.post(route('kegiatan.store'), { onSuccess: closeModal })
  }
}

function submitKegiatan(k) {
  router.post(route('kegiatan.submit', k.id))
}
function approveKegiatan(k) {
  router.post(route('kegiatan.approve', k.id))
}
function openReject(k) {
  rejecting.value = k; rejectForm.reset(); showRejectModal.value = true
}
function submitReject() {
  rejectForm.post(route('kegiatan.reject', rejecting.value.id), {
    onSuccess: () => { showRejectModal.value = false }
  })
}
function confirmDelete(k) { deleting.value = k; showDeleteModal.value = true }
function deleteKegiatan() {
  router.delete(route('kegiatan.destroy', deleting.value.id), {
    onSuccess: () => { showDeleteModal.value = false }
  })
}
</script>
