<template>
  <AppLayout title="Manajemen User">
    <template #header-actions>
      <button @click="openCreate" class="btn-primary">+ Tambah User</button>
    </template>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Role</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Divisi</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ u.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.email }}</td>
            <td class="px-4 py-3 text-center">
              <span
                class="px-2 py-0.5 rounded-full text-xs"
                :class="roleBadgeClass(u.role)"
              >
                {{ roleLabel(u.role) }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ u.divisi?.nama || '-' }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-end">
                <button @click="openEdit(u)" class="text-gray-500 hover:text-gray-700 text-xs">Edit</button>
                <button @click="confirmDelete(u)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada user.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showModal" :title="editing ? 'Edit User' : 'Tambah User'" @close="closeModal">
      <form @submit.prevent="save" class="space-y-4">
        <div>
          <label class="label">Nama</label>
          <input v-model="form.name" class="input" :class="{ 'input-error': form.errors.name }" type="text" />
          <p v-if="form.errors.name" class="err">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="label">Email</label>
          <input v-model="form.email" class="input" :class="{ 'input-error': form.errors.email }" type="email" />
          <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="label">Role</label>
          <select v-model="form.role" class="input" :class="{ 'input-error': form.errors.role }">
            <option value="superadmin">Superadmin</option>
            <option value="divisi">Divisi</option>
            <option value="pimpinan">Pimpinan</option>
          </select>
          <p v-if="form.errors.role" class="err">{{ form.errors.role }}</p>
        </div>

        <div v-if="form.role === 'divisi'">
          <label class="label">Divisi</label>
          <select v-model="form.divisi_id" class="input" :class="{ 'input-error': form.errors.divisi_id }">
            <option value="">Pilih divisi</option>
            <option v-for="d in divisis" :key="d.id" :value="d.id">{{ d.nama }}</option>
          </select>
          <p v-if="form.errors.divisi_id" class="err">{{ form.errors.divisi_id }}</p>
        </div>

        <div>
          <label class="label">Password {{ editing ? '(Kosongkan jika tidak diubah)' : '' }}</label>
          <input
            v-model="form.password"
            class="input"
            :class="{ 'input-error': form.errors.password }"
            type="password"
            autocomplete="new-password"
          />
          <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>
        </div>

        <div>
          <label class="label">Konfirmasi Password</label>
          <input
            v-model="form.password_confirmation"
            class="input"
            type="password"
            autocomplete="new-password"
          />
        </div>

        <p v-if="form.errors.user" class="err">{{ form.errors.user }}</p>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" title="Hapus User" @close="showDeleteModal = false">
      <p class="text-gray-600">Hapus user <strong>{{ deleting?.name }}</strong>?</p>
      <p v-if="form.errors.user" class="err mt-2">{{ form.errors.user }}</p>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
          <button @click="deleteUser" class="btn-danger">Hapus</button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ users: Array, divisis: Array })

const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleting = ref(null)

const form = useForm({
  name: '',
  email: '',
  role: 'divisi',
  divisi_id: '',
  password: '',
  password_confirmation: '',
})

watch(
  () => form.role,
  (role) => {
    if (role === 'superadmin') {
      form.divisi_id = ''
      form.clearErrors('divisi_id')
    }
  }
)

function roleLabel(role) {
  if (role === 'superadmin') return 'Superadmin'
  if (role === 'pimpinan') return 'Pimpinan'
  return 'Divisi'
}

function roleBadgeClass(role) {
  if (role === 'superadmin') return 'bg-blue-100 text-blue-700'
  if (role === 'pimpinan') return 'bg-purple-100 text-purple-700'
  return 'bg-orange-100 text-orange-700'
}

function openCreate() {
  editing.value = null
  form.reset()
  form.role = 'divisi'
  form.divisi_id = ''
  showModal.value = true
}

function openEdit(user) {
  editing.value = user
  form.name = user.name
  form.email = user.email
  form.role = user.role
  form.divisi_id = user.divisi_id || ''
  form.password = ''
  form.password_confirmation = ''
  form.clearErrors()
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.clearErrors()
}

function save() {
  if (editing.value) {
    form.put(route('user.update', editing.value.id), {
      onSuccess: closeModal,
      preserveScroll: true,
    })
    return
  }

  form.post(route('user.store'), {
    onSuccess: closeModal,
    preserveScroll: true,
  })
}

function confirmDelete(user) {
  deleting.value = user
  form.clearErrors('user')
  showDeleteModal.value = true
}

function deleteUser() {
  router.delete(route('user.destroy', deleting.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      deleting.value = null
    },
  })
}
</script>
