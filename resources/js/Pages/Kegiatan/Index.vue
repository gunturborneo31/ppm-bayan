<template>
  <AppLayout title="Kegiatan">
    <template #header-actions>
      <button @click="openCreate" class="px-3 text-base py-2 rounded rounded-xl  border border-gray-300 bg-white font-bold text-gray-800 hover:bg-orange-500 hover:text-white cursor-pointer">+ Tambah Kegiatan</button>
    </template>

    <!-- Color Legend -->
    <div class="mb-4 flex gap-3 px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
      <div>
          <span class="text-xs text-gray-600">Keterangan Warna : </span>
      </div>
      <div class="flex gap-6">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded bg-white border border-gray-300"></div>
          <span class="text-xs text-gray-600">Program</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded" style="background:#4B9FD9"></div>
          <span class="text-xs text-gray-600">Kegiatan</span>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="max-h-[75vh] overflow-auto">
        <table class="w-full min-w-[1600px] text-sm">
          <thead>
            <tr class="bg-orange-500 text-white text-xs font-medium">
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Kode Ref</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Nama Program / Kegiatan</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-right bg-orange-500">Rencana Biaya</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Deskripsi</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Target Output</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Satuan</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Pilar Terkait</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Divisi / Unit</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">User Penginput</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Status / Anggaran</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <template v-for="prog in programs" :key="prog.id">
              <tr class="sticky top-[37px] z-10 bg-white border-b border-gray-200 shadow-sm">
                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ rekCode('PRG', prog.id) }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-800">{{ prog.nama }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">Progress: {{ prog.avg_progress || 0 }}%</p>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center justify-between gap-2 min-w-[130px] text-gray-800 font-medium">
                    <span>Rp</span>
                    <span class="text-right w-full">{{ amount(prog.rencana_biaya) }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ prog.deskripsi || '-' }}</td>
                <td class="px-4 py-3 text-center text-gray-600">-</td>
                <td class="px-4 py-3 text-center text-gray-600">-</td>
                <td class="px-4 py-3 text-center">-</td>
                <td class="px-4 py-3 text-center text-gray-400">-</td>
                <td class="px-4 py-3 text-center text-gray-400">-</td>
                <td class="px-4 py-3 text-sm">
                  <div class="text-amber-700 mb-1 whitespace-nowrap flex items-center justify-between gap-2 min-w-[170px]">
                    <span>Terpakai User Lain: Rp</span>
                    <span class="text-right w-full">{{ amount(prog.total_kegiatan_biaya_user_lain) }}</span>
                  </div>
                  <div class="text-gray-600 mb-1 whitespace-nowrap flex items-center justify-between gap-2 min-w-[150px]">
                    <span>Terpakai: Rp</span>
                    <span class="text-right w-full">{{ amount(prog.total_kegiatan_biaya_user) }}</span>
                  </div>
                  <div :class="prog.sisa_anggaran < 0 ? 'text-red-600 font-medium' : 'text-green-600 font-medium'" class="whitespace-nowrap flex items-center justify-between gap-2 min-w-[150px]">
                    <span>Sisa: Rp</span>
                    <span class="text-right w-full">{{ amount(prog.sisa_anggaran) }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-center">-</td>
              </tr>

              <tr
                v-for="(k, kIndex) in (prog.kegiatans || [])"
                :key="k.id"
                class="border-b border-gray-100"
                :style="kegiatanRowStyle(kIndex)"
              >
                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ rekCode('KGT', k.id) }}</td>
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-800">{{ k.nama }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-gray-800 font-medium whitespace-nowrap">
                  <div class="flex items-center justify-between gap-2 min-w-[130px]">
                    <span>Rp</span>
                    <span class="text-right w-full">{{ amount(k.rencana_biaya) }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.deskripsi || '-' }}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-800">{{ k.target_output ?? '-' }}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-800">{{ k.satuan || '-' }}</td>
                <td class="px-4 py-3 text-center text-xs">
                  <div v-if="k.pilars && k.pilars.length > 0" class="flex flex-wrap gap-1 justify-center">
                    <span v-for="p in k.pilars" :key="p.id" class="px-1.5 py-0.5 rounded-lg bg-orange-100 text-orange-700 text-[10px] font-medium">{{ p.nama }}</span>
                  </div>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.divisi?.nama || '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.creator_name || '-' }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs font-medium px-2 py-1 rounded-lg" :class="statusBadgeClass(k.status)">{{ k.status_label || statusLabel(k.status) }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex flex-wrap gap-1 justify-center">
                    <Link :href="route('kegiatan.show', k.id)" class="text-[11px] px-2 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition-colors">Detail</Link>
                    <button v-if="canEdit(k)" @click="openEdit(k)" class="text-[11px] px-2 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition-colors">Ubah</button>
                    <button v-if="isSuperadmin && k.status === 'diajukan'" @click="approveKegiatan(k)" class="text-[11px] px-2 py-1 rounded border border-green-300 bg-green-50 text-green-600 hover:bg-green-100 transition-colors">Setujui</button>
                    <button v-if="isSuperadmin && k.status === 'diajukan'" @click="openReject(k)" class="text-[11px] px-2 py-1 rounded border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Tolak</button>
                    <button v-if="isSuperadmin && canDelete(k)" @click="confirmDelete(k)" class="text-[11px] px-2 py-1 rounded border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Hapus</button>
                  </div>
                </td>
              </tr>

              <tr v-if="!prog.kegiatans || prog.kegiatans.length === 0" class="bg-gray-50 border-b border-gray-100">
                <td colspan="11" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada kegiatan di program ini.</td>
              </tr>
            </template>

            <tr v-if="programs.length === 0" class="bg-white">
              <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data program/kegiatan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit Kegiatan' : 'Tambah Kegiatan'" @close="closeModal" max-width="2xl">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Program</label>
            <select v-model="form.program_id" class="input" :class="{'input-error': form.errors.program_id}">
              <option value="">-- Pilih Program --</option>
              <option v-for="p in allPrograms" :key="p.id" :value="p.id">{{ p.nama }}</option>
            </select>
            <p v-if="form.errors.program_id" class="err">{{ form.errors.program_id }}</p>
          </div>
          <div>
            <label class="label">Divisi</label>
            <select v-if="isSuperadmin" v-model="form.divisi_id" class="input" :class="{'input-error': form.errors.divisi_id}">
              <option value="">-- Pilih Divisi --</option>
              <option v-for="d in divisis" :key="d.id" :value="d.id">{{ d.nama }}</option>
            </select>
            <input v-else :value="currentUserDivisiName" class="input bg-gray-50" type="text" disabled />
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
            <input :value="form.target_output" @input="onNumberInput('target_output', $event.target.value)" class="input text-right" :class="{'input-error': form.errors.target_output}" type="text" inputmode="numeric" />
            <p v-if="form.errors.target_output" class="err">{{ form.errors.target_output }}</p>
          </div>
          <div>
            <label class="label">Satuan</label>
            <input v-model="form.satuan" class="input" :class="{'input-error': form.errors.satuan}" type="text" placeholder="Contoh: Orang, Paket, Unit, dll" />
            <p v-if="form.errors.satuan" class="err">{{ form.errors.satuan }}</p>
          </div>
        </div>
        <div>
          <label class="label">Rencana Biaya (Rp)</label>
          <input :value="form.rencana_biaya" @input="onNumberInput('rencana_biaya', $event.target.value)" class="input text-right" :class="{'input-error': form.errors.rencana_biaya}" type="text" inputmode="numeric" />
          <p v-if="form.errors.rencana_biaya" class="err">{{ form.errors.rencana_biaya }}</p>
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
          <button type="button" @click="closeModal" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button type="submit" :disabled="form.processing" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 disabled:opacity-60 cursor-pointer">
            {{ form.processing ? 'Menyimpan...' : 'Simpan & Ajukan' }}
          </button>
        </div>
      </form>
    </Modal>

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
          <button type="button" @click="showRejectModal = false" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button type="submit" :disabled="rejectForm.processing" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 disabled:opacity-60 cursor-pointer">Konfirmasi</button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Kegiatan" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus kegiatan <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button @click="deleteKegiatan" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Hapus</button>
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
  programs: Array,
  allPrograms: Array,
  divisis: Array,
  pilars: Array,
})

