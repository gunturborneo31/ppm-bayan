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
              <th class="sticky top-0 z-20 px-4 py-2 text-right bg-orange-500 whitespace-nowrap">Rencana Biaya</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-left bg-orange-500">Deskripsi</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500 whitespace-nowrap">Target Output</th>
              <th class="sticky top-0 z-20 px-4 py-2 text-center bg-orange-500 whitespace-nowrap">Satuan</th>
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
                <td class="px-4 py-3 text-center text-xs text-gray-800">
                  <div>{{ k.target_output ?? '-' }}</div>
                  <div class="text-[10px] text-gray-500">/ bln: {{ k.target_bulanan ?? '-' }}</div>
                </td>
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
                    <button v-if="isSuperadmin && canVerify(k)" @click="approveKegiatan(k)" class="text-[11px] px-2 py-1 rounded border border-green-300 bg-green-50 text-green-600 hover:bg-green-100 transition-colors">Setujui</button>
                    <button v-if="isSuperadmin && canVerify(k)" @click="openReject(k)" class="text-[11px] px-2 py-1 rounded border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Tolak</button>
                    <button v-if="canResubmit(k)" @click="submitUlang(k)" class="text-[11px] px-2 py-1 rounded border border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">Ajukan Ulang</button>
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
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="label">Pilar</label>
            <SearchableSelect
              v-model="selectedPilarId"
              :options="filteredPilarOptions"
              placeholder="Pilih pilar"
              search-placeholder="Cari pilar..."
              empty-text="Pilar tidak ditemukan."
              :error="isPilarInvalid() || Boolean(form.errors.program_id)"
              :error-message="isPilarInvalid() ? 'Pilar wajib dipilih.' : form.errors.program_id"
            />
          </div>
          <div>
            <label class="label">Program</label>
            <SearchableSelect
              v-model="form.program_id"
              :options="filteredProgramOptions"
              placeholder="Pilih program"
              search-placeholder="Cari program..."
              :empty-text="selectedPilarId ? 'Tidak ada program pada pilar ini.' : 'Pilih pilar terlebih dahulu.'"
              :disabled="!selectedPilarId"
              :error="isProgramInvalid() || Boolean(form.errors.program_id)"
              :error-message="isProgramInvalid() ? 'Program wajib dipilih.' : form.errors.program_id"
            />
            <p v-if="!selectedPilarId" class="mt-1 text-xs text-slate-500">Pilih pilar terlebih dahulu untuk menampilkan program.</p>
            <p v-else class="mt-1 text-xs text-slate-500">{{ filteredProgramOptions.length }} program tersedia dari pilar yang dipilih.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
          <div>
            <label class="label">Divisi</label>
            <select v-if="isSuperadmin" v-model="form.divisi_id" class="input" :class="{'input-error': form.errors.divisi_id, 'border-red-300 bg-red-50/60 hover:border-red-400': isDivisiInvalid() && !form.errors.divisi_id}" :title="isDivisiInvalid() ? 'Divisi wajib dipilih.' : ''">
              <option value="">-- Pilih Divisi --</option>
              <option v-for="d in divisis" :key="d.id" :value="d.id">{{ d.nama }}</option>
            </select>
            <input v-else :value="currentUserDivisiName" class="input bg-gray-50" type="text" disabled />
            <p v-if="isDivisiInvalid() && !form.errors.divisi_id" class="err">Divisi wajib dipilih.</p>
            <p v-if="form.errors.divisi_id" class="err">{{ form.errors.divisi_id }}</p>
          </div>
        </div>

        <div v-if="selectedPilar" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800">
          <p class="font-semibold">Deskripsi Pilar</p>
          <p class="mt-1">{{ selectedPilar.deskripsi || 'Tidak ada deskripsi pilar.' }}</p>
        </div>

        <div v-if="selectedProgram" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
          <p class="font-semibold">Deskripsi Program</p>
          <p class="mt-1">{{ selectedProgram.deskripsi || 'Tidak ada deskripsi program.' }}</p>
        </div>
        <div>
          <label class="label">Nama Kegiatan</label>
          <input v-model="form.nama" class="input" :class="{'input-error': form.errors.nama, 'border-red-300 bg-red-50/60 hover:border-red-400': isNamaInvalid() && !form.errors.nama}" :title="isNamaInvalid() ? 'Nama kegiatan wajib diisi.' : ''" type="text" />
          <p v-if="isNamaInvalid() && !form.errors.nama" class="err">Nama kegiatan wajib diisi.</p>
          <p v-if="form.errors.nama" class="err">{{ form.errors.nama }}</p>
        </div>
        <div>
          <label class="label">Deskripsi</label>
          <textarea v-model="form.deskripsi" class="input h-20 resize-none" rows="3" />
        </div>
        <div>
          <label class="label">Berdasarkan</label>
          <input v-model="form.berdasarkan" class="input" :class="{'input-error': form.errors.berdasarkan}" type="text" placeholder="" />
          <p v-if="form.errors.berdasarkan" class="err">{{ form.errors.berdasarkan }}</p>
        </div>
        <div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
          <div class="flex items-center justify-between gap-3">
            <div>
              <label class="label">Lokasi Kegiatan</label>
              <p class="text-xs text-gray-500">Tambahkan satu atau lebih lokasi pelaksanaan kegiatan.</p>
            </div>
            <button type="button" @click="addLokasiRow" class="px-3 py-2 rounded border border-gray-300 bg-white text-sm font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">+ Tambah Lokasi</button>
          </div>
          <p v-if="form.errors.lokasis" class="err">{{ form.errors.lokasis }}</p>

          <div v-for="(lokasi, index) in form.lokasis" :key="index" class="rounded-xl border bg-white p-4 space-y-3 transition" :class="isLokasiInvalid(lokasi) ? 'border-red-200 shadow-[0_0_0_1px_rgba(248,113,113,0.18)]' : 'border-gray-200'" :title="isLokasiInvalid(lokasi) ? `Data lokasi ${index + 1} belum lengkap: ${lokasiMissingFields(lokasi).join(', ')}.` : ''">
            <div class="flex items-center justify-between gap-3">
              <p class="text-sm font-semibold text-gray-800">Lokasi {{ index + 1 }}</p>
              <button v-if="form.lokasis.length > 1" type="button" @click="removeLokasiRow(index)" class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
            </div>
            <p v-if="isLokasiInvalid(lokasi)" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
              Lengkapi: {{ lokasiMissingFields(lokasi).join(', ') }}.
            </p>

            <div>
              <label class="label">Nama Lokasi</label>
              <input v-model="lokasi.lokasi" :list="lokasiDatalistId" class="input" :class="lokasiFieldClass(lokasi, 'lokasi', form.errors[`lokasis.${index}.lokasi`])" :title="lokasiFieldTitle(lokasi, 'lokasi', 'Nama lokasi')" type="text" placeholder="Pilih lokasi yang ada atau ketik lokasi baru" />
              <p v-if="form.errors[`lokasis.${index}.lokasi`]" class="err">{{ form.errors[`lokasis.${index}.lokasi`] }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Waktu Pelaksanaan Mulai</label>
                <input v-model="lokasi.tanggal_mulai" class="input" :class="lokasiFieldClass(lokasi, 'tanggal_mulai', form.errors[`lokasis.${index}.tanggal_mulai`])" :title="lokasiFieldTitle(lokasi, 'tanggal_mulai', 'Tanggal mulai')" type="date" />
                <p v-if="form.errors[`lokasis.${index}.tanggal_mulai`]" class="err">{{ form.errors[`lokasis.${index}.tanggal_mulai`] }}</p>
              </div>
              <div>
                <label class="label">Waktu Pelaksanaan Akhir</label>
                <input v-model="lokasi.tanggal_selesai" class="input" :class="lokasiFieldClass(lokasi, 'tanggal_selesai', form.errors[`lokasis.${index}.tanggal_selesai`])" :title="lokasiFieldTitle(lokasi, 'tanggal_selesai', 'Tanggal selesai')" type="date" />
                <p v-if="form.errors[`lokasis.${index}.tanggal_selesai`]" class="err">{{ form.errors[`lokasis.${index}.tanggal_selesai`] }}</p>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="label">Target Output</label>
                <input :value="lokasi.target_output" @input="setLokasiNumeric(index, 'target_output', $event.target.value)" class="input text-right" :class="lokasiFieldClass(lokasi, 'target_output', form.errors[`lokasis.${index}.target_output`])" :title="lokasiFieldTitle(lokasi, 'target_output', 'Target output')" type="text" inputmode="numeric" />
                <p v-if="form.errors[`lokasis.${index}.target_output`]" class="err">{{ form.errors[`lokasis.${index}.target_output`] }}</p>
              </div>
              <div>
                <label class="label">Satuan</label>
                <input v-model="lokasi.satuan" class="input" :class="lokasiFieldClass(lokasi, 'satuan', form.errors[`lokasis.${index}.satuan`])" :title="lokasiFieldTitle(lokasi, 'satuan', 'Satuan')" type="text" placeholder="Contoh: Orang" />
                <p v-if="form.errors[`lokasis.${index}.satuan`]" class="err">{{ form.errors[`lokasis.${index}.satuan`] }}</p>
              </div>
              <div>
                <label class="label">Rencana Biaya (Rp)</label>
                <input :value="lokasi.rencana_biaya" @input="setLokasiNumeric(index, 'rencana_biaya', $event.target.value)" class="input text-right" :class="lokasiFieldClass(lokasi, 'rencana_biaya', form.errors[`lokasis.${index}.rencana_biaya`])" :title="lokasiFieldTitle(lokasi, 'rencana_biaya', 'Nominal')" type="text" inputmode="numeric" />
                <p v-if="form.errors[`lokasis.${index}.rencana_biaya`]" class="err">{{ form.errors[`lokasis.${index}.rencana_biaya`] }}</p>
              </div>
            </div>
          </div>
        </div>

        <datalist :id="lokasiDatalistId">
          <option v-for="namaLokasi in lokasiOptions" :key="namaLokasi" :value="namaLokasi" />
        </datalist>

        <div class="rounded-xl border p-4" :class="isBudgetExceeded ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">RKAB Program</p>
              <p class="mt-1 text-lg font-bold text-gray-800">Rp {{ amount(selectedProgram?.rencana_biaya || 0) }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Biaya Lokasi</p>
              <p class="mt-1 text-lg font-bold text-gray-800">Rp {{ amount(currentLokasiTotalBiaya) }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sisa Setelah Input</p>
              <p class="mt-1 text-lg font-bold" :class="isBudgetExceeded ? 'text-red-700' : 'text-emerald-700'">Rp {{ amount(remainingBudgetAfterInput) }}</p>
            </div>
          </div>
          <p class="mt-3 text-sm" :class="isBudgetExceeded ? 'text-red-700' : 'text-emerald-700'">
            {{ isBudgetExceeded ? 'Total biaya lokasi melebihi sisa RKAB program. Periksa kembali rincian lokasi.' : 'Total biaya lokasi masih sesuai dengan RKAB program dan siap disubmit.' }}
          </p>
        </div>

        <p class="text-xs text-gray-500">Pilar kegiatan mengikuti pilar dari program yang dipilih. Nilai target dan anggaran kegiatan akan dihitung dari seluruh lokasi.</p>
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 cursor-pointer">Batal</button>
          <button type="submit" :disabled="form.processing || isBudgetExceeded" class="px-3 py-2 rounded border border-gray-300 bg-white font-bold text-gray-800 hover:bg-gray-100 disabled:opacity-60 cursor-pointer">
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
import { ref, computed, watch } from 'vue'
import { useForm, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
  kegiatans: Array,
  programs: Array,
  allPrograms: Array,
  divisis: Array,
  lokasiOptions: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const isSuperadmin = computed(() => page.props.auth.user?.is_superadmin || page.props.auth.user?.is_cdo)
const currentUserDivisiId = computed(() => page.props.auth.user?.divisi_id || null)
const currentUserDivisiName = computed(() => {
  const found = props.divisis.find(d => d.id === currentUserDivisiId.value)
  return found ? found.nama : '-'
})
const lokasiDatalistId = 'lokasi-kegiatan-options'

const showModal = ref(false)
const showRejectModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const rejecting = ref(null)
const deleting = ref(null)
const selectedPilarId = ref('')
const submitAttempted = ref(false)

const form = useForm({
  program_id: '', divisi_id: '', nama: '', deskripsi: '', berdasarkan: '',
  lokasis: [],
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
function emptyLokasi() {
  return { lokasi: '', tanggal_mulai: '', tanggal_selesai: '', target_output: '', satuan: '', rencana_biaya: '' }
}
function addLokasiRow() {
  form.lokasis.push(emptyLokasi())
}
function removeLokasiRow(index) {
  form.lokasis.splice(index, 1)
}
function setLokasiNumeric(index, field, value) {
  const clean = String(value || '').replace(/\D/g, '')
  form.lokasis[index][field] = clean ? Number(clean).toLocaleString('id-ID') : ''
}
function formatDateInput(value) {
  return value ? String(value).slice(0, 10) : ''
}
function hydrateLokasis(lokasis = []) {
  if (!lokasis.length) {
    return [emptyLokasi()]
  }

  return lokasis.map((lokasi) => ({
    lokasi: lokasi.lokasi || '',
    tanggal_mulai: formatDateInput(lokasi.tanggal_mulai),
    tanggal_selesai: formatDateInput(lokasi.tanggal_selesai),
    target_output: amount(lokasi.target_output),
    satuan: lokasi.satuan || '',
    rencana_biaya: amount(lokasi.rencana_biaya),
  }))
}
const allPrograms = computed(() => props.allPrograms || [])
const allPilarOptions = computed(() => {
  const map = new Map()

  allPrograms.value.forEach((program) => {
    const pilar = program.pilar
    if (pilar?.id && !map.has(Number(pilar.id))) {
      map.set(Number(pilar.id), {
        id: Number(pilar.id),
        nama: pilar.nama,
        deskripsi: pilar.deskripsi || '',
      })
    }
  })

  return Array.from(map.values()).sort((left, right) => left.nama.localeCompare(right.nama))
})

const filteredPilarOptions = computed(() => allPilarOptions.value)

const filteredProgramOptions = computed(() => {
  return allPrograms.value.filter((program) => {
    if (!selectedPilarId.value) return false

    const samePilar = String(program.pilar?.id || program.pilar_id || '') === String(selectedPilarId.value)
    return samePilar
  })
})

const selectedPilar = computed(() => allPilarOptions.value.find((pilar) => String(pilar.id) === String(selectedPilarId.value)) || null)
const selectedProgram = computed(() => allPrograms.value.find((program) => Number(program.id) === Number(form.program_id)) || null)
const currentLokasiTotalBiaya = computed(() => (form.lokasis || []).reduce((total, lokasi) => total + Number(normalize(lokasi.rencana_biaya) || 0), 0))
const editingCurrentBiaya = computed(() => Number(editing.value?.rencana_biaya || 0))
const availableBudgetBeforeCurrent = computed(() => {
  if (!selectedProgram.value) return 0
  const baseUsed = Number(selectedProgram.value.total_kegiatan_biaya || 0)
  const currentUsed = editing.value && Number(editing.value.program_id) === Number(selectedProgram.value.id) ? editingCurrentBiaya.value : 0
  return Number(selectedProgram.value.rencana_biaya || 0) - (baseUsed - currentUsed)
})
const remainingBudgetAfterInput = computed(() => availableBudgetBeforeCurrent.value - currentLokasiTotalBiaya.value)
const isBudgetExceeded = computed(() => selectedProgram.value ? remainingBudgetAfterInput.value < 0 : false)

watch(selectedPilarId, () => {
  const isCurrentProgramValid = filteredProgramOptions.value.some((program) => Number(program.id) === Number(form.program_id))

  if (!isCurrentProgramValid) {
    form.program_id = ''
  }
})

watch(
  () => form.program_id,
  (programId) => {
    const found = allPrograms.value.find((program) => Number(program.id) === Number(programId))
    selectedPilarId.value = found ? String(found.pilar?.id || found.pilar_id || '') : ''
  }
)

function hasValue(value) {
  return String(value ?? '').trim() !== ''
}

function isPilarInvalid() {
  return submitAttempted.value && !selectedPilarId.value
}

function isProgramInvalid() {
  return submitAttempted.value && (!form.program_id || !programMatchesSelectedPilar())
}

function programMatchesSelectedPilar() {
  if (!form.program_id || !selectedPilarId.value) {
    return false
  }

  const found = allPrograms.value.find((program) => Number(program.id) === Number(form.program_id))
  return String(found?.pilar?.id || found?.pilar_id || '') === String(selectedPilarId.value)
}

function isDivisiInvalid() {
  return submitAttempted.value && isSuperadmin.value && !form.divisi_id
}

function isNamaInvalid() {
  return submitAttempted.value && !hasValue(form.nama)
}

function lokasiMissingFields(lokasi) {
  const missing = []

  if (!hasValue(lokasi.lokasi)) missing.push('lokasi')
  if (!hasValue(lokasi.tanggal_mulai)) missing.push('tanggal mulai')
  if (!hasValue(lokasi.tanggal_selesai)) missing.push('tanggal selesai')
  if (!hasValue(lokasi.target_output)) missing.push('target output')
  if (!hasValue(lokasi.satuan)) missing.push('satuan')
  if (!hasValue(lokasi.rencana_biaya)) missing.push('nominal')

  return missing
}

function isLokasiInvalid(lokasi) {
  return submitAttempted.value && lokasiMissingFields(lokasi).length > 0
}

function lokasiFieldClass(lokasi, field, serverError) {
  return {
    'input-error': Boolean(serverError),
    'border-red-300 bg-red-50/60 hover:border-red-400': !serverError && submitAttempted.value && !hasValue(lokasi[field]),
  }
}

function lokasiFieldTitle(lokasi, field, label) {
  if (!submitAttempted.value || hasValue(lokasi[field])) {
    return ''
  }

  return `${label} wajib diisi.`
}
function canVerify(k) { return ['diajukan', 'diajukan_ulang'].includes(k.status) }
function canResubmit(k) { return ['revisi', 'ditolak'].includes(k.status) }
function canEdit(k) { return !['disetujui', 'diajukan', 'diajukan_ulang'].includes(k.status) }
function canDelete(k) { return !['disetujui', 'diajukan', 'diajukan_ulang'].includes(k.status) }

function statusBadgeClass(s) {
  return {
    draft: 'bg-gray-100 text-gray-700',
    diajukan: 'bg-orange-100 text-orange-700',
    diajukan_ulang: 'bg-orange-100 text-orange-700',
    revisi: 'bg-yellow-100 text-yellow-700',
    disetujui: 'bg-green-100 text-green-700',
    ditolak: 'bg-red-100 text-red-700',
    selesai: 'bg-blue-100 text-blue-700',
  }[s] || 'bg-gray-100 text-gray-700'
}

function statusLabel(s) {
  if (s === 'diajukan_ulang') return 'Diajukan Ulang'
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
  submitAttempted.value = false
  form.reset()
  form.clearErrors()
  form.lokasis = [emptyLokasi()]
  selectedPilarId.value = ''
  if (!isSuperadmin.value) {
    form.divisi_id = currentUserDivisiId.value || ''
  }
  showModal.value = true
}
function openEdit(k) {
  editing.value = k
  submitAttempted.value = false
  form.clearErrors()
  form.program_id = k.program_id
  form.divisi_id = k.divisi_id
  form.nama = k.nama
  form.deskripsi = k.deskripsi || ''
  form.berdasarkan = k.berdasarkan || k.berasarkan || ''
  form.lokasis = hydrateLokasis(k.lokasis || [])
  const foundProgram = allPrograms.value.find((program) => Number(program.id) === Number(k.program_id))
  selectedPilarId.value = foundProgram ? String(foundProgram.pilar?.id || foundProgram.pilar_id || '') : ''
  showModal.value = true
}
function closeModal() {
  showModal.value = false
  submitAttempted.value = false
  form.clearErrors()
}

function save() {
  submitAttempted.value = true

  if (!isSuperadmin.value) {
    form.divisi_id = currentUserDivisiId.value || ''
  }

  if (!selectedPilarId.value || !form.program_id || !programMatchesSelectedPilar() || (isSuperadmin.value && !form.divisi_id) || !hasValue(form.nama)) {
    window.alert('Masih ada data wajib yang belum diisi. Periksa field yang ditandai merah.')
    return
  }

  const invalidLokasiIndex = (form.lokasis || []).findIndex((lokasi) => {
    return lokasiMissingFields(lokasi).length > 0
  })

  if (invalidLokasiIndex !== -1) {
    window.alert(`Data lokasi ke-${invalidLokasiIndex + 1} belum lengkap. Wajib isi: lokasi, tanggal, target, satuan, dan nominal.`)
    return
  }

  form.transform((data) => ({
    ...data,
    lokasis: (data.lokasis || []).map((lokasi) => ({
      ...lokasi,
      target_output: normalize(lokasi.target_output),
      rencana_biaya: normalize(lokasi.rencana_biaya),
    })),
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
function submitUlang(k) {
  router.post(route('kegiatan.submit', k.id))
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
</script>
