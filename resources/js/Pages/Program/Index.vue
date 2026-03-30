<template>
  <AppLayout title="Program">
    <template #header-actions>
      <button @click="openCreate" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">+ Tambah Program</button>
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
      <div class="overflow-x-auto">
        <table class="w-full min-w-[1400px] text-sm">
          <thead>
            <tr class="bg-orange-500 text-white text-xs font-medium">
              <th class="px-4 py-2 text-left">Divisi / Unit</th>
              <th class="px-4 py-2 text-left">Kode Ref</th>
              <th class="px-4 py-2 text-left">Nama Program / Kegiatan</th>
              <th class="px-4 py-2 text-right">Rencana Biaya</th>
              <th class="px-4 py-2 text-left">Deskripsi</th>
              <th class="px-4 py-2 text-center">Target Output</th>
              <th class="px-4 py-2 text-center">Satuan</th>
              <th class="px-4 py-2 text-center">Pilar Terkait</th>
              <th class="px-4 py-2 text-left">Status / Anggaran</th>
              <th class="px-4 py-2 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <template v-for="p in programs" :key="p.id">
              <tr class="bg-white hover:bg-gray-50 border-b border-gray-100">
                <td class="px-4 py-3 text-center text-gray-400">-</td>
                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ rekCode('PRG', p.id) }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-800">
                    <Link :href="route('program.show', p.id)" class="hover:underline" style="color:#D7561E">{{ p.nama }}</Link>
                  </p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ p.kegiatans?.length || 0 }} kegiatan · {{ countByStatus(p, 'disetujui') }} disetujui</p>
                </td>
                <td class="px-4 py-3 text-right font-medium text-gray-800 whitespace-nowrap">Rp {{ amount(p.rencana_biaya) }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ p.deskripsi || '-' }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ p.target_output ?? '-' }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ p.satuan || '-' }}</td>
                <td class="px-4 py-3 text-center">-</td>
                <td class="px-4 py-3 text-sm">
                  <div class="text-gray-600 mb-1 whitespace-nowrap">Terpakai: Rp {{ amount(p.total_kegiatan_biaya) }}</div>
                  <div :class="p.sisa_anggaran < 0 ? 'text-red-600 font-medium' : 'text-green-600 font-medium'" class="whitespace-nowrap">
                    Sisa: Rp {{ amount(p.sisa_anggaran) }}
                  </div>
                </td>
                <td class="px-4 py-3 text-center space-y-1">
                  <button @click="openEdit(p)" class="block w-full text-xs px-2 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition-colors">Ubah</button>
                  <button @click="confirmDelete(p)" class="block w-full text-xs px-2 py-1 rounded border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Hapus</button>
                </td>
              </tr>

              <tr v-for="k in p.kegiatans || []" :key="k.id" class="border-b border-gray-100" style="background:#E8F4FB;">
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.divisi?.nama || '-' }}</td>
                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ rekCode('KGT', k.id) }}</td>
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-800">{{ k.nama }}</p>
                </td>
                <td class="px-4 py-3 text-right text-sm text-gray-800 font-medium whitespace-nowrap">Rp {{ amount(k.rencana_biaya) }}</td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ k.deskripsi || '-' }}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-800">{{ k.target_output ?? '-' }}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-800">{{ p.satuan || '-' }}</td>
                <td class="px-4 py-3 text-center text-xs">
                  <div v-if="k.pilars && k.pilars.length > 0" class="flex flex-wrap gap-1 justify-center">
                    <span v-for="pl in k.pilars" :key="pl.id" class="px-1.5 py-0.5 rounded-lg bg-orange-100 text-orange-700 text-[10px] font-medium">{{ pl.nama }}</span>
                  </div>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs font-medium px-2 py-1 rounded-lg" :class="statusBadgeClass(k.status)">{{ statusLabel(k.status) }}</span>
                </td>
                <td class="px-4 py-3 text-center">-</td>
              </tr>

              <tr v-if="!p.kegiatans || p.kegiatans.length === 0" class="bg-gray-50 border-b border-gray-100">
                <td colspan="10" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada kegiatan pada program ini.</td>
              </tr>
            </template>

            <tr v-if="programs.length === 0" class="bg-white">
              <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data program.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

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
          <button type="button" @click="closeModal" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button type="submit" :disabled="form.processing" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 disabled:opacity-60 cursor-pointer">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus Program" @close="showDeleteModal = false">
      <p class="text-gray-600">Apakah Anda yakin ingin menghapus program <strong>{{ deleting?.nama }}</strong>?</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button @click="deleteProgram" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Hapus</button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ programs: Array })

const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleting = ref(null)

const form = useForm({ nama: '', deskripsi: '', target_output: '', satuan: '', rencana_biaya: '' })

function amount(v) {
  return Number(v || 0).toLocaleString('id-ID')
}

function countByStatus(program, status) {
  return program.kegiatans?.filter(k => k.status === status).length || 0
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

function rekCode(prefix, id) {
  return `${prefix}.${String(id).padStart(3, '0')}`
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
