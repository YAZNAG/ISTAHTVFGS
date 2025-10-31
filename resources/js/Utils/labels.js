const getMarcheStatutInfo = (statut) => {
    
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: 'bg-blue-100 text-blue-800' }
    case 'attente_livraison':
      return { label: 'En attente livraison', color: 'bg-yellow-100 text-yellow-800' }
    case 'livre_partiellement':
      return { label: 'Livré partiellement', color: 'bg-orange-100 text-orange-800' }
    case 'livre_completement':
      return { label: 'Livré complètement', color: 'bg-green-100 text-green-800' }
    case 'annule':
      return { label: 'Annulé', color: 'bg-red-100 text-red-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

const getDemandeStatutInfo = (statut) => {
    
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: 'bg-purple-100 text-purple-800' }
    case 'en_attente_validation':
      return { label: 'En attente de validation', color: 'bg-yellow-100 text-yellow-800' }
    case 'en_attente_livraison':
      return { label: 'En attente de livraison', color: 'bg-blue-100 text-blue-800' }
    case 'livree':
      return { label: 'Livrée', color: 'bg-green-100 text-green-800' }
    case 'annulee':
      return { label: 'Annulé', color: 'bg-red-100 text-red-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

const getDemandeTypeInfo = (type) => {
  switch (type) {
    case 'vente':
      return { label: 'Vente', color: 'bg-green-100 text-green-800' }
    case 'transfert':
      return { label: 'Transfert', color: 'bg-blue-100 text-blue-800' }
    case 'perte':
      return { label: 'Perte', color: 'bg-red-100 text-red-800' }
    case 'ajustement':
      return { label: 'Ajustement', color: 'bg-yellow-100 text-yellow-800' }
    case 'demande':
      return { label: 'Demande', color: 'bg-purple-100 text-purple-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}


const getSortieStatutInfo = (statut) => {
    
  switch (statut) {
    // case 'cree':
    //   return { label: 'Créé', color: 'bg-purple-100 text-purple-800' }
    case 'attente_validation':
      return { label: 'En attente de validation', color: 'bg-yellow-100 text-yellow-800' }
    case 'attente_livraison':
      return { label: 'En attente de livraison', color: 'bg-blue-100 text-blue-800' }
    case 'livree':
      return { label: 'Livrée', color: 'bg-green-100 text-green-800' }
    case 'annulee':
      return { label: 'Annulé', color: 'bg-red-100 text-red-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

const getSortieTypeInfo = (type) => {
  switch (type) {
    case 'vente':
      return { label: 'Vente', color: 'bg-green-100 text-green-800' }
    case 'transfert':
      return { label: 'Transfert', color: 'bg-blue-100 text-blue-800' }
    case 'perte':
      return { label: 'Perte', color: 'bg-red-100 text-red-800' }
    case 'ajustement':
      return { label: 'Ajustement', color: 'bg-yellow-100 text-yellow-800' }
    case 'demande':
      return { label: 'Demande', color: 'bg-purple-100 text-purple-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

const getBonCommandeStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: 'bg-purple-100 text-purple-800' }
    case 'attente_livraison':
      return { label: 'En attente de livraison', color: 'bg-yellow-100 text-yellow-800' }
    case 'livre_completement':
      return { label: 'Livrée complètement', color: 'bg-green-100 text-green-800' }
    case 'livre_partiellement':
      return { label: 'Livrée partiellement', color: 'bg-blue-100 text-blue-800' }
    case 'annule':
      return { label: 'Annulé', color: 'bg-red-100 text-red-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

// en attente d’approbation, en attente de livraison, livré, annulé
const getChefCommandeStatutInfo = (statut) => {
  switch (statut) {
    case 'cree':
      return { label: 'Créé', color: 'bg-purple-100 text-purple-800' }
    case 'en_attente_validation':
      return { label: 'en attente d’approbation', color: 'bg-yellow-100 text-yellow-800' }
    case 'en_attente_livraison':
      return { label: 'En attente de livraison', color: 'bg-orange-100 text-orange-800' }
    case 'livre_completement':
      return { label: 'Livrée complètement', color: 'bg-green-100 text-green-800' }
    case 'livre_partiellement':
      return { label: 'Livrée partiellement', color: 'bg-blue-100 text-blue-800' }
    case 'annulee':
      return { label: 'Annulé', color: 'bg-red-100 text-red-800' }
    default:
      return { label: 'Inconnu', color: 'bg-gray-100 text-gray-800' }
  }
}

export { getDemandeStatutInfo, getDemandeTypeInfo, getSortieStatutInfo, getSortieTypeInfo, getBonCommandeStatutInfo, getChefCommandeStatutInfo }