const page = usePage()
const isSuperadmin = computed(() => page.props.auth.user?.is_superadmin || page.props.auth.user?.is_cdo)
const currentUserDivisiId = computed(() => page.props.auth.user?.divisi_id || null)
const currentUserDivisiName = computed(() => {
  const found = props.divisis.find(d => d.id === currentUserDivisiId.value)
  return found ? found.nama : '-'
})

const showModal = ref(false)
const showRejectModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const rejecting = ref(null)
const deleting = ref(null)

const form = useForm({
  program_id: '', divisi_id: '', nama: '', deskripsi: '',
  target_output: '', satuan: '', rencana_biaya: '', pilar_ids: [],
})
const rejectForm = useForm({ tipe: 'revisi', catatan_revisi: '' })

function amount(v) { return Number(v || 0).toLocaleString('id-ID') }
function normalize(v) {
  if (v === null || v === undefined || v === '') return ''
  return String(v).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.]/g, '')
}
function onNumberInput(field, value) {
  const clean = String(value || '').replace(/\D/g, '')
  form[field] = clean ? Number(clean).toLocaleString('id-ID') : ''
}
function canEdit(k) { return !['disetujui', 'diajukan'].includes(k.status) }
function canDelete(k) { return !['disetujui', 'diajukan'].includes(k.status) }

