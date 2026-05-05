<script setup>
defineProps({ value: { type: Number, default: 0 } });
</script>

<template>
    <span
        class="mono px-2 py-0.5 rounded font-bold text-sm"
        :style="value >= 0
            ? 'background: rgba(34,197,94,0.12); color: #4ade80'
            : 'background: rgba(239,68,68,0.12); color: #f87171'"
    >
        {{ value >= 0 ? '+' : '' }}{{ value?.toFixed(0) }} €/mois
    </span>
</template>
