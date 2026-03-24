<template>
  <div class="flex h-screen bg-gray-100 overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0">
      <div class="p-5 border-b border-blue-800">
        <h1 class="text-xl font-bold">PPM Bayan</h1>
        <p class="text-xs text-blue-300 mt-1">Perencanaan &amp; Realisasi</p>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <Link :href="route('dashboard')" class="nav-link" :class="{ 'nav-link-active': isActive('dashboard') }">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Dashboard
        </Link>

        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Perencanaan</div>

        <Link v-if="isSuperadmin" :href="route('program.index')" class="nav-link" :class="{ 'nav-link-active': isActive('program') }">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Program
        </Link>

        <Link :href="route('kegiatan.index')" class="nav-link" :class="{ 'nav-link-active': isActive('kegiatan') }">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          Kegiatan
        </Link>

        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Realisasi</div>

        <Link :href="route('realisasi.index')" class="nav-link" :class="{ 'nav-link-active': isActive('realisasi') }">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Input Realisasi
        </Link>

        <template v-if="isSuperadmin">
          <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Master Data</div>

          <Link :href="route('divisi.index')" class="nav-link" :class="{ 'nav-link-active': isActive('divisi') }">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Divisi
          </Link>

          <Link :href="route('pilar.index')" class="nav-link" :class="{ 'nav-link-active': isActive('pilar') }">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Pilar
          </Link>

          <Link :href="route('periode.index')" class="nav-link" :class="{ 'nav-link-active': isActive('periode') }">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Periode
          </Link>
        </template>

        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Sistem</div>

        <Link :href="route('activity-log.index')" class="nav-link" :class="{ 'nav-link-active': isActive('activity-log') }">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
          Activity Log
        </Link>
      </nav>

      <div class="p-4 border-t border-blue-800">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-sm font-bold">
            {{ auth.user?.name?.[0]?.toUpperCase() }}
          </div>
          <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-blue-300 capitalize">{{ auth.user?.role }}</p>
          </div>
        </div>
        <form @submit.prevent="logout" class="mt-3">
          <button type="submit" class="w-full text-left text-sm text-blue-300 hover:text-white transition-colors">
            Keluar →
          </button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between flex-shrink-0">
        <h2 class="text-lg font-semibold text-gray-800">{{ title }}</h2>
        <div class="flex items-center gap-3">
          <slot name="header-actions" />
        </div>
      </header>

      <main class="flex-1 overflow-auto p-6">
        <slot />
      </main>
    </div>

    <Toast />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'

defineProps({ title: { type: String, default: '' } })

const page = usePage()
const auth = computed(() => page.props.auth)
const isSuperadmin = computed(() => auth.value.user?.is_superadmin)

function isActive(segment) {
  return window.location.pathname.startsWith('/' + segment)
}
function logout() {
  router.post(route('logout'))
}
</script>

