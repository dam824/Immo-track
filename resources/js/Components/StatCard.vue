<script setup>
const props = defineProps({
    label: String,
    value: [String, Number],
    unit: { type: String, default: '' },
    color: { type: String, default: 'blue' },
});

const colorMap = {
    blue: { border: '#3b82f6', text: '#60a5fa' },
    green: { border: '#22c55e', text: '#4ade80' },
    purple: { border: '#a855f7', text: '#c084fc' },
    yellow: { border: '#eab308', text: '#facc15' },
    red: { border: '#ef4444', text: '#f87171' },
};

const c = colorMap[props.color] ?? colorMap.blue;
</script>

<template>
    <div
        class="rounded-lg p-4"
        :style="`background: var(--bg-2); border: 1px solid var(--border-strong); border-top: 2px solid ${c.border}`"
    >
        <div class="text-xs uppercase tracking-wide mb-2" style="color: var(--text-4)">{{ label }}</div>
        <div class="mono text-2xl font-bold" :style="`color: ${c.text}`">
            {{ value ?? '—' }}<span v-if="unit && value != null" class="text-sm ml-1" style="color: var(--text-3)">{{ unit }}</span>
        </div>
    </div>
</template>
