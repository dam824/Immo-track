<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({ listings: Array });

function deleteItem(id) {
    if (!confirm('Supprimer cette location ?')) return;
    router.delete(`/locations/${id}`);
}

const srcBg = { leboncoin: 'rgba(251,146,60,0.15)', seloger: 'rgba(248,113,113,0.15)', jinka: 'rgba(167,139,250,0.15)', pap: 'rgba(52,211,153,0.15)' };
const srcText = { leboncoin: '#fb923c', seloger: '#f87171', jinka: '#a78bfa', pap: '#34d399' };
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--text)">Locations</h1>
                <p class="text-sm mt-0.5" style="color: var(--text-4)">{{ listings?.length ?? 0 }} annonce{{ (listings?.length ?? 0) > 1 ? 's' : '' }} enregistrée{{ (listings?.length ?? 0) > 1 ? 's' : '' }}</p>
            </div>
            <Link href="/locations/create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors" style="background: var(--green); color: white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nouvelle location
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="!listings?.length" class="py-16 text-center rounded-lg" style="background: var(--bg-2); border: 1px dashed var(--border-strong)">
            <p class="text-sm" style="color: var(--text-4)">Aucune location enregistrée.</p>
        </div>

        <!-- Table -->
        <div v-else class="rounded-lg overflow-hidden" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-sm">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-strong)">
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Annonce</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Ville</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Surface</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Loyer</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium hidden md:table-cell" style="color: var(--text-4)">€/m²</th>
                        <th class="px-4 py-3 text-center text-xs uppercase tracking-wide font-medium hidden sm:table-cell" style="color: var(--text-4)">Meublé</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="l in listings"
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
                                    <div class="font-medium truncate" style="color: var(--text)">{{ l.titre ?? 'Sans titre' }}</div>
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
                        <td class="px-4 py-3 text-right mono font-semibold" style="color: var(--green-2)">
                            {{ l.loyer_mensuel ? `${l.loyer_mensuel} €/mois` : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right mono text-xs hidden md:table-cell" style="color: var(--text-3)">
                            {{ l.loyer_m2 ? `${l.loyer_m2} €` : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span v-if="l.meuble" class="text-xs px-2 py-0.5 rounded-full" style="background: rgba(168,85,247,0.15); color: #c084fc">Oui</span>
                            <span v-else style="color: var(--text-4)">—</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Link :href="`/locations/${l.id}/edit`" class="text-xs px-2 py-1 rounded transition-colors" style="color: #facc15; background: rgba(234,179,8,0.12)">Éditer</Link>
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
