<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const stats    = ref(null);
const running  = ref(false);
const done     = ref(false);
const lines    = ref([]);
const logEl    = ref(null);
const esRef    = ref(null);

const ALL_DEPTS = ['92','93','94','95','77','78'];
const DEPT_NAMES = { '77':'Seine-et-Marne','78':'Yvelines','92':'Hauts-de-Seine','93':'Seine-Saint-Denis','94':'Val-de-Marne','95':"Val-d'Oise" };

const selectedDepts = ref([...ALL_DEPTS]);
const selectedYear  = ref(new Date().getFullYear());
const years = [2025, 2024, 2023, 2022];

async function loadStats() {
    try {
        const { data } = await axios.get('/api/dvf/stats');
        stats.value = data;
    } catch { stats.value = null; }
}

onMounted(loadStats);

function toggleDept(d) {
    const i = selectedDepts.value.indexOf(d);
    if (i >= 0) selectedDepts.value.splice(i, 1);
    else selectedDepts.value.push(d);
}

function startSync() {
    if (running.value || selectedDepts.value.length === 0) return;
    lines.value = [];
    done.value  = false;
    running.value = true;

    const params = new URLSearchParams({
        dept:     selectedDepts.value.join(','),
        year:     selectedYear.value,
        truncate: '1',
    });

    const es = new EventSource(`/api/dvf/sync?${params}`);
    esRef.value = es;

    es.onmessage = async (e) => {
        const data = JSON.parse(e.data);
        if (data.line !== undefined) {
            lines.value.push(data.line);
            await nextTick();
            if (logEl.value) logEl.value.scrollTop = logEl.value.scrollHeight;
        }
        if (data.done) {
            done.value    = true;
            running.value = false;
            es.close();
            loadStats();
        }
        if (data.error) {
            lines.value.push('ERREUR: ' + data.error);
            running.value = false;
            es.close();
        }
    };

    es.onerror = () => {
        if (running.value) {
            lines.value.push('Connexion interrompue.');
            running.value = false;
        }
        es.close();
    };
}

function stopSync() {
    if (esRef.value) esRef.value.close();
    running.value = false;
    lines.value.push('— Annulé par l\'utilisateur —');
}

function fmt(n) {
    return n?.toLocaleString('fr-FR') ?? '—';
}
</script>

