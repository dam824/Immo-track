<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import ThumbnailFetcher from '@/Components/ThumbnailFetcher.vue';

const form = useForm({
    type: 'location',
    titre: '',
    url: '',
    thumbnail_url: '',
    source: '',
    ville: '',
    departement: '',
    code_postal: '',
    quartier: '',
    superficie: '',
    nb_pieces: '',
    nb_chambres: '',
    dpe: '',
    meuble: false,
    loyer_mensuel: '',
    charges_incluses: false,
    notes: '',
});

function onFetched(meta) {
    if (meta.titre && !form.titre) form.titre = meta.titre;
    if (meta.thumbnail_url) form.thumbnail_url = meta.thumbnail_url;
}

function submit() {
    form.post('/locations');
}
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/locations" class="text-sm transition-colors" style="color: var(--text-4)">← Retour</Link>
            <h1 class="text-2xl font-bold" style="color: var(--text)">Nouvelle annonce location</h1>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- URL -->
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">URL de l'annonce</h2>
                <ThumbnailFetcher v-model="form.url" @fetched="onFetched" />
                <div v-if="form.thumbnail_url" class="flex items-center gap-3">
                    <img :src="form.thumbnail_url" class="w-24 h-16 object-cover rounded" />
                    <button type="button" @click="form.thumbnail_url = ''" class="text-xs hover:underline" style="color: var(--red)">Retirer</button>
                </div>
            </div>

            <!-- Identification -->
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Identification</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Titre</label>
                        <input v-model="form.titre" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" />
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color: var(--text-4)">Source</label>
                        <input v-model="form.source" list="sources-list" placeholder="leboncoin, bien ici…" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" />
                        <datalist id="sources-list">
                            <option value="leboncoin" /><option value="seloger" /><option value="bien ici" /><option value="jinka" /><option value="pap" /><option value="logic-immo" /><option value="meilleursagents" /><option value="orpi" /><option value="century21" /><option value="laforet" /><option value="era" />
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Localisation</h2>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Ville</label><input v-model="form.ville" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Département</label><input v-model="form.departement" type="text" maxlength="3" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Code postal</label><input v-model="form.code_postal" type="text" maxlength="10" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div class="col-span-3"><label class="text-xs block mb-1" style="color: var(--text-4)">Quartier</label><input v-model="form.quartier" type="text" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                </div>
            </div>

            <!-- Bien -->
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Bien</h2>
                <div class="grid grid-cols-4 gap-3">
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Surface (m²)</label><input v-model="form.superficie" type="number" step="0.5" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Pièces</label><input v-model="form.nb_pieces" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">Chambres</label><input v-model="form.nb_chambres" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                    <div><label class="text-xs block mb-1" style="color: var(--text-4)">DPE</label><select v-model="form.dpe" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"><option value="">—</option><option v-for="d in ['A','B','C','D','E','F','G']" :key="d" :value="d">{{ d }}</option></select></div>
                </div>
                <label class="flex items-center gap-2 text-sm cursor-pointer" style="color: var(--text-3)">
                    <input type="checkbox" v-model="form.meuble" class="w-4 h-4" /> Meublé
                </label>
            </div>

            <!-- Financier -->
            <div class="rounded-lg p-4 space-y-3" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Financier</h2>
                <div><label class="text-xs block mb-1" style="color: var(--text-4)">Loyer mensuel (€)</label><input v-model="form.loyer_mensuel" type="number" min="0" class="w-full rounded px-3 py-2 text-sm focus:outline-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)" /></div>
                <label class="flex items-center gap-2 text-sm cursor-pointer" style="color: var(--text-3)">
                    <input type="checkbox" v-model="form.charges_incluses" class="w-4 h-4" /> Charges incluses
                </label>
            </div>

            <!-- Notes -->
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <label class="text-xs block mb-1" style="color: var(--text-4)">Notes</label>
                <textarea v-model="form.notes" rows="3" class="w-full rounded px-3 py-2 text-sm focus:outline-none resize-none" style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded text-sm font-medium transition-opacity disabled:opacity-50" style="background: var(--green); color: white">
                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                </button>
                <Link href="/locations" class="px-6 py-2 rounded text-sm transition-colors" style="background: var(--bg-3); color: var(--text-3)">Annuler</Link>
            </div>
        </form>
    </div>
</template>
