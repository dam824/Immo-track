import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useImmoStore = defineStore('immo', () => {
    const filtreVille = ref('');
    const filtreStatut = ref('');

    function setFiltreVille(v) { filtreVille.value = v; }
    function setFiltreStatut(s) { filtreStatut.value = s; }

    return { filtreVille, filtreStatut, setFiltreVille, setFiltreStatut };
});
