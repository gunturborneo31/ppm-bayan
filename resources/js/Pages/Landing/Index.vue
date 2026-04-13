<template>
  <div class="min-h-screen overflow-hidden bg-[#f4f1ea] text-slate-900">
    <div class="absolute inset-x-0 top-0 -z-10 h-[520px] bg-[radial-gradient(circle_at_top_left,_rgba(215,86,30,0.18),_transparent_36%),radial-gradient(circle_at_top_right,_rgba(15,60,115,0.14),_transparent_30%),linear-gradient(180deg,_#fffaf4_0%,_#f4f1ea_58%,_#efe8dd_100%)]" />
    <div class="absolute inset-y-0 right-0 -z-10 w-[34rem] bg-[radial-gradient(circle_at_center,_rgba(15,60,115,0.08),_transparent_58%)]" />

    <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
      <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-[1.25rem] bg-white shadow-[0_18px_35px_-18px_rgba(15,60,115,0.45)] ring-1 ring-black/5">
          <img src="/logo-bayan.png" alt="Logo Bayan" class="h-10 w-10 object-contain" />
        </div>
        <div>
          <img src="/logo-bayan-text.png" alt="PPM Bayan" class="h-8 w-auto object-contain" />
          <p class="mt-1 text-xs font-medium uppercase tracking-[0.26em] text-slate-500">Program Performance Monitoring</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <Link
          v-if="authUser"
          :href="route('dashboard')"
          class="rounded-full border border-slate-300/80 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
        >
          Buka Dashboard
        </Link>
        <Link
          v-else
          :href="route('login')"
          class="rounded-full bg-[#0f3c73] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_14px_30px_-18px_rgba(15,60,115,0.85)] transition hover:bg-[#123f79]"
        >
          Login Sistem
        </Link>
      </div>
    </header>

    <main class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr] lg:px-10 lg:pb-24 lg:pt-10">
      <section class="max-w-3xl pt-2 lg:pt-10">
        <p class="inline-flex items-center gap-2 rounded-full border border-[#d4c4aa] bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.26em] text-[#9f5c24] backdrop-blur">
          <span class="h-2 w-2 rounded-full bg-[#d7561e]" />
          Sistem Monitoring Terpadu
        </p>

        <h1 class="mt-6 max-w-3xl text-4xl font-black leading-tight tracking-[-0.04em] text-[#12263f] sm:text-5xl lg:text-[4.5rem] lg:leading-[0.94]">
          Halaman utama kini menjadi pintu masuk kerja yang lebih jelas.
        </h1>

        <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
          PPM Bayan merangkum perencanaan program, pengawasan realisasi, dan evaluasi anggaran dalam satu sistem yang dapat dibaca cepat oleh tim divisi maupun pengelola pusat.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
          <Link
            :href="authUser ? route('dashboard') : route('login')"
            class="inline-flex items-center justify-center rounded-full bg-[#d7561e] px-6 py-3 text-sm font-semibold text-white shadow-[0_18px_35px_-20px_rgba(215,86,30,0.9)] transition hover:bg-[#bf4918]"
          >
            {{ authUser ? 'Lanjut ke Dashboard' : 'Masuk ke Aplikasi' }}
          </Link>
          <a href="#fitur" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
            Lihat Fitur Utama
          </a>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <article class="rounded-[1.6rem] border border-white/80 bg-white/80 p-5 shadow-[0_24px_55px_-36px_rgba(15,60,115,0.38)] backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Workflow</p>
            <p class="mt-3 text-3xl font-black text-[#0f3c73]">1 alur</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">Perencanaan, realisasi, resume, dan pelaporan dalam struktur data yang sama.</p>
          </article>
          <article class="rounded-[1.6rem] border border-white/80 bg-white/80 p-5 shadow-[0_24px_55px_-36px_rgba(15,60,115,0.38)] backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Kontrol</p>
            <p class="mt-3 text-3xl font-black text-[#0f3c73]">Multi role</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">Akses menyesuaikan superadmin, CDO, dan unit divisi yang melakukan input.</p>
          </article>
          <article class="rounded-[1.6rem] border border-white/80 bg-white/80 p-5 shadow-[0_24px_55px_-36px_rgba(15,60,115,0.38)] backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Output</p>
            <p class="mt-3 text-3xl font-black text-[#0f3c73]">Siap export</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">Ringkasan anggaran dan status kegiatan lebih cepat dibaca untuk evaluasi.</p>
          </article>
        </div>
      </section>

      <section class="relative lg:pl-6">
        <div class="relative overflow-hidden rounded-[2rem] border border-[#dfd6c6] bg-[#12263f] p-6 text-white shadow-[0_30px_90px_-36px_rgba(15,38,63,0.65)] sm:p-8">
          <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-[#d7561e]/25 blur-3xl" />
          <div class="absolute -bottom-12 left-0 h-36 w-36 rounded-full bg-[#f3c97d]/10 blur-3xl" />

          <div class="relative">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#f0c9a6]">Quick Snapshot</p>
                <h2 class="mt-2 text-2xl font-bold leading-tight">Halaman publik untuk orientasi, dashboard tetap untuk operasional.</h2>
              </div>
              <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/10 text-lg font-black text-white ring-1 ring-white/15">PPM</div>
            </div>

            <div class="mt-8 space-y-4">
              <div class="rounded-[1.4rem] border border-white/10 bg-white/8 p-5 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-semibold text-white">Monitoring anggaran lintas divisi</p>
                  <span class="rounded-full bg-[#f2d052] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-[#4f3b00]">Aktif</span>
                </div>
                <p class="mt-3 text-sm leading-7 text-slate-200">Tim dapat melihat status rencana, serapan, dan sisa anggaran tanpa berpindah antar sistem.</p>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-[1.4rem] bg-white p-5 text-slate-900">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Arah halaman</p>
                  <p class="mt-3 text-xl font-black text-[#12263f]">Landing page</p>
                  <p class="mt-2 text-sm leading-6 text-slate-500">Cocok untuk menampilkan identitas sistem dan CTA masuk yang jelas.</p>
                </div>
                <div class="rounded-[1.4rem] bg-[#e8eef6] p-5 text-slate-900">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">Area kerja</p>
                  <p class="mt-3 text-xl font-black text-[#0f3c73]">Halaman login</p>
                  <p class="mt-2 text-sm leading-6 text-slate-600">Dibuat terpisah agar fokus autentikasi tidak bercampur dengan halaman publik.</p>
                </div>
              </div>

              <div id="fitur" class="rounded-[1.5rem] border border-white/10 bg-[#0d2138] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Yang tersedia</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                  <div class="rounded-2xl border border-white/8 bg-white/5 p-4">
                    <p class="text-sm font-semibold">Perencanaan program dan kegiatan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Susun target, biaya, dan relasi pilar secara rapi.</p>
                  </div>
                  <div class="rounded-2xl border border-white/8 bg-white/5 p-4">
                    <p class="text-sm font-semibold">Realisasi dan verifikasi</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Pantau capaian triwulan dan tindak lanjut verifikasi kegiatan.</p>
                  </div>
                  <div class="rounded-2xl border border-white/8 bg-white/5 p-4">
                    <p class="text-sm font-semibold">Resume eksekutif</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Ringkasan divisi, pilar, dan status untuk kebutuhan evaluasi.</p>
                  </div>
                  <div class="rounded-2xl border border-white/8 bg-white/5 p-4">
                    <p class="text-sm font-semibold">Export laporan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Output dokumen siap dibagikan tanpa merapikan manual.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)
</script>