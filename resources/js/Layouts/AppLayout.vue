<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const navCounts = computed(() => page.props.nav_counts ?? {});
const currentUrl = computed(() => page.url);
const component = computed(() => page.component);

// "mis à jour" counter
const minutesSince = ref(0);
onMounted(() => {
    setInterval(() => minutesSince.value++, 60000);
});
const updateLabel = computed(() =>
    minutesSince.value === 0 ? 'à l\'instant' : `il y a ${minutesSince.value} min`
);

function isActive(href) {
    if (href === '/') return currentUrl.value === '/';
    return currentUrl.value.startsWith(href);
}

const pageTitles = {
    'Dashboard/Index':    { title: 'Dashboard',          subtitle: 'Vue d\'ensemble' },
    'Achats/Index':       { title: 'Achats',              subtitle: 'Annonces d\'achat' },
    'Achats/Create':      { title: 'Nouvelle annonce',   subtitle: 'Achat' },
    'Achats/Edit':        { title: 'Modifier',            subtitle: 'Achat' },
    'Achats/Show':        { title: 'Détail',              subtitle: 'Achat' },
    'Locations/Index':    { title: 'Locations',           subtitle: 'Annonces de location' },
    'Locations/Create':   { title: 'Nouvelle location',  subtitle: 'Location' },
    'Locations/Edit':     { title: 'Modifier',            subtitle: 'Location' },
    'Simulations/Index':  { title: 'Simulateur',          subtitle: 'Cashflow & rendements' },
    'Simulations/Create': { title: 'Nouvelle simulation', subtitle: 'Simulateur' },
    'Stats/Index':        { title: 'Statistiques',        subtitle: 'Analyse de marché' },
    'Parametres/Index':   { title: 'Paramètres',           subtitle: 'Données & synchronisation' },
};

const pageActions = {
    'Dashboard/Index':   { href: '/achats/create',      label: '+ Nouvelle annonce' },
    'Achats/Index':      { href: '/achats/create',      label: '+ Nouvelle annonce' },
    'Locations/Index':   { href: '/locations/create',   label: '+ Nouvelle location' },
    'Simulations/Index': { href: '/simulations/create', label: '+ Nouvelle simulation' },
};

const meta   = computed(() => pageTitles[component.value]   ?? { title: '', subtitle: '' });
const action = computed(() => pageActions[component.value]  ?? null);
</script>

