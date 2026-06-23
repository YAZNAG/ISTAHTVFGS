const statusColor = {
  draft: 'bg-slate-100 text-slate-700',
  info: 'bg-cyan-50 text-istaht-blue',
  waiting: 'bg-amber-50 text-amber-800',
  partial: 'bg-orange-50 text-orange-800',
  success: 'bg-green-50 text-istaht-green',
  danger: 'bg-red-50 text-istaht-red',
  neutral: 'bg-slate-100 text-slate-700',
}

const getMarcheStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: statusColor.info }
    case 'attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.waiting }
    case 'livre_partiellement':
      return { label: 'Livré partiellement', color: statusColor.partial }
    case 'livre_completement':
      return { label: 'Livré complètement', color: statusColor.success }
    case 'annule':
      return { label: 'Annulé', color: statusColor.danger }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getDemandeStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créée', color: statusColor.info }
    case 'en_attente_validation':
      return { label: 'En attente de validation', color: statusColor.waiting }
    case 'en_attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.info }
    case 'validee':
      return { label: 'Validée', color: statusColor.success }
    case 'livree':
      return { label: 'Livrée', color: statusColor.success }
    case 'annulee':
      return { label: 'Annulée', color: statusColor.danger }
    case 'rejetee':
      return { label: 'Rejetée', color: statusColor.danger }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getDemandeTypeInfo = (type) => {
  switch (type) {
    case 'collectivite':
      return { label: 'Collectivité', color: statusColor.info }
    case 'pedagogique':
      return { label: 'Pédagogique', color: statusColor.info }
    case 'restaurant':
      return { label: 'Restaurant', color: statusColor.waiting }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getSortieStatutInfo = (statut) => {
  switch (statut) {
    case 'attente_validation':
      return { label: "En attente d'approbation", color: statusColor.waiting }
    case 'attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.info }
    case 'valide':
      return { label: 'Validée', color: statusColor.success }
    case 'livree':
      return { label: 'Livrée', color: statusColor.success }
    case 'annulee':
      return { label: 'Annulée', color: statusColor.danger }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getSortieTypeInfo = (type) => {
  switch (type) {
    case 'vente':
      return { label: 'Vente', color: statusColor.success }
    case 'transfert':
      return { label: 'Transfert', color: statusColor.info }
    case 'perte':
      return { label: 'Perte', color: statusColor.danger }
    case 'ajustement':
      return { label: 'Ajustement', color: statusColor.waiting }
    case 'demande':
      return { label: 'Demande', color: statusColor.info }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getBonCommandeStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: statusColor.info }
    case 'attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.waiting }
    case 'livre_completement':
      return { label: 'Livré complètement', color: statusColor.success }
    case 'livre_partiellement':
      return { label: 'Livré partiellement', color: statusColor.partial }
    case 'annule':
      return { label: 'Annulé', color: statusColor.danger }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getChefCommandeStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: statusColor.info }
    case 'en_attente_validation':
      return { label: "En attente d'approbation", color: statusColor.waiting }
    case 'en_attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.partial }
    case 'livre_completement':
      return { label: 'Livré complètement', color: statusColor.success }
    case 'livre_partiellement':
      return { label: 'Livré partiellement', color: statusColor.info }
    case 'rejet':
      return { label: 'Rejeté', color: statusColor.danger }
    case 'annulee':
      return { label: 'Annulé', color: statusColor.danger }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

const getBonLivraisonInfo = (type) => {
  switch (type) {
    case 'en_attente_livraison':
      return { label: 'En attente de livraison', color: statusColor.partial }
    case 'livree':
      return { label: 'Livrée', color: statusColor.success }
    default:
      return { label: 'Inconnu', color: statusColor.neutral }
  }
}

export {
  getBonLivraisonInfo,
  getDemandeStatutInfo,
  getDemandeTypeInfo,
  getSortieStatutInfo,
  getSortieTypeInfo,
  getBonCommandeStatutInfo,
  getChefCommandeStatutInfo,
}
