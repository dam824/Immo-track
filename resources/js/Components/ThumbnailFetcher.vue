<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ modelValue: String });
const emit = defineEmits(['update:modelValue', 'fetched']);

const loading = ref(false);
const error = ref(null);

async function fetchMeta() {
    if (!props.modelValue) return;
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.post('/api/metadata', { url: props.modelValue });
        if (data.success) {
            emit('fetched', data);
        } else {
            error.value = 'Impossible de récupérer les métadonnées';
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
        <div v-if="error" class="text-xs mt-1" style="color: var(--red)">{{ error }}</div>
    </div>
</template>
