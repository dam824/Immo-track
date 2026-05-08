<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({ listings: Array });

const filterStatut = ref('');

const filtered = computed(() => {
    if (!filterStatut.value) return props.listings ?? [];
    return (props.listings ?? []).filter(l => l.statut === filterStatut.value);
});

const statuts = computed(() => {
    const set = new Set((props.listings ?? []).map(l => l.statut).filter(Boolean));
    return [...set];
});

function deleteItem(id) {
    if (!confirm('Supprimer cette annonce ?')) return;
    router.delete(`/achats/${id}`);
}

const dpeBg = { A: '#14532d', B: '#365314', C: '#713f12', D: '#7c2d12', E: '#7f1d1d', F: '#4c0519', G: '#1c1917' };
const dpeText = { A: '#4ade80', B: '#a3e635', C: '#fde047', D: '#fb923c', E: '#f87171', F: '#fb7185', G: '#a8a29e' };

const srcBg = { leboncoin: 'rgba(251,146,60,0.15)', seloger: 'rgba(248,113,113,0.15)', jinka: 'rgba(167,139,250,0.15)', pap: 'rgba(52,211,153,0.15)' };
const srcText = { leboncoin: '#fb923c', seloger: '#f87171', jinka: '#a78bfa', pap: '#34d399' };

const statutBg = { actif: 'rgba(59,130,246,0.15)', a_visiter: 'rgba(234,179,8,0.15)', visite: 'rgba(168,85,247,0.15)', offre_faite: 'rgba(34,197,94,0.15)', archive: 'rgba(107,114,128,0.15)', elimine: 'rgba(239,68,68,0.15)' };
const statutText = { actif: '#60a5fa', a_visiter: '#facc15', visite: '#c084fc', offre_faite: '#4ade80', archive: '#9ca3af', elimine: '#f87171' };
const statutLabel = { actif: 'Actif', a_visiter: 'À visiter', visite: 'Visité', offre_faite: 'Offre faite', archive: 'Archivé', elimine: 'Éliminé' };
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--text)">Acquisitions</h1>
                <p class="text-sm mt-0.5" style="color: var(--text-4)">{{ listings?.length ?? 0 }} annonce{{ (listings?.length ?? 0) > 1 ? 's' : '' }} enregistrée{{ (listings?.length ?? 0) > 1 ? 's' : '' }}</p>
            </div>
            <Link href="/achats/create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors" style="background: var(--blue); color: white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nouvelle annonce
            </Link>
        </div>

        <!-- Filter chips -->
        <div v-if="statuts.length" class="flex flex-wrap gap-2 mb-4">
            <button
                @click="filterStatut = ''"
                class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                :style="!filterStatut ? 'background: var(--blue); color: white' : 'background: var(--bg-3); color: var(--text-3)'"
            >Tous ({{ listings?.length ?? 0 }})</button>
            <button
                v-for="s in statuts"
                :key="s"
                @click="filterStatut = filterStatut === s ? '' : s"
                class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                :style="filterStatut === s
                    ? `background: ${statutText[s] ?? '#6b7280'}33; color: ${statutText[s] ?? '#9ca3af'}; outline: 1px solid ${statutText[s] ?? '#6b7280'}55`
                    : 'background: var(--bg-3); color: var(--text-3)'"
            >{{ statutLabel[s] ?? s }}</button>
        </div>

        <!-- Empty state -->
        <div v-if="!filtered.length" class="py-16 text-center rounded-lg" style="background: var(--bg-2); border: 1px dashed var(--border-strong)">
            <p class="text-sm" style="color: var(--text-4)">{{ listings?.length ? 'Aucune annonce pour ce filtre.' : 'Aucune annonce d\'achat enregistrée.' }}</p>
        </div>

        <!-- Table -->
        <div v-else class="rounded-lg overflow-hidden" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-strong)">
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Annonce</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Ville</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Surface</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Prix</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium hidden md:table-cell" style="color: var(--text-4)">€/m²</th>
                        <th class="px-4 py-3 text-center text-xs uppercase tracking-wide font-medium hidden md:table-cell" style="color: var(--text-4)">DPE</th>
                        <th class="px-4 py-3 text-center text-xs uppercase tracking-wide font-medium hidden sm:table-cell" style="color: var(--text-4)">Statut</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="l in filtered"
                        :key="l.id"
                        class="transition-colors row-hover"
                        style="border-bottom: 1px solid var(--border-soft)"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-9 rounded overflow-hidden flex-shrink-0" style="background: var(--bg-3)">
                                    <img v-if="l.thumbnail_url" :src="l.thumbnail_url" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                                </div>
                                <div class="min-w-0">
                                    <Link :href="`/achats/${l.id}`" class="font-medium hover:underline truncate block" style="color: var(--text)">
                                        {{ l.titre ?? 'Sans titre' }}
                                    </Link>
                                    <span
                                        v-if="l.source"
                                        class="text-xs px-1.5 py-0.5 rounded mt-0.5 inline-block"
                                        :style="`background: ${srcBg[l.source] ?? 'rgba(107,114,128,0.15)'}; color: ${srcText[l.source] ?? '#9ca3af'}`"
                                    >{{ l.source }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3" style="color: var(--text-3)">{{ l.ville ?? '—' }}</td>
                        <td class="px-4 py-3 text-right mono" style="color: var(--text-3)">{{ l.superficie ? `${l.superficie}m²` : '—' }}</td>
                        <td class="px-4 py-3 text-right mono font-semibold" style="color: var(--blue-2)">
                            {{ l.prix_achat ? l.prix_achat.toLocaleString('fr-FR') + ' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right mono text-xs hidden md:table-cell" style="color: var(--text-3)">
                            {{ l.prix_m2 ? l.prix_m2.toLocaleString('fr-FR') + ' €' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell">
                            <span
                                v-if="l.dpe"
                                class="inline-flex items-center justify-center w-6 h-6 rounded text-xs font-bold"
                                :style="`background: ${dpeBg[l.dpe] ?? '#1f2937'}; color: ${dpeText[l.dpe] ?? '#9ca3af'}`"
                            >{{ l.dpe }}</span>
                            <span v-else style="color: var(--text-4)">—</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full"
                                :style="`background: ${statutBg[l.statut] ?? 'rgba(107,114,128,0.15)'}; color: ${statutText[l.statut] ?? '#9ca3af'}`"
                            >{{ statutLabel[l.statut] ?? l.statut }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Link :href="`/simulations/create?achat_id=${l.id}`" class="text-xs px-2 py-1 rounded transition-colors" style="color: var(--violet-2); background: rgba(168,85,247,0.12)">Simuler</Link>
                                <Link :href="`/achats/${l.id}/edit`" class="text-xs px-2 py-1 rounded transition-colors" style="color: #facc15; background: rgba(234,179,8,0.12)">Éditer</Link>
                                <button @click="deleteItem(l.id)" class="text-xs px-2 py-1 rounded transition-colors" style="color: #f87171; background: rgba(239,68,68,0.12)">Suppr.</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.row-hover:hover {
    background: rgba(255, 255, 255, 0.03);
}
</style>
