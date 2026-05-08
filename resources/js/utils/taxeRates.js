// Taux taxe foncière 2024 — Île-de-France (93, 94, 95)
export const TAXE_RATES = {
    // 95 — Val-d'Oise
    'argenteuil': 52.65, 'sarcelles': 56.15, 'cergy': 46.65,
    'garges-lès-gonesse': 51.90, 'garges lès gonesse': 51.90, 'garges les gonesse': 51.90,
    'franconville': 35.46, 'pontoise': 45.63, 'ermont': 42.93,
    'goussainville': 51.20, 'bezons': 45.95,
    'herblay-sur-seine': 39.21, 'herblay sur seine': 39.21, 'herblay': 39.21,
    'sannois': 48.15, 'eaubonne': 39.78,
    'saint-gratien': 39.91, 'saint gratien': 39.91,
    'arnouville': 41.01, 'osny': 42.18,

    // 93 — Seine-Saint-Denis
    'saint-denis': 38.94, 'saint denis': 38.94,
    'montreuil': 49.60,
    'aulnay-sous-bois': 30.69, 'aulnay sous bois': 30.69,
    'aubervilliers': 43.45,
    'bobigny': 49.92,
    'pantin': 46.31,
    'bondy': 46.72,
    'épinay-sur-seine': 43.30, 'epinay-sur-seine': 43.30, 'épinay sur seine': 43.30, 'epinay sur seine': 43.30,
    'noisy-le-grand': 38.97, 'noisy le grand': 38.97,
    'stains': 50.42,
    'pierrefitte-sur-seine': 47.84, 'pierrefitte sur seine': 47.84,
    'villepinte': 48.84,
    'sevran': 48.12,
    'livry-gargan': 39.81, 'livry gargan': 39.81,
    'rosny-sous-bois': 42.44, 'rosny sous bois': 42.44,

    // 94 — Val-de-Marne
    'créteil': 44.56, 'creteil': 44.56,
    'vitry-sur-seine': 45.98, 'vitry sur seine': 45.98,
    'champigny-sur-marne': 46.75, 'champigny sur marne': 46.75,
    'saint-maur-des-fossés': 35.42, 'saint-maur-des-fosses': 35.42,
    'saint maur des fossés': 35.42, 'saint maur des fosses': 35.42,
    'ivry-sur-seine': 42.91, 'ivry sur seine': 42.91,
    'villejuif': 39.83,
    'maisons-alfort': 31.45, 'maisons alfort': 31.45,
    'fontenay-sous-bois': 43.47, 'fontenay sous bois': 43.47,
    'vincennes': 22.85,
    'alfortville': 43.90,
    'choisy-le-roi': 46.31, 'choisy le roi': 46.31,
    'le perreux-sur-marne': 31.77, 'le perreux sur marne': 31.77,
    'villeneuve-saint-georges': 52.18, 'villeneuve saint georges': 52.18,
    'nogent-sur-marne': 34.22, 'nogent sur marne': 34.22,
    "l'haÿ-les-roses": 42.39, "l'hay-les-roses": 42.39,
    "l haÿ les roses": 42.39, "l hay les roses": 42.39,
};

// Base locative ≈ superficie × 110 €/m² × abattement 50% × taux communal
// (110 €/m² est une valeur réaliste pour l'Île-de-France; la vraie VLC figure sur l'avis fiscal)
export function estimateTF(superficie, taux) {
    if (!superficie || !taux) return null;
    return Math.round(superficie * 110 * 0.5 * (taux / 100));
}
