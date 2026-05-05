<script setup>
import { computed } from 'vue';
import StatCard from '@/Components/StatCard.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    stats_villes: Array,
    global: Object,
});

const villesSorted = computed(() =>
    [...(props.stats_villes ?? [])].sort((a, b) => (b.rendement_estime ?? 0) - (a.rendement_estime ?? 0))
);

const villesAvecPrix = computed(() => props.stats_villes?.filter(v => v.prix_m2_moyen) ?? []);
const villesAvecRdt = computed(() => villesSorted.value.filter(v => v.rendement_estime > 0));

const chartOpts = {
    chart: { background: 'transparent', toolbar: { show: false }, sparkline: { enabled: false } },
    theme: { mode: 'dark' },
    plotOptions: { bar: { borderRadius: 4, distributed: false } },
    grid: { borderColor: '#1f2937', strokeDashArray: 3 },
    dataLabels: { enabled: false },
    tooltip: { theme: 'dark' },
    states: { hover: { filter: { type: 'lighten', value: 0.15 } } },
};

const chartPrixM2 = computed(() => ({
    series: [{ name: 'Prix €/m²', data: villesAvecPrix.value.map(v => v.prix_m2_moyen) }],
    options: {
        ...chartOpts,
        xaxis: { categories: villesAvecPrix.value.map(v => v.ville), labels: { style: { colors: '#6b7280', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#6b7280', fontSize: '11px' } } },
        colors: ['#3b82f6'],
    },
}));

const chartRendement = computed(() => ({
    series: [{ name: 'Rendement %', data: villesAvecRdt.value.map(v => v.rendement_estime) }],
    options: {
        ...chartOpts,
        xaxis: { categories: villesAvecRdt.value.map(v => v.ville), labels: { style: { colors: '#6b7280', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#6b7280', fontSize: '11px' } } },
        colors: ['#22c55e'],
    },
}));
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold" style="color: var(--text)">Statistiques</h1>
            <p class="text-sm mt-0.5" style="color: var(--text-4)">Analyse de votre marché immobilier par ville</p>
        </div>

        <!-- KPI bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-8">
            <StatCard label="Achats" :value="global?.total_achats ?? 0" color="blue" />
            <StatCard label="Locations" :value="global?.total_locations ?? 0" color="green" />
            <StatCard label="Simulations" :value="global?.total_simulations ?? 0" color="purple" />
            <StatCard label="Rendement moyen" :value="global?.rendement_moyen" unit="%" color="yellow" />
            <StatCard label="Meilleur cashflow" :value="global?.meilleur_cashflow" unit="€/mois" color="green" />
        </div>

        <!-- Empty state -->
        <p v-if="!stats_villes?.length" class="text-sm py-10 text-center rounded-lg" style="color: var(--text-4); background: var(--bg-2); border: 1px dashed var(--border-strong)">
            Ajoutez des annonces pour voir les statistiques par ville.
        </p>

        <!-- Charts -->
        <div v-if="stats_villes?.length" class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide mb-4" style="color: var(--text-3)">Prix moyen au m² par ville (€)</h2>
                <VueApexCharts type="bar" height="200" :series="chartPrixM2.series" :options="chartPrixM2.options" />
            </div>
            <div class="rounded-lg p-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
                <h2 class="text-xs font-semibold uppercase tracking-wide mb-4" style="color: var(--text-3)">Rendement estimé par ville (%)</h2>
                <VueApexCharts type="bar" height="200" :series="chartRendement.series" :options="chartRendement.options" />
            </div>
        </div>

        <!-- Comparison table -->
        <div v-if="stats_villes?.length" class="rounded-lg overflow-hidden" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-strong)">
                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Ville</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Achats</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Prix moyen</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">€/m² achat</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Locations</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Loyer moyen</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">€/m² loc.</th>
                        <th class="px-4 py-3 text-right text-xs uppercase tracking-wide font-medium" style="color: var(--text-4)">Rendement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="v in villesSorted"
                        :key="v.ville"
                        class="transition-colors row-hover"
                        style="border-bottom: 1px solid var(--border-soft)"
                    >
                        <td class="px-4 py-3 font-medium" style="color: var(--text)">{{ v.ville }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ v.nb_achats }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--blue-2)">{{ v.prix_moyen ? v.prix_moyen.toLocaleString('fr-FR') + ' €' : '—' }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ v.prix_m2_moyen ? v.prix_m2_moyen.toLocaleString('fr-FR') + ' €' : '—' }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ v.nb_locations }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--green-2)">{{ v.loyer_moyen ? v.loyer_moyen + ' €/mois' : '—' }}</td>
                        <td class="px-4 py-3 text-right mono text-xs" style="color: var(--text-3)">{{ v.loyer_m2_moyen ? v.loyer_m2_moyen + ' €' : '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span
                                v-if="v.rendement_estime"
                                class="mono text-xs font-bold px-2 py-0.5 rounded"
                                :style="v.rendement_estime >= 5
                                    ? 'background: rgba(34,197,94,0.15); color: #4ade80'
                                    : v.rendement_estime > 0
                                        ? 'background: rgba(234,179,8,0.15); color: #facc15'
                                        : 'color: var(--text-4)'"
                            >{{ v.rendement_estime }}%</span>
                            <span v-else style="color: var(--text-4)">—</span>
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
