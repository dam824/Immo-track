<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import ThumbnailFetcher from '@/Components/ThumbnailFetcher.vue';
import { TAXE_RATES, estimateTF } from '@/utils/taxeRates.js';

const DPE_COST_M2 = { A: 4, B: 8, C: 14, D: 20, E: 28, F: 36, G: 45 };

const props = defineProps({ listing: Object });

const form = useForm({
    titre: props.listing.titre ?? '',
    url: props.listing.url ?? '',
    thumbnail_url: props.listing.thumbnail_url ?? '',
    source: props.listing.source ?? '',
    ville: props.listing.ville ?? '',
    departement: props.listing.departement ?? '',
    code_postal: props.listing.code_postal ?? '',
    quartier: props.listing.quartier ?? '',
    superficie: props.listing.superficie ?? '',
    nb_pieces: props.listing.nb_pieces ?? '',
    nb_chambres: props.listing.nb_chambres ?? '',
    dpe: props.listing.dpe ?? '',
    meuble: props.listing.meuble ?? false,
    prix_achat: props.listing.prix_achat ?? '',
    charges_copro: props.listing.charges_copro ?? '',
    taxe_fonciere: props.listing.taxe_fonciere ?? '',
    travaux_estimes: props.listing.travaux_estimes ?? '',
    charges_annuelles_energie: props.listing.charges_annuelles_energie ?? '',
    cave: props.listing.cave ?? false,
    parking: props.listing.parking ?? false,
    balcon: props.listing.balcon ?? false,
    ascenseur: props.listing.ascenseur ?? false,
    gardien: props.listing.gardien ?? false,
    digicode: props.listing.digicode ?? false,
    statut: props.listing.statut ?? 'actif',
    notes: props.listing.notes ?? '',
});

const cityTaxRate = ref(TAXE_RATES[props.listing.ville?.toLowerCase().trim()] ?? null);
const tfEstimate  = ref(estimateTF(props.listing.superficie, cityTaxRate.value));

watch(() => form.ville, (v) => {
    cityTaxRate.value = TAXE_RATES[v?.toLowerCase().trim()] ?? null;
    tfEstimate.value  = estimateTF(form.superficie, cityTaxRate.value);
});

watch(() => form.superficie, (s) => {
    tfEstimate.value = estimateTF(s, cityTaxRate.value);
});

const energieEstimate = computed(() => {
    const rate = DPE_COST_M2[form.dpe];
    if (!rate || !form.superficie) return null;
    return Math.round(rate * form.superficie);
});

const loyerMeublEstime = computed(() => {
    if (!form.meuble || !form.prix_achat) return null;
    return Math.round(form.prix_achat * 0.055 / 12);
});

function onFetched(meta) {
    if (meta.titre) form.titre = meta.titre;
    if (meta.thumbnail_url) form.thumbnail_url = meta.thumbnail_url;
}

