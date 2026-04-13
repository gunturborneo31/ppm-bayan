<template>
  <div class="flex h-screen overflow-hidden" style="background:#eef1f4">
    <!-- Sidebar -->
    <aside
      class="flex flex-col flex-shrink-0 transition-all duration-300 border-r border-gray-200"
      :class="sidebarCollapsed ? 'w-20' : 'w-60'"
      style="background:#ffffff"
    >
      <!-- Logo + Toggle -->
      <div class="px-4 py-4 flex items-start" :class="sidebarCollapsed ? 'justify-center flex-col gap-3' : 'justify-between gap-3'">
        <div class="flex items-center min-w-0" :class="sidebarCollapsed ? 'justify-center' : 'gap-3.5'">
          <img
            src="/logo-bayan.png"
            alt="Logo Bayan"
            class="object-contain flex-shrink-0"
            :class="sidebarCollapsed ? 'w-11 h-11' : 'w-10 h-10'"
          />
          <div v-if="!sidebarCollapsed" class="min-w-0 pt-0.5 leading-tight">
            <p class="ml-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">PPM</p>
            <p class="ml-1 mt-0.5 truncate text-[10px] font-medium text-slate-500">PT Bayan Resources</p>
          </div>
        </div>

        <button
          @click="toggleSidebar"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer"
          :class="sidebarCollapsed ? '' : 'mt-1'"
          :title="sidebarCollapsed ? 'Perbesar sidebar' : 'Perkecil sidebar'"
          :aria-label="sidebarCollapsed ? 'Perbesar sidebar' : 'Perkecil sidebar'"
        >
          <svg v-if="sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M4 5l7 7-7 7" />
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m9 14l-7-7 7-7" />
          </svg>
        </button>
      </div>

      <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
        <Link
          v-if="!isPimpinan"
          :href="route('dashboard')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('dashboard') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          <span v-if="!sidebarCollapsed">Dashboard</span>
        </Link>

        <Link
          v-if="isSuperadmin"
          :href="route('program.index')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('program') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Program' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span v-if="!sidebarCollapsed">Program</span>
        </Link>

        <Link
          v-if="!isPimpinan"
          :href="route('perencanaan.index')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('perencanaan') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Perencanaan Kegiatan' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H11a2 2 0 01-2-2V5z"/>
          </svg>
          <span v-if="!sidebarCollapsed" class="min-w-0 flex-1">Perencanaan Kegiatan</span>
          <span
            v-if="pendingVerificationCount > 0 && !sidebarCollapsed"
            class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-semibold text-white"
          >
            {{ pendingVerificationCount }}
          </span>
          <span
            v-else-if="pendingVerificationCount > 0 && sidebarCollapsed"
            class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-orange-500 ring-2 ring-white"
          />
        </Link>

        <Link
          v-if="!isPimpinan"
          :href="route('realisasi.index')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('realisasi') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Realisasi Kegiatan' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <span v-if="!sidebarCollapsed">Realisasi Kegiatan</span>
        </Link>

        <div v-if="sidebarCollapsed" class="relative">
          <Link
            :href="route('resume.pilar.index')"
            class="nav-link justify-center"
            :class="{ 'nav-link-active': isResumeActive }"
            title="Resume"
          >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </Link>
        </div>

        <div v-else class="space-y-1">
          <button
            type="button"
            class="nav-link w-full justify-between"
            :class="{ 'nav-link-active': isResumeActive }"
            @click="resumeMenuOpen = !resumeMenuOpen"
          >
            <span class="inline-flex items-center">
              <svg class="w-4 h-4 flex-shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Resume
            </span>
            <svg class="w-4 h-4 transition-transform" :class="resumeMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div v-show="resumeMenuOpen" class="space-y-1 pl-6">
            <Link :href="route('resume.pilar.index')" class="nav-link" :class="{ 'nav-link-active': isActivePath('/resume/pilar') }">Resume Pilar</Link>
            <Link :href="route('resume.program.index')" class="nav-link" :class="{ 'nav-link-active': isActivePath('/resume/program') }">Resume Program</Link>
            <!-- <Link :href="route('resume.divisi.index')" class="nav-link" :class="{ 'nav-link-active': isActivePath('/resume/divisi') }">Resume Divisi</Link> -->
            <Link :href="route('resume.user.index')" class="nav-link" :class="{ 'nav-link-active': isActivePath('/resume/user') }">Resume User</Link>
          </div>
        </div>

        <template v-if="isSuperadmin">
          <div v-if="!sidebarCollapsed" class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Master Data</div>

          <Link
            :href="route('divisi.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('divisi') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Divisi' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span v-if="!sidebarCollapsed">Divisi</span>
          </Link>

          <Link
            :href="route('pilar.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('pilar') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Pilar' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span v-if="!sidebarCollapsed">Pilar</span>
          </Link>

          <Link
            :href="route('periode.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('periode') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Periode' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span v-if="!sidebarCollapsed">Periode</span>
          </Link>

          <Link
            :href="route('lokasi.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('lokasi') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Lokasi' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span v-if="!sidebarCollapsed">Lokasi</span>
          </Link>

          <Link
            :href="route('user.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('user') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'User' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5V9a2 2 0 00-2-2h-3m-4 13H8m5 0v-5a3 3 0 00-6 0v5m6 0H7m-2 0H2V9a2 2 0 012-2h3m10 0V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H9"/>
            </svg>
            <span v-if="!sidebarCollapsed">User</span>
          </Link>

          <Link
            :href="route('pengumuman.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('pengumuman') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Pengumuman' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span v-if="!sidebarCollapsed">Pengumuman</span>
          </Link>

          <Link
            :href="route('regulasi.index')"
            class="nav-link"
            :class="[{ 'nav-link-active': isActive('regulasi') }, sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? 'Dasar Hukum Pelaksanaan' : ''"
          >
            <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.343 2 8v11c0-1.657 2.462-3 5.5-3 1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.343 5.5 3v11c0-1.657-2.462-3-5.5-3-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span v-if="!sidebarCollapsed">Dasar Hukum</span>
          </Link>
        </template>

        <div v-if="!sidebarCollapsed && !isPimpinan" class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Sistem</div>

        <Link
          v-if="isSuperadmin"
          :href="route('settings.index')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('settings') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Pengaturan' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span v-if="!sidebarCollapsed">Pengaturan</span>
        </Link>

        <Link
          v-if="!isPimpinan"
          :href="route('activity-log.index')"
          class="nav-link"
          :class="[{ 'nav-link-active': isActive('activity-log') }, sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Activity Log' : ''"
        >
          <svg class="w-4 h-4 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
          <span v-if="!sidebarCollapsed">Activity Log</span>
        </Link>
      </nav>

      <div class="px-4 py-4 border-t border-gray-200">
        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
          <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold text-white flex-shrink-0" style="background:#d7561e">
            {{ auth.user?.name?.[0]?.toUpperCase() }}
          </div>
          <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate leading-none">{{ auth.user?.name }}</p>
            <p class="text-xs capitalize mt-0.5 text-gray-500">{{ auth.user?.role }}</p>
          </div>
        </div>
        <form @submit.prevent="logout" class="mt-3" :class="sidebarCollapsed ? 'text-center' : ''">
          <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 transition-colors cursor-pointer">
            <span v-if="sidebarCollapsed">⎋</span>
            <span v-else>Keluar →</span>
          </button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="bg-white/90 border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0 backdrop-blur-sm">
        <h2 class="text-base font-semibold text-gray-800">{{ title }}</h2>
        <div class="flex items-center gap-3">
          <div class="relative" v-if="notifications.length">
            <button
              type="button"
              class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50"
              @click="notificationsOpen = !notificationsOpen"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="unreadNotificationCount > 0"
                class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-orange-500 px-1.5 py-0.5 text-[11px] font-semibold text-white"
              >
                {{ unreadNotificationCount }}
              </span>
            </button>

            <div
              v-if="notificationsOpen"
              class="absolute right-0 top-12 z-30 w-[360px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
            >
              <div class="border-b border-gray-100 px-4 py-3">
                <p class="text-sm font-semibold text-gray-800">Notifikasi</p>
                <p class="text-xs text-gray-500">Komentar kegiatan terbaru</p>
              </div>
              <div class="max-h-[420px] overflow-auto">
                <a
                  v-for="item in notifications"
                  :key="item.id"
                  :href="item.route || '#'"
                  class="block border-b border-gray-100 px-4 py-3 transition hover:bg-slate-50"
                  :class="!item.read_at ? 'bg-orange-50/60' : 'bg-white'"
                  @click="notificationsOpen = false"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-gray-800">{{ item.title }}</p>
                      <p class="mt-1 text-xs leading-5 text-gray-600">{{ item.message }}</p>
                    </div>
                    <span v-if="!item.read_at" class="mt-1 h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                  </div>
                  <p class="mt-2 text-[11px] text-gray-400">{{ formatNotificationDate(item.created_at) }}</p>
                </a>
              </div>
            </div>
          </div>
          <slot name="header-actions" />
        </div>
      </header>

      <main class="flex-1 overflow-auto p-6" style="background:#eef1f4">
        <slot />
      </main>
    </div>

    <Toast />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'

defineProps({ title: { type: String, default: '' } })

const page = usePage()
const auth = computed(() => page.props.auth)
const isSuperadmin = computed(() => auth.value.user?.is_superadmin)
const isPimpinan = computed(() => auth.value.user?.is_pimpinan || auth.value.user?.role === 'pimpinan')
const pendingVerificationCount = computed(() => Number(page.props.pending_verification_count || 0))
const notifications = computed(() => page.props.notifications || [])
const unreadNotificationCount = computed(() => Number(page.props.unread_notification_count || 0))
const isResumeActive = computed(() => window.location.pathname.startsWith('/resume'))

const sidebarCollapsed = ref(localStorage.getItem('sidebar-collapsed') === '1')
const resumeMenuOpen = ref(window.location.pathname.startsWith('/resume'))
const notificationsOpen = ref(false)

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value ? '1' : '0')
}

function isActive(segment) {
  return window.location.pathname.startsWith('/' + segment)
}

function isActivePath(path) {
  return window.location.pathname.startsWith(path)
}

function logout() {
  router.post(route('logout'))
}

function formatNotificationDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
