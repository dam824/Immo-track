<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: Object,
    recent_achats: Array,
    recent_locations: Array,
});

// ─── Activité récente ───────────────────────────────────────────────
const activities = computed(() => {
    const items = [];
    for (const l of props.recent_achats ?? []) {
        items.push({
            label: 'Nouvelle annonce ajoutée',
            detail: [l.titre, l.ville, l.prix_achat ? l.prix_achat.toLocaleString('fr-FR') + ' €' : null].filter(Boolean).join(' · '),
            color: '#3b82f6',
            created_at: l.created_at,
        });
    }
    for (const l of props.recent_locations ?? []) {
        items.push({
            label: 'Nouvelle location suivie',
            detail: [l.titre, l.ville, l.loyer_mensuel ? l.loyer_mensuel + ' €/mois' : null].filter(Boolean).join(' · '),
            color: '#22c55e',
            created_at: l.created_at,
        });
    }
    return items
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 4);
});

function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return 'à l\'instant';
    if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `il y a ${Math.floor(diff / 3600)} h`;
    return `il y a ${Math.floor(diff / 86400)} j`;
}

// ─── Listing card thumbnail gradients ───────────────────────────────
const GRADIENTS = [
    'linear-gradient(135deg,#1d4ed8,#3b82f6)',
    'linear-gradient(135deg,#c2410c,#fb923c)',
    'linear-gradient(135deg,#15803d,#22c55e)',
    'linear-gradient(135deg,#7e22ce,#a855f7)',
    'linear-gradient(135deg,#9d174d,#ec4899)',
    'linear-gradient(135deg,#0e7490,#22d3ee)',
];

function cardGradient(id) {
    return GRADIENTS[(id ?? 0) % GRADIENTS.length];
}

// ─── Source badges ───────────────────────────────────────────────────
const SRC_BG   = { leboncoin:'rgba(251,146,60,.15)', seloger:'rgba(248,113,113,.15)', jinka:'rgba(167,139,250,.15)', pap:'rgba(52,211,153,.15)' };
const SRC_TEXT = { leboncoin:'#fb923c', seloger:'#f87171', jinka:'#a78bfa', pap:'#34d399' };
</script>

