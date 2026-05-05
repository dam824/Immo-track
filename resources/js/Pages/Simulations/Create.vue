<script setup>
import { ref, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    achats: Array,
    locations: Array,
    preselect_achat_id: [String, Number],
});

const form = useForm({
    titre: '',
    listing_achat_id: props.preselect_achat_id ?? '',
    listing_location_id: '',
    montant_emprunt: '',
    apport: 0,
    taux_interet: 3.5,
    duree_ans: 20,
    charges_copro: 0,
    taxe_fonciere: 0,
    assurance_pno: 50,
    loyer_retenu: '',
    prix_achat: '',
    notes: '',
});

const locationsVille = ref([]);
const calcResult = ref(null);
const calcLoading = ref(false);

function recalcEmprunt() {
    const prix = Number(form.prix_achat) || 0;
    const apport = Number(form.apport) || 0;
    if (prix > 0) form.montant_emprunt = Math.max(0, prix - apport);
}

watch(() => form.apport, recalcEmprunt);
watch(() => form.prix_achat, recalcEmprunt);

watch(() => form.listing_achat_id, (id) => {
    const a = props.achats?.find(a => a.id == id);
    if (a) {
        form.prix_achat = a.prix_achat ?? '';
        form.charges_copro = a.charges_copro ?? 0;
        form.taxe_fonciere = a.taxe_fonciere ?? 0;
        recalcEmprunt();
        if (a.ville) fetchLocationsVille(a.ville);
    }
});

async function fetchLocationsVille(ville) {
    try {
        const { data } = await axios.get(`/api/locations/ville/${encodeURIComponent(ville)}`);
        locationsVille.value = data;
    } catch {
        locationsVille.value = [];
    }
}

function selectLocation(l) {
    form.listing_location_id = l.id;
    form.loyer_retenu = l.loyer_mensuel ?? '';
}

let calcTimer = null;
function scheduleCalc() {
    clearTimeout(calcTimer);
    calcTimer = setTimeout(doCalc, 400);
}

async function doCalc() {
    if (!form.montant_emprunt || !form.loyer_retenu) { calcResult.value = null; return; }
    calcLoading.value = true;
    try {
        const { data } = await axios.post('/api/simulations/calculer', {
            montant_emprunt: form.montant_emprunt,
            taux_interet: form.taux_interet,
            duree_ans: form.duree_ans,
            charges_copro: form.charges_copro,
            taxe_fonciere: form.taxe_fonciere,
            assurance_pno: form.assurance_pno,
            loyer_retenu: form.loyer_retenu,
            prix_achat: form.prix_achat,
        });
        calcResult.value = data;
    } catch {
        calcResult.value = null;
    } finally {
        calcLoading.value = false;
    }
}

watch(
    () => [form.montant_emprunt, form.taux_interet, form.duree_ans, form.charges_copro, form.taxe_fonciere, form.assurance_pno, form.loyer_retenu, form.prix_achat],
    scheduleCalc
);

onMounted(() => {
    if (props.preselect_achat_id) {
        const a = props.achats?.find(a => a.id == props.preselect_achat_id);
        if (a) {
            form.prix_achat = a.prix_achat ?? '';
            form.charges_copro = a.charges_copro ?? 0;
            form.taxe_fonciere = a.taxe_fonciere ?? 0;
            recalcEmprunt();
            if (a.ville) fetchLocationsVille(a.ville);
        }
    }
});

function submit() {
    form.post('/simulations');
}

const inp = 'w-full rounded px-3 py-2 text-sm focus:outline-none';
const inpStyle = 'background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)';
</script>

