<template>
  <div class="relative min-h-screen overflow-hidden bg-[#f0ebe3] px-4 py-8 sm:px-6 lg:px-8">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(215,86,30,0.18),_transparent_24%),radial-gradient(circle_at_bottom_right,_rgba(15,60,115,0.16),_transparent_28%),linear-gradient(180deg,_#fcfaf6_0%,_#f0ebe3_100%)]" />

    <div class="mx-auto flex w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] border border-[#ddd2bf] bg-white shadow-[0_36px_90px_-40px_rgba(18,38,63,0.45)] lg:min-h-[720px] lg:flex-row">
      <section class="relative flex flex-1 flex-col justify-between overflow-hidden bg-[#102742] px-7 py-8 text-white sm:px-10 sm:py-10 lg:px-12 lg:py-12">
        <div class="absolute right-0 top-0 h-48 w-48 translate-x-1/4 -translate-y-1/4 rounded-full bg-[#d7561e]/20 blur-3xl" />
        <div class="absolute bottom-0 left-0 h-56 w-56 -translate-x-1/3 translate-y-1/3 rounded-full bg-[#f2d052]/10 blur-3xl" />

        <div class="relative">
          <Link :href="route('home')" class="inline-flex items-center gap-3 rounded-full border border-white/12 bg-white/6 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur-sm transition hover:bg-white/10">
          <img src="/logo-bayan.png" alt="Logo Bayan" class="h-9 w-9 rounded-xl bg-white/10 p-1.5 object-contain" />
            <span>Kembali ke Landing Page</span>
          </Link>

          <div class="mt-10 flex items-center gap-4">
            <!-- <img src="/logo-bayan.png" alt="Logo Bayan" class="h-14 w-14 rounded-2xl bg-white/10 p-2 object-contain" /> -->
            <img src="/logo-bayan-text.png" alt="PPM Bayan" class="h-10 w-auto object-contain " />
          </div>

          <p class="mt-8 text-xs font-semibold uppercase tracking-[0.26em] text-[#f0c9a6]">Secure Access</p>
          <h1 class="mt-4 max-w-lg text-3xl font-black leading-tight tracking-[-0.03em] sm:text-4xl">
            Area login dibuat terpisah agar akses kerja lebih fokus.
          </h1>
          <p class="mt-5 max-w-xl text-sm leading-7 text-slate-300 sm:text-base">
            Masuk ke PPM Bayan untuk mengelola program, kegiatan, realisasi, dan resume anggaran dengan struktur data yang konsisten lintas divisi.
          </p>
        </div>

        <div class="relative mt-10 grid gap-4 sm:grid-cols-2 lg:mt-0">
          <div class="rounded-[1.5rem] border border-white/10 bg-white/8 p-5 backdrop-blur-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Masuk lalu</p>
            <p class="mt-3 text-lg font-bold text-white">Pantau status kegiatan</p>
            <p class="mt-2 text-sm leading-6 text-slate-300">Lihat progres, verifikasi, dan realisasi tanpa memindah konteks kerja.</p>
          </div>
          <div class="rounded-[1.5rem] border border-white/10 bg-[#f6eee3] p-5 text-slate-900">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Workflow</p>
            <p class="mt-3 text-lg font-bold text-[#102742]">Perencanaan sampai pelaporan</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">Semua data tetap berada dalam satu alur yang mudah ditinjau.</p>
          </div>
        </div>
      </section>

      <section class="flex w-full items-center bg-[#fffdf9] px-6 py-8 sm:px-10 sm:py-10 lg:max-w-[34rem] lg:px-12 lg:py-12">
        <div class="w-full">
          <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#d7561e]">Autentikasi Pengguna</p>
            <h2 class="mt-3 text-3xl font-black tracking-[-0.03em] text-[#12263f]">Masuk ke sistem</h2>
            <p class="mt-3 text-sm leading-7 text-slate-500">Gunakan akun terdaftar untuk membuka dashboard dan modul kerja internal.</p>
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label class="label !mb-2 text-[#23384f]">Email</label>
              <input
                v-model="form.email"
                type="email"
                autocomplete="email"
                class="input h-13 rounded-2xl border-[#d7d2c8] bg-white px-4 py-3"
                :class="{ 'input-error': form.errors.email }"
                placeholder="nama@ppm-bayan.com"
              />
              <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
            </div>

            <div>
              <div class="mb-2 flex items-center justify-between gap-3">
                <label class="label !mb-0 text-[#23384f]">Password</label>
                <button type="button" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400 transition hover:text-slate-600" @click="showPassword = !showPassword">
                  {{ showPassword ? 'Sembunyikan' : 'Lihat Password' }}
                </button>
              </div>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                class="input h-13 rounded-2xl border-[#d7d2c8] bg-white px-4 py-3"
                :class="{ 'input-error': form.errors.password }"
                placeholder="Masukkan password"
              />
              <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#ece4d7] bg-[#f8f3ec] px-4 py-3">
              <label for="remember" class="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-600">
                <input v-model="form.remember" id="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-300" />
                <span>Ingat saya di perangkat ini</span>
              </label>
              <span class="text-xs uppercase tracking-[0.22em] text-slate-400">PPM Bayan</span>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="flex w-full items-center justify-center rounded-2xl bg-[#d7561e] px-5 py-3.5 text-sm font-semibold text-white shadow-[0_18px_35px_-22px_rgba(215,86,30,0.95)] transition hover:bg-[#bf4918] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ form.processing ? 'Memproses Login...' : 'Masuk ke Dashboard' }}
            </button>
          </form>

          <div class="mt-8 rounded-[1.5rem] border border-[#e6ddcf] bg-white p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Catatan akses</p>
            <p class="mt-3 text-sm leading-7 text-slate-600">Jika sesi Anda masih aktif, sistem akan langsung mengarahkan ke dashboard tanpa menampilkan form login lagi.</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

function submit() {
  form.post(route('login.post'))
}
</script>
