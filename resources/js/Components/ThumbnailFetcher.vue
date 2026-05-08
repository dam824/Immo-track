<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ modelValue: String });
const emit = defineEmits(['update:modelValue', 'fetched']);

const loading = ref(false);
const error = ref(null);
const partial = ref(false);

async function fetchMeta() {
    if (!props.modelValue) return;
    loading.value = true;
    error.value = null;
    partial.value = false;
    try {
        const { data } = await axios.post('/api/metadata', { url: props.modelValue });
        if (data.success) {
            emit('fetched', data);
            // Succès partiel : titre récupéré mais pas d'image (ou inversement)
            partial.value = !data.titre || !data.thumbnail_url;
        } else {
            error.value = data.error ?? 'Impossible de récupérer les métadonnées';
        }
    } catch {
        error.value = 'Erreur réseau';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div>
        <div class="flex gap-2">
            <input
                type="url"
                :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)"
                placeholder="https://www.leboncoin.fr/..."
                class="flex-1 rounded px-3 py-2 text-sm focus:outline-none"
                style="background: var(--bg-3); color: var(--text); border: 1px solid var(--border-strong)"
            />
            <button
                type="button"
                @click="fetchMeta"
                :disabled="loading || !modelValue"
                class="px-3 py-2 rounded text-sm font-medium transition-opacity disabled:opacity-40"
                style="background: var(--blue); color: white"
            >
                {{ loading ? '...' : 'Fetch' }}
            </button>
        </div>
        <div v-if="error" class="text-xs mt-1.5 flex items-start gap-1.5">
            <span style="color: var(--red)">{{ error }}</span>
        </div>
        <div v-if="partial && !error" class="text-xs mt-1.5" style="color: var(--yellow)">
            Récupéré partiellement — complète le titre / l'image manuellement si besoin
        </div>
    </div>
</template>