<template>
    <div>
        <div class="flex items-center gap-3 mb-6">
            <Link href="/simulations" class="text-sm" style="color: var(--text-4)">← Retour</Link>
            <h1 class="text-2xl font-bold" style="color: var(--text)">Nouvelle simulation cashflow</h1>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Left: Inputs -->
            <div class="space-y-4">
                <!-- Bien achat -->
                <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Bien à l'achat</h2>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Sélectionner un bien</label>
                        <select v-model="form.listing_achat_id" :class="inp" :style="inpStyle">
                            <option value="">— Saisie manuelle —</option>
                            <option v-for="a in achats" :key="a.id" :value="a.id">
                                {{ a.titre ?? 'Sans titre' }} — {{ a.ville }} — {{ a.prix_achat?.toLocaleString('fr-FR') }} €
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Prix d'achat (€)</label>
                            <input v-model="form.prix_achat" type="number" min="0" :class="inp" :style="inpStyle" />
                        </div>
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Apport (€)</label>
                            <input v-model="form.apport" type="number" min="0" :class="inp" :style="inpStyle" placeholder="0 = sans apport" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs block mb-1" style="color: var(--text-4)">
                                Montant emprunté (€)
                                <span v-if="form.prix_achat && form.apport > 0" class="ml-2 font-normal" style="color: var(--text-4)">
                                    = {{ Number(form.prix_achat).toLocaleString('fr-FR') }} − {{ Number(form.apport).toLocaleString('fr-FR') }} €
                                </span>
                            </label>
                            <input v-model="form.montant_emprunt" type="number" min="0" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Taux (%)</label>
                            <input v-model="form.taux_interet" type="number" step="0.05" min="0" max="20" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Durée (ans)</label>
                            <input v-model="form.duree_ans" type="number" min="1" max="30" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Copro (€/mois)</label>
                            <input v-model="form.charges_copro" type="number" min="0" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                        <div>
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Taxe fonc. (€/an)</label>
                            <input v-model="form.taxe_fonciere" type="number" min="0" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs block mb-1" style="color: var(--text-4)">Assurance PNO (€/mois)</label>
                            <input v-model="form.assurance_pno" type="number" min="0" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                        </div>
                    </div>
                </div>

                <!-- Loyer -->
                <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Loyer retenu</h2>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Loyer mensuel (€)</label>
                        <input v-model="form.loyer_retenu" type="number" min="0" @input="scheduleCalc" :class="inp" :style="inpStyle" />
                    </div>
                    <div v-if="locationsVille.length">
                        <div class="text-xs mb-2" style="color: var(--text-4)">Locations dans la même ville :</div>
                        <div class="space-y-1 max-h-44 overflow-y-auto">
                            <button
                                v-for="l in locationsVille"
                                :key="l.id"
                                type="button"
                                @click="selectLocation(l)"
                                class="w-full text-left p-2 rounded text-sm transition-colors"
                                :style="form.listing_location_id == l.id
                                    ? 'background: rgba(59,130,246,0.2); color: var(--blue-2); border: 1px solid rgba(59,130,246,0.4)'
                                    : 'background: var(--bg-3); color: var(--text-3); border: 1px solid var(--border-strong)'"
                            >
                                {{ l.titre ?? 'Sans titre' }} — <span class="mono font-semibold" style="color: var(--green-2)">{{ l.loyer_mensuel }} €/mois</span><span v-if="l.superficie" style="color: var(--text-4)"> · {{ l.superficie }}m²</span>
                            </button>
                        </div>
                    </div>
                    <div v-else-if="form.listing_achat_id" class="text-xs" style="color: var(--text-4)">Aucune location enregistrée dans cette ville.</div>
                </div>

                <!-- Sauvegarder -->
                <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Sauvegarder</h2>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Titre de la simulation</label>
                        <input v-model="form.titre" type="text" placeholder="Ex : Paris 12e F2 — scénario optimiste" :class="inp" :style="inpStyle" />
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full rounded px-3 py-2 text-sm focus:outline-none resize-none" :style="inpStyle"></textarea>
                    </div>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing || !form.montant_emprunt || !form.loyer_retenu"
                        class="px-6 py-2 rounded text-sm font-medium transition-opacity disabled:opacity-40"
                        style="background: var(--violet); color: white"
                    >
                        {{ form.processing ? 'Enregistrement...' : 'Sauvegarder la simulation' }}
                    </button>
                </div>
            </div>

            <!-- Right: Live results -->
            <div>
                <div class="rounded-lg p-5 sticky top-6" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                    <h2 class="text-xs font-semibold uppercase tracking-wide mb-4" style="color: var(--text-3)">Résultats en temps réel</h2>

                    <p v-if="!calcResult && !calcLoading" class="text-sm" style="color: var(--text-4)">
                        Renseignez le montant emprunté et le loyer pour calculer.
                    </p>
                    <div v-if="calcLoading" class="text-sm" style="color: var(--text-3)">Calcul en cours...</div>

                    <div v-if="calcResult" class="space-y-4">
                        <!-- Grid metrics -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg p-3" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Mensualité crédit</div>
                                <div class="mono font-bold text-lg" style="color: var(--text)">{{ calcResult.mensualite_credit?.toFixed(0) }} €/mois</div>
                            </div>
                            <div class="rounded-lg p-3" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Total sorties</div>
                                <div class="mono font-bold text-lg" style="color: var(--red)">{{ calcResult.sorties_totales?.toFixed(0) }} €/mois</div>
                            </div>
                            <div class="rounded-lg p-3" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Loyer retenu</div>
                                <div class="mono font-bold text-lg" style="color: var(--green-2)">{{ form.loyer_retenu }} €/mois</div>
                            </div>
                            <div class="rounded-lg p-3" style="background: var(--bg-3)">
                                <div class="text-xs mb-1" style="color: var(--text-4)">Rendement brut</div>
                                <div class="mono font-bold text-lg" style="color: var(--yellow)">{{ calcResult.rendement_brut }}%</div>
                            </div>
                        </div>

                        <!-- Cashflow hero -->
                        <div
                            class="rounded-xl p-6 text-center"
                            :style="calcResult.cashflow_mensuel >= 0
                                ? 'border: 2px solid rgba(34,197,94,0.5); background: rgba(34,197,94,0.06)'
                                : 'border: 2px solid rgba(239,68,68,0.5); background: rgba(239,68,68,0.06)'"
                        >
                            <div class="text-xs uppercase tracking-wider mb-2" style="color: var(--text-4)">Cashflow mensuel net</div>
                            <div
                                class="mono text-5xl font-black"
                                :style="calcResult.cashflow_mensuel >= 0 ? 'color: var(--green-2)' : 'color: var(--red)'"
                            >
                                {{ calcResult.cashflow_mensuel >= 0 ? '+' : '' }}{{ calcResult.cashflow_mensuel?.toFixed(0) }} €
                            </div>
                            <div class="text-sm mt-3" style="color: var(--text-4)">
                                Rendement net : <span class="mono font-semibold" style="color: var(--text-2)">{{ calcResult.rendement_net }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