<template>
    <div>
        <!-- KPI cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            <StatCard label="Achats suivis"    :value="stats?.total_achats     ?? 0" color="blue"   />
            <StatCard label="Locations suivies" :value="stats?.total_locations  ?? 0" color="green"  />
            <StatCard label="Simulations"       :value="stats?.total_simulations ?? 0" color="purple" />
            <StatCard label="Rendement moyen"   :value="stats?.rendement_moyen"   unit="%" color="yellow" />
            <StatCard label="Meilleur cashflow" :value="stats?.meilleur_cashflow" unit="€/mois" color="green" />
        </div>

        <!-- Activité récente -->
        <div class="rounded-lg mb-6" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <div class="flex items-center justify-between px-5 py-3" style="border-bottom: 1px solid var(--border-strong)">
                <div>
                    <div class="font-semibold text-sm" style="color: var(--text)">Activité récente</div>
                    <div class="text-xs" style="color: var(--text-4)">3 derniers jours</div>
                </div>
                <Link href="/achats" class="text-xs px-2.5 py-1 rounded" style="background: var(--bg-3); color: var(--text-3)">Voir tout</Link>
            </div>

            <div v-if="activities.length" class="divide-y" style="border-color: var(--border-soft)">
                <div
                    v-for="(a, i) in activities"
                    :key="i"
                    class="flex items-center justify-between px-5 py-3"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full flex-shrink-0" :style="`background: ${a.color}`"></div>
                        <div>
                            <span class="text-sm font-medium" style="color: var(--text-2)">{{ a.label }}</span>
                            <span class="text-sm ml-2" style="color: var(--text-4)">{{ a.detail }}</span>
                        </div>
                    </div>
                    <span class="text-xs flex-shrink-0 ml-4" style="color: var(--text-4)">{{ timeAgo(a.created_at) }}</span>
                </div>
            </div>
            <div v-else class="px-5 py-4 text-sm" style="color: var(--text-4)">
                Aucune activité récente. Ajoutez vos premières annonces.
            </div>
        </div>

        <!-- Two-column listing -->
        <div class="grid md:grid-cols-2 gap-5">

            <!-- Derniers achats -->
            <div class="rounded-lg" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <div class="flex items-center justify-between px-5 py-3" style="border-bottom: 1px solid var(--border-strong)">
                    <div>
                        <div class="font-semibold text-sm" style="color: var(--text)">Derniers achats suivis</div>
                        <div class="text-xs" style="color: var(--text-4)">{{ stats?.total_achats ?? 0 }} annonce{{ (stats?.total_achats ?? 0) > 1 ? 's' : '' }}</div>
                    </div>
                    <Link href="/achats" class="text-xs" style="color: var(--text-4)">Voir tout →</Link>
                </div>

                <div v-if="recent_achats?.length" class="divide-y" style="border-color: var(--border-soft)">
                    <Link
                        v-for="l in recent_achats"
                        :key="l.id"
                        :href="`/achats/${l.id}`"
                        class="flex items-center gap-3 px-5 py-3 transition-colors listing-row block"
                    >
                        <!-- Thumbnail / gradient icon -->
                        <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center overflow-hidden" :style="l.thumbnail_url ? '' : `background: ${cardGradient(l.id)}`">
                            <img v-if="l.thumbnail_url" :src="l.thumbnail_url" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                            <svg v-else class="w-5 h-5 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium truncate" style="color: var(--text)">{{ l.titre ?? 'Sans titre' }}</span>
                                <span
                                    v-if="l.source"
                                    class="text-[10px] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wide flex-shrink-0"
                                    :style="`background: ${SRC_BG[l.source] ?? 'rgba(107,114,128,.15)'}; color: ${SRC_TEXT[l.source] ?? '#9ca3af'}`"
                                >{{ l.source }}</span>
                            </div>
                            <div class="text-xs mt-0.5" style="color: var(--text-4)">
                                {{ [l.ville, l.superficie ? l.superficie + ' m²' : null, l.nb_pieces ? l.nb_pieces + 'p' : null].filter(Boolean).join(' · ') }}
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="text-right flex-shrink-0">
                            <div class="mono text-sm font-bold" style="color: var(--blue-2)">
                                {{ l.prix_achat ? l.prix_achat.toLocaleString('fr-FR') + ' €' : '—' }}
                            </div>
                            <div class="mono text-[11px]" style="color: var(--text-4)">
                                {{ l.prix_m2 ? l.prix_m2.toLocaleString('fr-FR') + ' €/m²' : '' }}
                            </div>
                        </div>
                    </Link>
                </div>
                <div v-else class="px-5 py-6 text-sm text-center" style="color: var(--text-4)">
                    Aucun achat enregistré.
                </div>
            </div>

            <!-- Dernières locations -->
            <div class="rounded-lg" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <div class="flex items-center justify-between px-5 py-3" style="border-bottom: 1px solid var(--border-strong)">
                    <div>
                        <div class="font-semibold text-sm" style="color: var(--text)">Dernières locations suivies</div>
                        <div class="text-xs" style="color: var(--text-4)">{{ stats?.total_locations ?? 0 }} annonce{{ (stats?.total_locations ?? 0) > 1 ? 's' : '' }}</div>
                    </div>
                    <Link href="/locations" class="text-xs" style="color: var(--text-4)">Voir tout →</Link>
                </div>

                <div v-if="recent_locations?.length" class="divide-y" style="border-color: var(--border-soft)">
                    <div
                        v-for="l in recent_locations"
                        :key="l.id"
                        class="flex items-center gap-3 px-5 py-3 listing-row"
                    >
                        <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center overflow-hidden" :style="l.thumbnail_url ? '' : `background: ${cardGradient(l.id)}`">
                            <img v-if="l.thumbnail_url" :src="l.thumbnail_url" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                            <svg v-else class="w-5 h-5 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium truncate" style="color: var(--text)">{{ l.titre ?? 'Sans titre' }}</span>
                                <span
                                    v-if="l.source"
                                    class="text-[10px] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wide flex-shrink-0"
                                    :style="`background: ${SRC_BG[l.source] ?? 'rgba(107,114,128,.15)'}; color: ${SRC_TEXT[l.source] ?? '#9ca3af'}`"
                                >{{ l.source }}</span>
                                <span v-if="l.meuble" class="text-[10px] px-1.5 py-0.5 rounded font-semibold uppercase" style="background: rgba(168,85,247,.15); color: #c084fc">Meublé</span>
                            </div>
                            <div class="text-xs mt-0.5" style="color: var(--text-4)">
                                {{ [l.ville, l.superficie ? l.superficie + ' m²' : null, l.nb_pieces ? l.nb_pieces + 'p' : null].filter(Boolean).join(' · ') }}
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="mono text-sm font-bold" style="color: var(--green-2)">
                                {{ l.loyer_mensuel ? l.loyer_mensuel + ' €' : '—' }}
                                <span class="text-xs font-normal" style="color: var(--text-4)">/mois</span>
                            </div>
                            <div class="mono text-[11px]" style="color: var(--text-4)">
                                {{ l.loyer_m2 ? l.loyer_m2 + ' €/m²' : '' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="px-5 py-6 text-sm text-center" style="color: var(--text-4)">
                    Aucune location enregistrée.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.listing-row:hover {
    background: rgba(255, 255, 255, 0.03);
}
</style>
