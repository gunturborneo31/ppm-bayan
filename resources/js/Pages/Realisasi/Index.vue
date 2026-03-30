<template>
  <AppLayout title="Realisasi Kegiatan">
    <div class="space-y-4">
      <div class="flex justify-between w-full gap-4 ">
        <div class="w-3/4 bg-white rounded-xl border border-gray-200 p-4">
          <label class="label">Cari Program / Kegiatan</label>
          <input v-model="searchQuery" type="text" class="input w-full"
            placeholder="Ketik nama program atau kegiatan..." />
        </div>

        <div class="w-1/4  bg-white rounded-xl border border-gray-200 p-4">
          <label class="label">Pilih Triwulan Input</label>
          <select v-model="selectedPeriodeId" class="input max-w-sm">
            <option value="">-- Pilih Triwulan --</option>
            <option v-for="p in periodes" :key="p.id" :value="String(p.id)">{{ p.tahun }} {{ p.triwulan }}</option>
          </select>
        </div>
      </div>

      <div class="space-y-6">
        <div v-for="prog in filteredGroupedPrograms" :key="prog.id"
          class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-white">{{ prog.nama }}</h3>
                <p class="text-sm text-orange-100 mt-1">
                  Anggaran: <span class="font-semibold">Rp {{ formatNumber(prog.rencana_biaya) }}</span>
                  | Terpakai: <span class="font-semibold">Rp {{ formatNumber(getProgramTerpakai(prog.id)) }}</span>
                </p>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold text-white">{{ prog.kegiatans.length }}</div>
                <p class="text-xs text-orange-100">Kegiatan</p>
              </div>
            </div>
          </div>

          <div v-if="prog.kegiatans.length > 0" class="divide-y divide-gray-100">
            <div v-for="(k, kIndex) in prog.kegiatans" :key="k.id" class="px-6 py-4" :style="kegiatanRowStyle(kIndex)">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <h4 class="font-semibold text-gray-800 text-lg">{{ k.nama }}</h4>
                    <span :class="statusBadgeClass(k.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium">{{
                      statusLabel(k.status) }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">{{ k.divisi?.nama || '-' }}</p>
                  <p class="text-sm text-gray-600 mt-2">{{ k.deskripsi || 'Tidak ada deskripsi' }}</p>

                  <div class="grid grid-cols-4 gap-4 mt-3 pt-3 border-t border-gray-100">
                    <div>
                      <p class="text-xs text-gray-500 uppercase">Target Output</p>
                      <p class="text-base font-semibold text-gray-800">{{ formatNumber(k.target_output) }} <span
                          class="text-sm text-gray-600">{{ k.satuan || '' }}</span></p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 uppercase">Rencana Biaya</p>
                      <p class="text-base font-semibold text-gray-800 whitespace-nowrap">Rp {{
                        formatNumber(k.rencana_biaya) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 uppercase">Divisi</p>
                      <p class="text-base font-semibold text-gray-800">{{ k.divisi?.nama || '-' }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 uppercase">Pilar Terkait</p>
                      <div class="flex flex-wrap gap-1 mt-1">
                        <span v-for="p in k.pilars || []" :key="p.id"
                          class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">{{ p.nama }}</span>
                        <span v-if="!k.pilars || k.pilars.length === 0" class="text-sm text-gray-500">-</span>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                      <label class="label">Realisasi Output</label>
                      <input type="text" class="input text-right" :disabled="!selectedPeriodeId"
                        :value="rows[k.id]?.realisasi_output || ''"
                        @input="onNumberInput(k.id, 'realisasi_output', $event.target.value)" />
                    </div>

                    <div>
                      <label class="label">Realisasi Biaya</label>
                      <div class="flex items-center gap-2">
                        <span class="text-sm">Rp</span>
                        <input type="text" class="input text-right" :disabled="!selectedPeriodeId"
                          :value="rows[k.id]?.realisasi_biaya || ''"
                          @input="onNumberInput(k.id, 'realisasi_biaya', $event.target.value)" />
                      </div>
                    </div>

                    <div>
                      <label class="label">Evidence</label>
                      <input type="file" multiple class="input text-xs" :disabled="!selectedPeriodeId"
                        @change="onFileChange(k.id, 'evidence_files', $event)" />
                      <div class="mt-1 space-y-1">
                        <div v-for="f in existingFiles(k.id, 'evidence')" :key="f.id"
                          class="flex items-center justify-between gap-2 text-xs">
                          <a :href="route('files.download', f.id)" class="text-blue-600 hover:underline">{{ f.file_name
                            }}</a>
                          <button v-if="isImageFile(f)" type="button" class="text-blue-700 hover:underline"
                            @click="openExistingPreview(f)">
                            Preview
                          </button>
                        </div>
                        <div v-for="(f, idx) in (rows[k.id]?.evidence_files || [])" :key="'new-e-' + idx"
                          class="flex items-center justify-between gap-2 text-xs text-gray-600">
                          <span>{{ f.name }}</span>
                          <button v-if="isImageFile(f)" type="button" class="text-blue-700 hover:underline"
                            @click="openNewFilePreview(f)">
                            Preview
                          </button>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="label">Laporan</label>
                      <input type="file" multiple class="input text-xs" :disabled="!selectedPeriodeId"
                        @change="onFileChange(k.id, 'laporan_files', $event)" />
                      <div class="mt-1 space-y-1">
                        <div v-for="f in existingFiles(k.id, 'laporan')" :key="f.id"
                          class="flex items-center justify-between gap-2 text-xs">
                          <a :href="route('files.download', f.id)" class="text-green-700 hover:underline">{{ f.file_name
                            }}</a>
                          <button v-if="isImageFile(f)" type="button" class="text-green-700 hover:underline"
                            @click="openExistingPreview(f)">
                            Preview
                          </button>
                        </div>
                        <div v-for="(f, idx) in (rows[k.id]?.laporan_files || [])" :key="'new-l-' + idx"
                          class="flex items-center justify-between gap-2 text-xs text-gray-600">
                          <span>{{ f.name }}</span>
                          <button v-if="isImageFile(f)" type="button" class="text-green-700 hover:underline"
                            @click="openNewFilePreview(f)">
                            Preview
                          </button>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="label">Keterangan</label>
                      <textarea class="input h-20" :disabled="!selectedPeriodeId" v-model="rows[k.id].keterangan"
                        placeholder="Keterangan realisasi" />
                      <button
                        class="mt-2 w-full px-3 py-1.5 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 disabled:opacity-50"
                        :disabled="!selectedPeriodeId || processing[k.id]" @click="saveRow(k)">
                        {{ processing[k.id] ? 'Menyimpan...' : 'Simpan' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="px-6 py-8 text-center text-gray-400">
            <p class="text-sm">Tidak ada kegiatan di program ini</p>
          </div>
        </div>

        <div v-if="filteredGroupedPrograms.length === 0"
          class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-8 text-center text-gray-400">
          <p class="text-sm">Belum ada kegiatan disetujui.</p>
        </div>
      </div>

      <Modal :show="confirmDialog.show" title="Sesuaikan Data Periode Berikutnya?" max-width="lg" @close="confirmDialog.show = false">
        <div class="space-y-4">
          <p class="text-sm text-gray-700">
            Nilai <strong>Realisasi Biaya</strong> pada <strong>{{ currentPeriodeName }}</strong>
            (<strong>Rp {{ formatNumber(confirmDialog.newBiaya) }}</strong>)
            lebih besar dari nilai pada periode berikut ini:
          </p>
          <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 overflow-hidden text-sm">
            <li v-for="item in (confirmDialog.subsequentPeriodes || [])" :key="item.periode.id"
              class="flex items-center justify-between px-4 py-2 bg-gray-50">
              <span class="font-medium text-gray-700">{{ item.periode.tahun }} {{ item.periode.triwulan }}</span>
              <span class="text-gray-500 flex items-center gap-2">
                <span>Rp {{ formatNumber(item.realisasi.realisasi_biaya) }}</span>
                <span class="text-gray-400">→</span>
                <span class="text-orange-600 font-semibold">Rp {{ formatNumber(confirmDialog.newBiaya) }}</span>
              </span>
            </li>
          </ul>
          <p class="text-sm text-gray-600">
            Apakah Anda ingin menyesuaikan nilai periode tersebut menjadi sama dengan <strong>{{ currentPeriodeName }}</strong>?
          </p>
          <div class="flex gap-3 justify-end pt-2">
            <button
              @click="proceedSaveWithoutUpdate"
              class="px-4 py-2 text-sm rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium"
            >
              Tidak, Simpan Saja
            </button>
            <button
              @click="proceedSaveWithUpdate"
              class="px-4 py-2 text-sm rounded-lg text-white font-medium hover:opacity-90"
              style="background: #d7561e"
            >
              Ya, Sesuaikan
            </button>
          </div>
        </div>
      </Modal>

      <Modal :show="showPreviewModal" title="Preview Gambar" max-width="2xl" @close="closePreview">
        <div class="space-y-3">
          <p class="text-sm text-gray-600 break-all">{{ previewTitle }}</p>
          <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-2">
            <img :src="previewUrl" :alt="previewTitle" class="w-full h-auto object-contain" />
          </div>
        </div>
      </Modal>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  realisasis: Array,
  kegiatans: Array,
  programSpendings: {
    type: Array,
    default: () => [],
  },
  periodes: Array,
})

const selectedPeriodeId = ref('')
const searchQuery = ref('')
const rows = reactive({})
const processing = reactive({})
const showPreviewModal = ref(false)
const previewUrl = ref('')
const previewTitle = ref('')
const previewObjectUrl = ref('')

const confirmDialog = reactive({
  show: false,
  kegiatan: null,
  newBiaya: 0,
  newOutput: null,
  subsequentPeriodes: [],
})

const groupedPrograms = computed(() => {
  const map = new Map()

  props.kegiatans.forEach((k) => {
    const pid = k.program?.id || 0
    if (!map.has(pid)) {
      map.set(pid, {
        id: pid,
        nama: k.program?.nama || 'Tanpa Program',
        rencana_biaya: 0,
        kegiatans: [],
      })
    }

    const item = map.get(pid)
    item.kegiatans.push(k)
    item.rencana_biaya += Number(k.rencana_biaya || 0)
  })

  return Array.from(map.values())
})

const spendingByProgram = computed(() => {
  const map = new Map()

  ;(props.programSpendings || []).forEach((row) => {
    const programId = Number(row.program_id || 0)
    if (!programId) return

    if (!map.has(programId)) {
      map.set(programId, {
        all: 0,
        byPeriode: new Map(),
      })
    }

    const item = map.get(programId)
    const total = Number(row.total_terpakai || 0)
    const periodeId = Number(row.periode_id || 0)

    item.all += total
    if (periodeId) {
      item.byPeriode.set(periodeId, (item.byPeriode.get(periodeId) || 0) + total)
    }
  })

  return map
})

function getProgramTerpakai(programId) {
  const item = spendingByProgram.value.get(Number(programId || 0))
  if (!item) return 0

  const periodeId = Number(selectedPeriodeId.value || 0)
  if (periodeId) {
    return Number(item.byPeriode.get(periodeId) || 0)
  }

  return Number(item.all || 0)
}

const filteredGroupedPrograms = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()
  if (!keyword) return groupedPrograms.value

  return groupedPrograms.value
    .map((prog) => {
      const programMatch = (prog.nama || '').toLowerCase().includes(keyword)
      const filteredKegiatans = programMatch
        ? prog.kegiatans
        : (prog.kegiatans || []).filter((k) => (k.nama || '').toLowerCase().includes(keyword))

      return {
        ...prog,
        kegiatans: filteredKegiatans,
      }
    })
    .filter((prog) => prog.kegiatans.length > 0)
})

function formatNumber(v) {
  return Number(v || 0).toLocaleString('id-ID')
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

function unformatNumber(v) {
  if (!v) return null
  const clean = String(v).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.]/g, '')
  if (!clean) return null
  return Number(clean)
}

function onNumberInput(kegiatanId, field, value) {
  const clean = String(value || '').replace(/\D/g, '')
  rows[kegiatanId][field] = clean ? Number(clean).toLocaleString('id-ID') : ''
}

function onFileChange(kegiatanId, field, event) {
  rows[kegiatanId][field] = Array.from(event.target.files || [])
}

function kegiatanRowStyle(index) {
  return {
    background: index % 2 === 0 ? '#fff7ed' : '#f8fafc',
  }
}

function isImageFile(file) {
  const fileType = String(file?.file_type || file?.type || '').toLowerCase()
  const fileName = String(file?.file_name || file?.name || '').toLowerCase()
  return fileType.startsWith('image/') || /\.(png|jpe?g|gif|webp)$/i.test(fileName)
}

function openExistingPreview(file) {
  closePreview()
  previewTitle.value = file.file_name || 'Gambar'
  previewUrl.value = route('files.download', file.id)
  showPreviewModal.value = true
}

function openNewFilePreview(file) {
  closePreview()
  const objectUrl = URL.createObjectURL(file)
  previewObjectUrl.value = objectUrl
  previewTitle.value = file.name || 'Gambar'
  previewUrl.value = objectUrl
  showPreviewModal.value = true
}

function closePreview() {
  showPreviewModal.value = false
  previewUrl.value = ''
  previewTitle.value = ''
  if (previewObjectUrl.value) {
    URL.revokeObjectURL(previewObjectUrl.value)
    previewObjectUrl.value = ''
  }
}

function existingFor(kegiatanId) {
  if (!selectedPeriodeId.value) return null
  return props.realisasis.find((r) => r.kegiatan_id === kegiatanId && String(r.periode_id) === selectedPeriodeId.value)
}

function existingFiles(kegiatanId, kategori) {
  const existing = existingFor(kegiatanId)
  return existing?.files?.filter((f) => f.kategori === kategori) || []
}

watch(
  () => selectedPeriodeId.value,
  () => {
    props.kegiatans.forEach((k) => {
      const existing = existingFor(k.id)
      rows[k.id] = {
        realisasi_output: existing?.realisasi_output ? formatNumber(existing.realisasi_output) : '',
        realisasi_biaya: existing?.realisasi_biaya ? formatNumber(existing.realisasi_biaya) : '',
        keterangan: existing?.keterangan || '',
        evidence_files: [],
        laporan_files: [],
      }
    })
  },
  { immediate: true }
)

const currentPeriode = computed(() =>
  props.periodes.find((p) => String(p.id) === selectedPeriodeId.value)
)

const currentPeriodeName = computed(() => {
  const p = currentPeriode.value
  return p ? `${p.tahun} ${p.triwulan}` : ''
})

function getTwNum(triwulan) {
  const m = String(triwulan || '').match(/\d+/)
  return m ? parseInt(m[0]) : 0
}

function checkSubsequentPeriodes(kegiatan, newBiaya) {
  const current = currentPeriode.value
  if (!current) return []
  const currentTw = getTwNum(current.triwulan)
  return props.periodes
    .filter((p) => {
      if (Number(p.tahun) !== Number(current.tahun)) return false
      if (getTwNum(p.triwulan) <= currentTw) return false
      const existing = props.realisasis.find(
        (r) => r.kegiatan_id === kegiatan.id && String(r.periode_id) === String(p.id)
      )
      return existing && Number(existing.realisasi_biaya || 0) < newBiaya
    })
    .map((p) => ({
      periode: p,
      realisasi: props.realisasis.find(
        (r) => r.kegiatan_id === kegiatan.id && String(r.periode_id) === String(p.id)
      ),
    }))
}

function saveRow(kegiatan) {
  const row = rows[kegiatan.id] || {}
  const newBiaya = unformatNumber(row.realisasi_biaya) ?? 0

  if (newBiaya > 0) {
    const subsequent = checkSubsequentPeriodes(kegiatan, newBiaya)
    if (subsequent.length > 0) {
      Object.assign(confirmDialog, {
        show: true,
        kegiatan,
        newBiaya,
        newOutput: unformatNumber(row.realisasi_output),
        subsequentPeriodes: subsequent,
      })
      return
    }
  }

  doSave(kegiatan, [])
}

function proceedSaveWithUpdate() {
  const { kegiatan, subsequentPeriodes } = confirmDialog
  confirmDialog.show = false
  doSave(kegiatan, subsequentPeriodes)
}

function proceedSaveWithoutUpdate() {
  const kegiatan = confirmDialog.kegiatan
  confirmDialog.show = false
  doSave(kegiatan, [])
}

function doSave(kegiatan, updateSubsequentList) {
  const row = rows[kegiatan.id] || {}
  const existing = existingFor(kegiatan.id)
  const formData = new FormData()

  if (!existing) {
    formData.append('kegiatan_id', String(kegiatan.id))
    formData.append('periode_id', String(selectedPeriodeId.value))
  }

  formData.append('realisasi_output', String(unformatNumber(row.realisasi_output) ?? ''))
  formData.append('realisasi_biaya', String(unformatNumber(row.realisasi_biaya) ?? ''))
  formData.append('keterangan', row.keterangan || '')
  ;(row.evidence_files || []).forEach((file) => formData.append('evidence_files[]', file))
  ;(row.laporan_files || []).forEach((file) => formData.append('laporan_files[]', file))

  processing[kegiatan.id] = true

  const afterSave = () => {
    processing[kegiatan.id] = false
    updateSubsequentList.forEach((item) => {
      const fd = new FormData()
      fd.append('_method', 'PUT')
      fd.append('realisasi_output', String(unformatNumber(row.realisasi_output) ?? ''))
      fd.append('realisasi_biaya', String(unformatNumber(row.realisasi_biaya) ?? ''))
      fd.append('keterangan', item.realisasi.keterangan || '')
      router.post(route('realisasi.update', item.realisasi.id), fd, {
        forceFormData: true,
        preserveScroll: true,
      })
    })
  }

  if (existing) {
    formData.append('_method', 'PUT')
    router.post(route('realisasi.update', existing.id), formData, {
      forceFormData: true,
      preserveScroll: true,
      onFinish: afterSave,
    })
  } else {
    router.post(route('realisasi.store'), formData, {
      forceFormData: true,
      preserveScroll: true,
      onFinish: afterSave,
    })
  }
}
</script>

<style scoped>
.input {
  border-width: 2px;
}
</style>