<template>
    <div class="flex min-h-screen" style="background: var(--bg-0); color: var(--text)">

        <!-- ─── Sidebar ─── -->
        <aside
            class="w-[220px] flex-shrink-0 fixed top-0 left-0 h-screen flex flex-col z-40"
            style="background: var(--bg-1); border-right: 1px solid var(--border-strong)"
        >
            <!-- Workspace label -->
            <div class="px-4 pt-4 pb-0">
                <span class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--text-4)">Espace de travail</span>
            </div>

            <!-- Logo -->
            <div class="px-4 pt-2 pb-4 flex-shrink-0" style="border-bottom: 1px solid var(--border-strong)">
                <Link href="/" class="flex items-center gap-2.5 no-underline">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">IT</div>
                    <div>
                        <div class="font-semibold text-sm leading-tight" style="color: var(--text)">ImmoTracker</div>
                        <div class="text-[10px]" style="color: var(--text-4)">V0.1 · LOCAL</div>
                    </div>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
                <!-- Dashboard -->
                <Link href="/" class="nav-item flex items-center justify-between px-3 py-2 rounded text-sm transition-colors relative" :class="isActive('/') ? 'nav-active' : 'nav-idle'">
                    <div class="flex items-center gap-2.5">
                        <span v-if="isActive('/')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        Dashboard
                    </div>
                </Link>

                <!-- Achats -->
                <Link href="/achats" class="nav-item flex items-center justify-between px-3 py-2 rounded text-sm transition-colors relative" :class="isActive('/achats') ? 'nav-active' : 'nav-idle'">
                    <div class="flex items-center gap-2.5">
                        <span v-if="isActive('/achats')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Achats
                    </div>
                    <span v-if="navCounts.achats" class="text-[10px] mono px-1.5 py-0.5 rounded" style="background: var(--bg-3); color: var(--text-4)">{{ navCounts.achats }}</span>
                </Link>

                <!-- Locations -->
                <Link href="/locations" class="nav-item flex items-center justify-between px-3 py-2 rounded text-sm transition-colors relative" :class="isActive('/locations') ? 'nav-active' : 'nav-idle'">
                    <div class="flex items-center gap-2.5">
                        <span v-if="isActive('/locations')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                        Locations
                    </div>
                    <span v-if="navCounts.locations" class="text-[10px] mono px-1.5 py-0.5 rounded" style="background: var(--bg-3); color: var(--text-4)">{{ navCounts.locations }}</span>
                </Link>

                <!-- Simulateur -->
                <Link href="/simulations" class="nav-item flex items-center justify-between px-3 py-2 rounded text-sm transition-colors relative" :class="isActive('/simulations') ? 'nav-active' : 'nav-idle'">
                    <div class="flex items-center gap-2.5">
                        <span v-if="isActive('/simulations')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" />
                        </svg>
                        Simulateur
                    </div>
                </Link>

                <!-- Statistiques -->
                <Link href="/stats" class="nav-item flex items-center justify-between px-3 py-2 rounded text-sm transition-colors relative" :class="isActive('/stats') ? 'nav-active' : 'nav-idle'">
                    <div class="flex items-center gap-2.5">
                        <span v-if="isActive('/stats')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Statistiques
                    </div>
                </Link>
            </nav>

            <!-- Bottom: Système -->
            <div class="px-2 py-3 flex-shrink-0" style="border-top: 1px solid var(--border-strong)">
                <div class="px-2 text-[10px] font-semibold uppercase tracking-widest mb-2" style="color: var(--text-4)">Système</div>

                <!-- Paramètres link -->
                <Link href="/parametres" class="nav-item flex items-center gap-2.5 px-3 py-2 rounded text-sm transition-colors relative mb-2" :class="isActive('/parametres') ? 'nav-active' : 'nav-idle'">
                    <span v-if="isActive('/parametres')" class="absolute left-0 top-1.5 bottom-1.5 w-0.5 rounded-full" style="background: var(--blue)"></span>
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Paramètres
                </Link>

                <div class="px-2">
                    <div class="flex items-center gap-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500 flex-shrink-0"></div>
                        <span class="text-xs" style="color: var(--text-3)">Base locale connectée</span>
                    </div>
                    <div class="text-[10px] mt-1" style="color: var(--text-4)">MAJ : {{ updateLabel }}</div>
                </div>
            </div>
        </aside>

        <!-- ─── Main ─── -->
        <div class="flex-1 ml-[220px] min-h-screen flex flex-col overflow-x-hidden">

            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex items-center gap-4 px-6 py-3 flex-shrink-0" style="background: var(--bg-1); border-bottom: 1px solid var(--border-strong); min-height: 60px">
                <!-- Côté gauche : flex-1 min-w-0 pour absorber le débordement -->
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] font-semibold uppercase tracking-widest mb-0.5" style="color: var(--text-4)">Espace de travail</div>
                    <div class="flex items-baseline gap-2 min-w-0">
                        <span class="text-xl font-bold flex-shrink-0" style="color: var(--text)">{{ meta.title }}</span>
                        <span class="text-sm truncate" style="color: var(--text-4)">{{ meta.subtitle }} · mis à jour {{ updateLabel }}</span>
                    </div>
                </div>
                <!-- Côté droit : flex-shrink-0 pour ne jamais réduire -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Search : caché sur petits écrans -->
                    <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm" style="background: var(--bg-3); border: 1px solid var(--border-strong); color: var(--text-4); min-width: 200px">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Rechercher...</span>
                        <span class="ml-auto text-[10px] px-1 rounded" style="background: var(--bg-0)">⌘K</span>
                    </div>
                    <!-- Exporter : caché sur petits écrans -->
                    <button class="hidden md:block px-3 py-1.5 rounded-lg text-sm font-medium" style="background: var(--bg-3); border: 1px solid var(--border-strong); color: var(--text-3)">
                        Exporter
                    </button>
                    <!-- Action button : toujours visible -->
                    <Link
                        v-if="action"
                        :href="action.href"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                        style="background: var(--blue); color: white"
                    >{{ action.label }}</Link>
                </div>
            </header>

            <!-- Flash -->
            <div v-if="flash.success" class="text-sm px-6 py-2.5 flex-shrink-0" style="background: rgba(34,197,94,0.1); border-bottom: 1px solid rgba(34,197,94,0.3); color: #4ade80">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="text-sm px-6 py-2.5 flex-shrink-0" style="background: rgba(239,68,68,0.1); border-bottom: 1px solid rgba(239,68,68,0.3); color: #f87171">
                {{ flash.error }}
            </div>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.nav-idle {
    color: var(--text-3);
}
.nav-idle:hover {
    color: var(--text-2);
    background: rgba(255, 255, 255, 0.04);
}
.nav-active {
    color: var(--blue-2);
    background: rgba(59, 130, 246, 0.1);
}
</style>
