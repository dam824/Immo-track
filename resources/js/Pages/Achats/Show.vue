<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    listing: Object,
    simulations: Array,
    locations_meme_ville: Array,
});

function deleteListing() {
    if (!confirm('Supprimer cette annonce et toutes ses simulations ?')) return;
    router.delete(`/achats/${props.listing.id}`);
}

const dvf = ref(null);
const dvfLoading = ref(false);

onMounted(async () => {
    if (!props.listing.ville) return;
    dvfLoading.value = true;
    try {
        const params = { ville: props.listing.ville };
        if (props.listing.superficie) params.superficie = props.listing.superficie;
        if (props.listing.prix_achat) params.prix_bien = props.listing.prix_achat;
        const { data } = await axios.get('/api/dvf/marche', { params });
        dvf.value = data;
    } catch {
        dvf.value = { data_disponible: false, error: 'Erreur réseau' };
    } finally {
        dvfLoading.value = false;
    }
});

function fmtDate(d) {
    if (!d) return '—';
    const str = typeof d === 'string' ? d.substring(0, 10) : `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const [y, m, day] = str.split('-');
    return `${day}/${m}/${y}`;
}

const statutBg = { actif: 'rgba(59,130,246,0.15)', a_visiter: 'rgba(234,179,8,0.15)', visite: 'rgba(168,85,247,0.15)', offre_faite: 'rgba(34,197,94,0.15)', archive: 'rgba(107,114,128,0.15)', elimine: 'rgba(239,68,68,0.15)' };
const statutText = { actif: '#60a5fa', a_visiter: '#facc15', visite: '#c084fc', offre_faite: '#4ade80', archive: '#9ca3af', elimine: '#f87171' };
const statutLabel = { actif: 'Actif', a_visiter: 'À visiter', visite: 'Visité', offre_faite: 'Offre faite', archive: 'Archivé', elimine: 'Éliminé' };
const dpeBg = { A: '#14532d', B: '#365314', C: '#713f12', D: '#7c2d12', E: '#7f1d1d', F: '#4c0519', G: '#1c1917' };
const dpeText = { A: '#4ade80', B: '#a3e635', C: '#fde047', D: '#fb923c', E: '#f87171', F: '#fb7185', G: '#a8a29e' };
</script>

<template>
    <div>
        <div class="flex items-center gap-3 mb-6">
            <Link href="/achats" class="text-sm" style="color: var(--text-4)">← Acquisitions</Link>
            <h1 class="text-2xl font-bold truncate" style="color: var(--text)">{{ listing.titre ?? 'Annonce sans titre' }}</h1>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main info -->
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-lg overflow-hidden" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <img v-if="listing.thumbnail_url" :src="listing.thumbnail_url" class="w-full h-52 object-cover" @error="$event.target.style.display='none'" />
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-5 flex-wrap gap-3">
                            <div>
                                <div class="mono text-3xl font-black" style="color: var(--blue-2)">
                                    {{ listing.prix_achat ? listing.prix_achat.toLocaleString('fr-FR') + ' €' : '—' }}
                                </div>
                                <div class="text-sm mt-0.5" style="color: var(--text-4)">{{ listing.prix_m2 ? listing.prix_m2.toLocaleString('fr-FR') + ' €/m²' : '' }}</div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <Link :href="`/simulations/create?achat_id=${listing.id}`" class="px-3 py-1.5 rounded text-sm font-medium transition-colors" style="background: var(--violet); color: white">Simuler</Link>
                                <Link :href="`/achats/${listing.id}/edit`" class="px-3 py-1.5 rounded text-sm font-medium transition-colors" style="background: rgba(234,179,8,0.15); color: #facc15">Éditer</Link>
                                <button @click="deleteListing" class="px-3 py-1.5 rounded text-sm font-medium transition-colors" style="background: rgba(239,68,68,0.15); color: #f87171">Supprimer</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Ville</span><div class="font-medium" style="color: var(--text)">{{ listing.ville ?? '—' }}</div></div>
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Surface</span><div class="mono font-medium" style="color: var(--text)">{{ listing.superficie ? listing.superficie + ' m²' : '—' }}</div></div>
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Pièces</span><div class="font-medium" style="color: var(--text)">{{ listing.nb_pieces ?? '—' }}</div></div>
                            <div>
                                <span class="text-xs block mb-0.5" style="color: var(--text-4)">DPE</span>
                                <span v-if="listing.dpe" class="inline-flex items-center justify-center w-6 h-6 rounded text-xs font-bold" :style="`background: ${dpeBg[listing.dpe] ?? '#1f2937'}; color: ${dpeText[listing.dpe] ?? '#9ca3af'}`">{{ listing.dpe }}</span>
                                <span v-else style="color: var(--text-3)">—</span>
                            </div>
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Copro</span><div class="mono" style="color: var(--text-3)">{{ listing.charges_copro ? listing.charges_copro + ' €/mois' : '—' }}</div></div>
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Taxe foncière</span><div class="mono" style="color: var(--text-3)">{{ listing.taxe_fonciere ? listing.taxe_fonciere + ' €/an' : '—' }}</div></div>
                            <div><span class="text-xs block mb-0.5" style="color: var(--text-4)">Travaux</span><div class="mono" style="color: var(--text-3)">{{ listing.travaux_estimes ? listing.travaux_estimes.toLocaleString('fr-FR') + ' €' : '—' }}</div></div>
                            <div>
                                <span class="text-xs block mb-0.5" style="color: var(--text-4)">Statut</span>
                                <span class="text-xs px-2 py-0.5 rounded-full" :style="`background: ${statutBg[listing.statut] ?? 'rgba(107,114,128,0.15)'}; color: ${statutText[listing.statut] ?? '#9ca3af'}`">
                                    {{ statutLabel[listing.statut] ?? listing.statut }}
                                </span>
                            </div>
                            <div v-if="listing.charges_annuelles_energie"><span class="text-xs block mb-0.5" style="color: var(--text-4)">Énergie</span><div class="mono" style="color: var(--text-3)">{{ listing.charges_annuelles_energie.toLocaleString('fr-FR') }} €/an</div></div>
                        </div>

                        <!-- Caractéristiques / Amenités -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span v-if="listing.meuble"    class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: rgba(59,130,246,0.15); color: #60a5fa">🛋 Meublé</span>
                            <span v-if="listing.cave"      class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Cave</span>
                            <span v-if="listing.parking"   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Parking</span>
                            <span v-if="listing.balcon"    class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Balcon / Terrasse</span>
                            <span v-if="listing.ascenseur" class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Ascenseur</span>
                            <span v-if="listing.gardien"   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Gardien</span>
                            <span v-if="listing.digicode"  class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full" style="background: var(--bg-3); color: var(--text-3)">Digicode</span>
                        </div>

                        <div v-if="listing.notes" class="mt-4 p-3 rounded text-sm" style="background: var(--bg-3); color: var(--text-3)">{{ listing.notes }}</div>
                        <a v-if="listing.url" :href="listing.url" target="_blank" rel="noopener" class="mt-3 inline-block text-xs hover:underline" style="color: var(--blue-2)">Voir l'annonce originale →</a>
                    </div>
                </div>

                <!-- Simulations -->
                <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-sm font-semibold" style="color: var(--text-2)">Simulations ({{ simulations?.length ?? 0 }})</h2>
                        <Link :href="`/simulations/create?achat_id=${listing.id}`" class="text-xs" style="color: var(--blue-2)">+ Nouvelle simulation</Link>
                    </div>
                    <p v-if="!simulations?.length" class="text-sm" style="color: var(--text-4)">Aucune simulation pour ce bien.</p>
                    <div class="space-y-2">
                        <div v-for="s in simulations" :key="s.id" class="flex items-center justify-between p-3 rounded gap-3" style="background: var(--bg-3)">
                            <div>
                                <div class="text-sm font-medium" style="color: var(--text)">{{ s.titre ?? 'Simulation' }}</div>
                                <div class="text-xs mt-0.5" style="color: var(--text-4)">{{ s.taux_interet }}% · {{ s.duree_ans }} ans · {{ s.montant_emprunt?.toLocaleString('fr-FR') }} €</div>
                            </div>
                            <span
                                class="mono text-sm font-bold px-2 py-0.5 rounded flex-shrink-0"
                                :style="Number(s.cashflow_mensuel) >= 0
                                    ? 'background: rgba(34,197,94,0.12); color: #4ade80'
                                    : 'background: rgba(239,68,68,0.12); color: #f87171'"
                            >
                                {{ Number(s.cashflow_mensuel) >= 0 ? '+' : '' }}{{ s.cashflow_mensuel?.toFixed(0) }} €/mois
                            </span>
                        </div>
                    </div>
                </div>

                <!-- DVF Marché local -->
                <div v-if="listing.ville" class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold" style="color: var(--text-2)">Marché local DVF</h2>
                        <span class="text-xs" style="color: var(--text-4)">données notariales officielles</span>
                    </div>

                    <!-- Loading -->
                    <div v-if="dvfLoading" class="text-sm py-2" style="color: var(--text-4)">Chargement du marché local...</div>

                    <!-- No data -->
                    <div v-else-if="!dvf || !dvf.data_disponible" class="py-2">
                        <p class="text-sm" style="color: var(--text-4)">
                            {{ dvf?.error ?? 'Données non disponibles.' }}
                        </p>
                        <p v-if="dvf?.nb_transactions === 0 || !dvf?.nb_transactions" class="text-xs mt-1 font-mono" style="color: var(--text-4)">
                            → Importez les données : <code style="color: var(--yellow)">php artisan dvf:import --dept=93,94,95</code>
                        </p>
                    </div>

                    <!-- Data available -->
                    <template v-else>
                        <!-- Header info -->
                        <div class="text-xs" style="color: var(--text-4)">
                            {{ dvf.nb_transactions }} ventes · appartements
                            <span v-if="dvf.surface_filtre">{{ dvf.surface_filtre }}</span>
                            · {{ dvf.periode }}
                            <span v-if="dvf.code_commune" class="ml-1">(INSEE {{ dvf.code_commune }})</span>
                        </div>

                        <!-- Price comparison grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg p-3 text-center" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Médiane locale</div>
                                <div class="mono font-bold text-lg" style="color: var(--text)">
                                    {{ dvf.mediane_prix_m2?.toLocaleString('fr-FR') }} €/m²
                                </div>
                            </div>
                            <div class="rounded-lg p-3 text-center" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Ce bien</div>
                                <div v-if="dvf.prix_m2_bien" class="mono font-bold text-lg" style="color: var(--text)">
                                    {{ dvf.prix_m2_bien?.toLocaleString('fr-FR') }} €/m²
                                </div>
                                <div v-else class="text-sm" style="color: var(--text-4)">—</div>
                            </div>
                        </div>

                        <!-- Comparison badge -->
                        <div v-if="dvf.diff_pct !== null && dvf.diff_pct !== undefined" class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold"
                                :style="dvf.color === 'green'
                                    ? 'background: rgba(34,197,94,0.15); color: #4ade80'
                                    : dvf.color === 'red'
                                        ? 'background: rgba(239,68,68,0.15); color: #f87171'
                                        : 'background: rgba(234,179,8,0.15); color: #facc15'"
                            >
                                <span>{{ dvf.diff_pct > 0 ? '+' : '' }}{{ dvf.diff_pct }}%</span>
                                <span class="font-normal text-xs">
                                    {{ dvf.color === 'green' ? 'en dessous du marché' : dvf.color === 'red' ? 'au-dessus du marché' : 'dans la moyenne' }}
                                </span>
                            </span>
                            <span class="text-xs" style="color: var(--text-4)">vs médiane</span>
                        </div>

                        <!-- Recent transactions -->
                        <div v-if="dvf.transactions?.length">
                            <div class="text-xs mb-2 font-semibold uppercase tracking-wide" style="color: var(--text-4)">Dernières ventes similaires</div>
                            <div class="space-y-1">
                                <div
                                    v-for="(t, i) in dvf.transactions"
                                    :key="i"
                                    class="flex items-center justify-between py-1.5 px-2 rounded text-xs"
                                    style="background: var(--bg-3); color: var(--text-3)"
                                >
                                    <span style="color: var(--text-4)">{{ fmtDate(t.date_mutation) }}</span>
                                    <span>{{ t.superficie }} m²<span v-if="t.nb_pieces"> · {{ t.nb_pieces }}P</span></span>
                                    <span class="mono" style="color: var(--text-2)">{{ Number(t.valeur_fonciere)?.toLocaleString('fr-FR') }} €</span>
                                    <span class="mono font-semibold" style="color: var(--blue-2)">{{ Number(t.prix_m2)?.toLocaleString('fr-FR') }} €/m²</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Sidebar: Locations même ville -->
            <div>
                <h2 class="text-sm font-semibold mb-3" style="color: var(--text-2)">
                    Locations à {{ listing.ville ?? 'cette ville' }} ({{ locations_meme_ville?.length ?? 0 }})
                </h2>
                <p v-if="!locations_meme_ville?.length" class="text-sm mb-3" style="color: var(--text-4)">Aucune location dans cette ville.</p>
                <div class="space-y-2">
                    <div v-for="l in locations_meme_ville" :key="l.id" class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                        <div class="w-12 h-9 rounded overflow-hidden flex-shrink-0" style="background: var(--bg-3)">
                            <img v-if="l.thumbnail_url" :src="l.thumbnail_url" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium truncate" style="color: var(--text)">{{ l.titre ?? 'Sans titre' }}</div>
                            <div class="mono text-sm font-bold mt-0.5" style="color: var(--green-2)">{{ l.loyer_mensuel }} €/mois</div>
                        </div>
                    </div>
                </div>
                <Link href="/locations/create" class="mt-3 block text-xs" style="color: var(--green-2)">+ Ajouter une location</Link>
            </div>
        </div>
    </div>
</template>