<template>
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text)">Paramètres</h1>
            <p class="text-sm mt-1" style="color: var(--text-4)">Synchronisation des données de marché DVF (Demandes de Valeurs Foncières)</p>
        </div>

        <!-- Stats card -->
        <div class="rounded-lg p-5" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <h2 class="text-xs font-semibold uppercase tracking-wide mb-4" style="color: var(--text-3)">État de la base DVF</h2>

            <div v-if="!stats" class="text-sm" style="color: var(--text-4)">Chargement...</div>

            <template v-else>
                <div class="flex items-baseline gap-3 mb-4">
                    <span class="mono text-3xl font-black" style="color: var(--blue-2)">{{ fmt(stats.total) }}</span>
                    <span class="text-sm" style="color: var(--text-4)">transactions appartements</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <div
                        v-for="d in stats.depts"
                        :key="d.code"
                        class="rounded-lg p-3"
                        style="background: var(--bg-3)"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold" style="color: var(--text-4)">{{ d.code }}</span>
                            <span
                                class="text-[10px] px-1.5 py-0.5 rounded-full"
                                :style="d.nb > 0 ? 'background: rgba(34,197,94,0.15); color: #4ade80' : 'background: rgba(107,114,128,0.15); color: #9ca3af'"
                            >{{ d.nb > 0 ? 'OK' : 'vide' }}</span>
                        </div>
                        <div class="text-xs mb-0.5 truncate" style="color: var(--text-3)">{{ d.nom }}</div>
                        <div class="mono text-sm font-semibold" style="color: var(--text)">{{ fmt(d.nb) }}</div>
                        <div v-if="d.date_min" class="text-[10px] mt-0.5" style="color: var(--text-4)">
                            {{ d.date_min?.substring(0,4) }}–{{ d.date_max?.substring(0,4) }}
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sync card -->
        <div class="rounded-lg p-5 space-y-4" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
            <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: var(--text-3)">Synchroniser les données</h2>

            <!-- Dept selector -->
            <div>
                <div class="text-xs mb-2" style="color: var(--text-4)">Départements</div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="d in ALL_DEPTS"
                        :key="d"
                        type="button"
                        @click="toggleDept(d)"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-all"
                        :style="selectedDepts.includes(d)
                            ? 'background: rgba(59,130,246,0.2); color: var(--blue-2); border: 1px solid rgba(59,130,246,0.5)'
                            : 'background: var(--bg-3); color: var(--text-4); border: 1px solid var(--border-strong)'"
                    >
                        {{ d }} <span class="text-[10px] ml-1" style="opacity:0.7">{{ DEPT_NAMES[d]?.split('-')[0] }}</span>
                    </button>
                </div>
            </div>

            <!-- Year selector -->
            <div>
                <div class="text-xs mb-2" style="color: var(--text-4)">Année des données</div>
                <div class="flex gap-2">
                    <button
                        v-for="y in years"
                        :key="y"
                        type="button"
                        @click="selectedYear = y"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-all"
                        :style="selectedYear === y
                            ? 'background: rgba(59,130,246,0.2); color: var(--blue-2); border: 1px solid rgba(59,130,246,0.5)'
                            : 'background: var(--bg-3); color: var(--text-4); border: 1px solid var(--border-strong)'"
                    >
                        {{ y }}
                    </button>
                </div>
            </div>

            <!-- Warning -->
            <div class="text-xs px-3 py-2 rounded" style="background: rgba(234,179,8,0.08); border: 1px solid rgba(234,179,8,0.2); color: #facc15">
                ⚠ La synchronisation vide et réimporte toutes les données DVF. Durée : 3–10 min selon la connexion.
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3 items-center">
                <button
                    type="button"
                    @click="startSync"
                    :disabled="running || selectedDepts.length === 0"
                    class="flex items-center gap-2 px-5 py-2.5 rounded text-sm font-semibold transition-all disabled:opacity-40"
                    style="background: var(--blue); color: white"
                >
                    <svg v-if="!running" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ running ? 'Synchronisation en cours...' : '↻ Synchroniser' }}
                </button>
                <button
                    v-if="running"
                    type="button"
                    @click="stopSync"
                    class="px-4 py-2.5 rounded text-sm font-medium transition-all"
                    style="background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3)"
                >
                    Annuler
                </button>
                <span v-if="done" class="text-sm font-semibold" style="color: var(--green-2)">✓ Synchronisation terminée</span>
            </div>

            <!-- Terminal log -->
            <div
                v-if="lines.length > 0"
                ref="logEl"
                class="rounded-lg p-4 font-mono text-xs overflow-y-auto max-h-72 space-y-0.5"
                style="background: #0a0f1a; border: 1px solid var(--border-strong); color: #a3e635"
            >
                <div
                    v-for="(l, i) in lines"
                    :key="i"
                    :style="l.includes('importés') || l.includes('terminé') ? 'color: #4ade80'
                          : l.includes('ERREUR') || l.includes('introuvable') ? 'color: #f87171'
                          : l.includes('Téléchargement') ? 'color: #60a5fa'
                          : l.includes('vidée') ? 'color: #facc15'
                          : 'color: #6b7280'"
                >{{ l }}</div>
                <div v-if="running" class="inline-block w-2 h-3.5 ml-0.5 animate-pulse" style="background: #4ade80"></div>
            </div>
        </div>

        <!-- Info footer -->
        <div class="text-xs space-y-1" style="color: var(--text-4)">
            <div>Source : <span style="color: var(--text-3)">data.gouv.fr · Demandes de Valeurs Foncières géolocalisées (DGFiP)</span></div>
            <div>Données officielles des ventes immobilières enregistrées chez les notaires. Mise à jour annuelle.</div>
        </div>
    </div>
</template>
