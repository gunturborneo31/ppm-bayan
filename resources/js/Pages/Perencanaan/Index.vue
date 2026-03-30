<template>
  <AppLayout title="Perencanaan Kegiatan">
    <template #header-actions>
      <button
        @click="navigateToAdd"
        class="px-3 py-2 rounded-xl border border-gray-300 bg-white font-bold text-gray-800 hover:bg-orange-500 hover:text-white cursor-pointer"
      >
        + Tambah Kegiatan
      </button>
    </template>

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
        <table class="w-full min-w-[1450px] text-sm">
          <thead>
            <tr class="bg-orange-500 text-white text-xs font-medium">
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Kode Ref</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Nama Program / Kegiatan</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-right bg-orange-500">Rencana Biaya</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Deskripsi</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Target Output</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Satuan</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Pilar Terkait</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Divisi</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Status</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500">Aksi Cepat</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <template v-for="prog in programs" :key="prog.id">
              <tr class="sticky top-[37px] z-10 bg-white border-b border-gray-200 shadow-sm">
                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ rekCode('PRG', prog.id) }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-800">{{ prog.nama }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ prog.kegiatans?.length || 0 }} kegiatan</p>
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
                <td class="px-4 py-3 text-center text-gray-400">-</td>
                <td class="px-4 py-3 text-center text-gray-400">-</td>
                <td class="px-4 py-3 text-center text-gray-400">-</td>
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
                    <span
                      v-for="p in k.pilars"
                      :key="p.id"
                      class="px-1.5 py-0.5 rounded-lg bg-orange-100 text-orange-700 text-[10px] font-medium"
                    >
                      {{ p.nama }}
                    </span>
                  </div>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.divisi?.nama || '-' }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs font-medium px-2 py-1 rounded-lg" :class="statusBadgeClass(k.status)">
                    {{ statusLabel(k.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex flex-wrap gap-1 justify-center">
                    <button
                      v-if="isSuperadmin && k.status === 'diajukan'"
                      @click="approveKegiatan(k)"
                      class="text-[11px] px-2 py-1 rounded border border-green-300 bg-green-50 text-green-700 hover:bg-green-100 transition-colors"
                    >
                      Setujui
                    </button>
                    <button
                      v-if="isSuperadmin && k.status === 'diajukan'"
                      @click="openReject(k)"
                      class="text-[11px] px-2 py-1 rounded border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 transition-colors"
                    >
                      Tolak
                    </button>
                    <button
                      @click="viewDetail(k)"
                      class="text-[11px] px-2 py-1 rounded border border-blue-300 bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                    >
                      Detail
                    </button>
                    <button
                      v-if="canEdit(k)"
                      @click="editKegiatan(k)"
                      class="text-[11px] px-2 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                      Edit
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!prog.kegiatans || prog.kegiatans.length === 0" class="bg-gray-50 border-b border-gray-100">
                <td colspan="10" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada kegiatan di program ini.</td>
              </tr>
            </template>

            <tr v-if="programs.length === 0" class="bg-white">
              <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data program/kegiatan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal :show="showRejectModal" title="Tolak Kegiatan" @close="showRejectModal = false">
      <form @submit.prevent="submitReject" class="space-y-4">
        <div class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-orange-700">
          Alasan penolakan wajib diisi.
        </div>

        <div>
          <label class="label">Alasan Penolakan</label>
          <textarea
            v-model="rejectForm.catatan_revisi"
            class="input h-24 resize-none"
            placeholder="Tuliskan alasan penolakan kegiatan"
            required
          />
          <p v-if="rejectForm.errors.catatan_revisi" class="err">{{ rejectForm.errors.catatan_revisi }}</p>
        </div>

        <div class="flex gap-3 justify-end">
          <button type="button" @click="showRejectModal = false" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button type="submit" :disabled="rejectForm.processing" class="px-3 py-2 rounded border border-red-300 bg-red-50 font-bold text-red-700 hover:bg-red-100 disabled:opacity-60 cursor-pointer">
            {{ rejectForm.processing ? 'Memproses...' : 'Tolak Kegiatan' }}
          </button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

defineProps({
  programs: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const isSuperadmin = computed(() => Boolean(page.props.auth?.user?.is_superadmin))
const showRejectModal = ref(false)
const rejecting = ref(null)

const rejectForm = useForm({
  tipe: 'ditolak',
  catatan_revisi: '',
})

function amount(v) {
  return Number(v || 0).toLocaleString('id-ID')
}

function rekCode(prefix, id) {
  return `${prefix}.${String(id).padStart(3, '0')}`
}

function kegiatanRowStyle(index) {
  return {
    background: index % 2 === 0 ? '#e8f4fb' : '#f1f8fd',
  }
}

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

function canEdit(k) {
  return !['disetujui', 'diajukan'].includes(k.status)
}

function viewDetail(k) {
  router.visit(route('kegiatan.show', k.id))
}

function editKegiatan(k) {
  router.visit(route('kegiatan.index'), {
    preserveState: true,
  })
}

function approveKegiatan(k) {
  router.post(route('kegiatan.approve', k.id))
}

function openReject(k) {
  rejecting.value = k
  rejectForm.reset()
  rejectForm.tipe = 'ditolak'
  rejectForm.clearErrors()
  showRejectModal.value = true
}

function submitReject() {
  if (!rejecting.value) return

  rejectForm.post(route('kegiatan.reject', rejecting.value.id), {
    onSuccess: () => {
      showRejectModal.value = false
      rejecting.value = null
      rejectForm.reset()
      rejectForm.tipe = 'ditolak'
    },
  })
}

function navigateToAdd() {
  router.visit(route('kegiatan.index'))
}
</script>