function statusBadgeClass(s) {
  return {
    draft: 'bg-gray-100 text-gray-700',
    diajukan: 'bg-orange-100 text-orange-700',
    revisi: 'bg-yellow-100 text-yellow-700',
    disetujui: 'bg-green-100 text-green-700',
    ditolak: 'bg-red-100 text-red-700',
    selesai: 'bg-blue-100 text-blue-700',
  }[s] || 'bg-gray-100 text-gray-700'
}

function statusLabel(s) {
  return String(s || '-').charAt(0).toUpperCase() + String(s || '-').slice(1)
}

function rekCode(prefix, id) {
  return `${prefix}.${String(id).padStart(3, '0')}`
}

function kegiatanRowStyle(index) {
  return {
    background: index % 2 === 0 ? '#e8f4fb' : '#f1f8fd',
  }
}

function openCreate() {
  editing.value = null
  form.reset()
  if (!isSuperadmin.value) {
    form.divisi_id = currentUserDivisiId.value || ''
  }
  showModal.value = true
}
function openEdit(k) {
  editing.value = k
  form.program_id = k.program_id
  form.divisi_id = k.divisi_id
  form.nama = k.nama
  form.deskripsi = k.deskripsi || ''
  form.target_output = amount(k.target_output)
  form.satuan = k.satuan || ''
  form.rencana_biaya = amount(k.rencana_biaya)
  form.pilar_ids = k.pilars?.map(p => p.id) || []
  showModal.value = true
}
function closeModal() {
  showModal.value = false
  form.clearErrors()
}

function save() {
  if (!isSuperadmin.value) {
    form.divisi_id = currentUserDivisiId.value || ''
  }

  form.transform((data) => ({
    ...data,
    target_output: normalize(data.target_output),
    rencana_biaya: normalize(data.rencana_biaya),
  }))

  if (editing.value) {
    form.put(route('kegiatan.update', editing.value.id), { onSuccess: closeModal })
  } else {
    form.post(route('kegiatan.store'), { onSuccess: closeModal })
  }
}

function approveKegiatan(k) {
  router.post(route('kegiatan.approve', k.id))
}
function openReject(k) {
  rejecting.value = k
  rejectForm.reset()
  showRejectModal.value = true
}
function submitReject() {
  rejectForm.post(route('kegiatan.reject', rejecting.value.id), {
    onSuccess: () => { showRejectModal.value = false }
  })
}
function confirmDelete(k) {
  deleting.value = k
  showDeleteModal.value = true
}
function deleteKegiatan() {
  router.delete(route('kegiatan.destroy', deleting.value.id), {
    onSuccess: () => { showDeleteModal.value = false }
  })
}

const allPrograms = computed(() => props.allPrograms || [])
</script>
