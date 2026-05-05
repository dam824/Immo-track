<script setup>
defineProps({ listing: Object });
</script>

<template>
    <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--bg-2); border: 1px solid var(--border-strong)">
        <div class="w-16 h-12 rounded overflow-hidden flex-shrink-0" style="background: var(--bg-3)">
            <img
                v-if="listing.thumbnail_url"
                :src="listing.thumbnail_url"
                class="w-full h-full object-cover"
                @error="$event.target.style.display='none'"
            />
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium truncate" style="color: var(--text)">{{ listing.titre ?? 'Sans titre' }}</div>
            <div class="text-xs mt-0.5" style="color: var(--text-4)">
                {{ listing.ville }}<span v-if="listing.superficie"> · {{ listing.superficie }}m²</span><span v-if="listing.nb_pieces"> · {{ listing.nb_pieces }}P</span>
            </div>
            <div v-if="listing.prix_achat" class="mono font-bold text-sm mt-0.5" style="color: var(--blue-2)">
                {{ listing.prix_achat?.toLocaleString('fr-FR') }} €
            </div>
            <div v-if="listing.loyer_mensuel" class="mono font-bold text-sm mt-0.5" style="color: var(--green-2)">
                {{ listing.loyer_mensuel }} €/mois
            </div>
        </div>
    </div>
</template>