function submit() {
    form.put(`/achats/${props.listing.id}`);
}
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link :href="`/achats/${listing.id}`" class="text-sm transition-colors" style="color: var(--text-4)">← Retour</Link>
            <h1 class="text-2xl font-bold" style="color: var(--text)">Modifier l'annonce</h1>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">URL de l'annonce</h2>
                <ThumbnailFetcher v-model="form.url" @fetched="onFetched" />
                <div v-if="form.thumbnail_url" class="flex items-center gap-3">
                    <img :src="form.thumbnail_url" class="w-24 h-16 object-cover rounded" />
                    <button type="button" @click="form.thumbnail_url = ''" class="text-xs hover:underline" style="color: var(--red)">Retirer</button>
                </div>
            </div>

            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Identification</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2"><label class="text-xs block mb-1" style="color: var(--text-4)">Titre</label><input v-model="form.titre" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Source</label>
                        <input v-model="form.source" list="sources-list" placeholder="leboncoin, bien ici…" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" />
                        <datalist id="sources-list">
                            <option value="leboncoin" /><option value="seloger" /><option value="bien ici" /><option value="jinka" /><option value="pap" /><option value="logic-immo" /><option value="meilleursagents" /><option value="orpi" /><option value="century21" /><option value="laforet" /><option value="era" />
                        </datalist>
                    </div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Statut</label><select v-model="form.statut" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"><option value="actif">Actif</option><option value="a_visiter">À visiter</option><option value="visite">Visité</option><option value="offre_faite">Offre faite</option><option value="archive">Archivé</option><option value="elimine">Éliminé</option></select></div>
                </div>
            </div>

            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Localisation</h2>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Ville</label><input v-model="form.ville" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Département</label><input v-model="form.departement" type="text" maxlength="3" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Code postal</label><input v-model="form.code_postal" type="text" maxlength="10" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div class="col-span-3"><label class="text-xs block mb-1" style="color: var(--text-4)">Quartier</label><input v-model="form.quartier" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                </div>
            </div>

            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Bien</h2>
                <div class="grid grid-cols-4 gap-3">
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Surface (m²)</label><input v-model="form.superficie" type="number" step="0.5" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Pièces</label><input v-model="form.nb_pieces" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Chambres</label><input v-model="form.nb_chambres" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">DPE</label><select v-model="form.dpe" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"><option value="">—</option><option v-for="d in ['A','B','C','D','E','F','G']" :key="d" :value="d">{{ d }}</option></select></div>
                </div>
            </div>

            <!-- Caractéristiques -->
            <div class="rounded-lg p-4 space-y-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Caractéristiques</h2>

                <!-- Meublé -->
                <div>
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <div
                            @click="form.meuble = !form.meuble"
                            class="w-9 h-5 rounded-full transition-colors relative flex-shrink-0"
                            :style="form.meuble ? 'background: var(--blue)' : 'background: var(--bg-4, #374151)'"
                        >
                            <div class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform" :style="form.meuble ? 'transform: translateX(18px)' : 'transform: translateX(2px)'"></div>
                        </div>
                        <span class="text-sm font-medium" style="color: var(--text)">Meublé</span>
                    </label>
                    <div v-if="form.meuble" class="mt-2 ml-11 rounded px-3 py-2 text-xs space-y-0.5" style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2)">
                        <div style="color: var(--blue-2)">Statut LMNP possible · Loyer meublé = nu +15 à +25%</div>
                        <div v-if="loyerMeublEstime" style="color: var(--text-3)">
                            Estimation loyer meublé ≈ <span class="font-semibold" style="color: var(--blue-2)">{{ loyerMeublEstime.toLocaleString('fr-FR') }} €/mois</span>
                            <span style="color: var(--text-4)"> (rendement 5,5%)</span>
                        </div>
                    </div>
                </div>

                <!-- Commodités -->
                <div>
                    <div class="text-xs mb-2" style="color: var(--text-4)">Équipements</div>
                    <div class="grid grid-cols-3 gap-2">
                        <label v-for="item in [
                            { key: 'cave',      label: 'Cave' },
                            { key: 'parking',   label: 'Parking' },
                            { key: 'balcon',    label: 'Balcon / Terrasse' },
                            { key: 'ascenseur', label: 'Ascenseur' },
                            { key: 'gardien',   label: 'Gardien' },
                            { key: 'digicode',  label: 'Digicode / Interphone' },
                        ]" :key="item.key"
                            class="flex items-center gap-2 px-3 py-2 rounded cursor-pointer select-none transition-colors"
                            :style="form[item.key]
                                ? 'background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.3)'
                                : 'background: var(--bg-3); border: 1px solid var(--border-strong)'"
                            @click="form[item.key] = !form[item.key]"
                        >
                            <svg class="w-3.5 h-3.5 flex-shrink-0 transition-opacity" :style="form[item.key] ? 'color: var(--blue-2); opacity:1' : 'opacity:0.3; color: var(--text-4)'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span class="text-xs" :style="form[item.key] ? 'color: var(--text)' : 'color: var(--text-4)'">{{ item.label }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Financier</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Prix d'achat (€)</label><input v-model="form.prix_achat" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Charges copro (€/mois)</label><input v-model="form.charges_copro" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">
                            Taxe foncière (€/an)
                            <span v-if="cityTaxRate" class="ml-1 font-normal" style="color: var(--blue-2)">· {{ form.ville }} {{ cityTaxRate }}%</span>
                        </label>
                        <input v-model="form.taxe_fonciere" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" />
                        <div v-if="cityTaxRate && tfEstimate" class="text-[11px] mt-1" style="color: var(--text-4)">Estimation : ~{{ tfEstimate }} €/an · modifiable</div>
                    </div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Travaux estimés (€)</label><input v-model="form.travaux_estimes" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div class="col-span-2">
                        <label class="text-xs block mb-1" style="color: var(--text-4)">
                            Charges annuelles énergie (€/an)
                            <span v-if="energieEstimate && form.dpe" class="ml-1 font-normal" style="color: var(--text-4)">· estimation DPE {{ form.dpe }}</span>
                        </label>
                        <input v-model="form.charges_annuelles_energie" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" />
                        <div v-if="energieEstimate" class="text-[11px] mt-1" style="color: var(--text-4)">
                            Estimation selon DPE {{ form.dpe }} : ~{{ energieEstimate.toLocaleString('fr-FR') }} €/an · modifiable
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <label class="text-xs block mb-1" style="color: var(--text-4)">Notes</label>
                <textarea v-model="form.notes" rows="3" class="w-full rounded px-3 py-2 text-sm focus:outline-none resize-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded text-sm font-medium transition-opacity disabled:opacity-50" style="background: var(--blue); color: white">
                    {{ form.processing ? 'Enregistrement...' : 'Mettre à jour' }}
                </button>
                <Link :href="`/achats/${listing.id}`" class="px-6 py-2 rounded text-sm transition-colors" style="background: var(--bg-3); color: var(--text-3)">Annuler</Link>
            </div>
        </form>
    </div>
</template>
