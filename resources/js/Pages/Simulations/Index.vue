<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({ simulations: Array });

const avgCashflow = computed(() => {
    if (!props.simulations?.length) return null;
    const sum = props.simulations.reduce((s, x) => s + (x.cashflow_mensuel ?? 0), 0);
    return (sum / props.simulations.length).toFixed(0);
});

const bestCashflow = computed(() => {
    if (!props.simulations?.length) return null;
    return Math.max(...props.simulations.map(s => s.cashflow_mensuel ?? 0)).toFixed(0);
});

function deleteItem(id) {
    if (!confirm('Supprimer cette simulation ?')) return;
    router.delete(`/simulations/${id}`);
}

function cashflowColor(v) {
    return Number(v) >= 0 ? 'var(--green-2)' : 'var(--red)';
}

function cashflowBg(v) {
    return Number(v) >= 0 ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)';
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--text)">Simulateur</h1>
                <p class="text-sm mt-0.5" style="color: var(--text-4)">{{ simulations?.length ?? 0 }} simulation{{ (simulations?.length ?? 0) > 1 ? 's' : '' }} enregistrée{{ (simulations?.length ?? 0) > 1 ? 's' : '' }}</p>
            </div>
            <Link href="/simulations/create" class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors" style="background: var(--violet); color: white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nouvelle simulation
            </Link>
        </div>

        <!-- Summary KPIs -->
        <div v-if="simulations?.length" class="grid grid-cols-3 gap-3 mb-6">
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong); border-top: 2px solid var(--violet)">
                <div class="text-xs uppercase tracking-wide mb-1" style="color: var(--text-4)">Total</div>
                <div class="mono text-2xl font-bold" style="color: var(--violet-2)">{{ simulations.length }}</div>
            </div>
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong); border-top: 2px solid var(--blue)">
                <div class="text-xs uppercase tracking-wide mb-1" style="color: var(--text-4)">Cashflow moyen</div>
                <div class="mono text-2xl font-bold" :style="`color: ${cashflowColor(avgCashflow)}`">
                    {{ Number(avgCashflow) >= 0 ? '+' : '' }}{{ avgCashflow }} €/mois
                </div>
            </div>
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong); border-top: 2px solid var(--green)">
                <div class="text-xs uppercase tracking-wide mb-1" style="color: var(--text-4)">Meilleur cashflow</div>
                <div class="mono text-2xl font-bold" :style="`color: ${cashflowColor(bestCashflow)}`">
                    {{ Number(bestCashflow) >= 0 ? '+' : '' }}{{ bestCashflow }} €/mois
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-if="!simulations?.length" class="py-16 text-center rounded-lg" style="background: var(--bg-2); border: 1px dashed var(--border-strong)">
            <p class="text-sm" style="color: var(--text-4)">Aucune simulation enregistrée.</p>
            <Link href="/simulations/create" class="inline-block mt-3 text-sm" style="color: var(--violet-2)">Créer une simulation →</Link>
        </div>

        <!-- Table -->
        <div v-else class="rounded-lg overflow-hidden" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-strong)">
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Simulation</th>
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Bien</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Emprunt</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Mensualité</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Loyer</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Cashflow</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Rdmt brut</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="s in simulations"
                        :key="s.id"
                        class="transition-colors row-hover"
                        style="border-bottom: 1px solid var(--border-soft)"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium" style="color: var(--text)">{{ s.titre ?? 'Sans titre' }}</div>
                            <div class="text-xs mt-0.5" style="color: var(--text-4)">{{ s.taux_interet }}% · {{ s.duree_ans }} ans</div>
                        </td>
                        <td class="px-4 py-3">
                            <div v-if="s.achat">
                                <Link :href="`/achats/${s.achat.id}`" class="text-xs hover:underline" style="color: var(--blue-2)">{{ s.achat.titre ?? 'Bien' }}</Link>
                                <div class="text-xs" style="color: var(--text-4)">{{ s.achat.ville }}</div>
                            </div>
                            <span v-else style="color: var(--text-4)">—</span>
                        </td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ s.montant_emprunt?.toLocaleString('fr-FR') }} €</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ s.mensualite_credit?.toFixed(0) }} €/mois</td>
                        <td class="px-4 py-3 text-right mono text-xs font-medium" style="color: var(--green-2)">{{ s.loyer_retenu }} €/mois</td>
                        <td class="px-4 py-3 text-right">
                            <span
                                class="mono text-xs font-bold px-2 py-0.5 rounded"
                                :style="`background: ${cashflowBg(s.cashflow_mensuel)}; color: ${cashflowColor(s.cashflow_mensuel)}`"
                            >
                                {{ Number(s.cashflow_mensuel) >= 0 ? '+' : '' }}{{ s.cashflow_mensuel?.toFixed(0) }} €
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right mono font-semibold" style="color: var(--yellow)">{{ s.rendement_brut }}%</td>
                        <td class="px-4 py-3 text-right">
                            <button @click="deleteItem(s.id)" class="text-xs px-2 py-1 rounded transition-colors" style="color: #f87171; background: rgba(239,68,68,0.12)">Suppr.</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.row-hover:hover {
    background: rgba(255, 255, 255, 0.03);
}
</style>
