# Hanooty — Design System & Screens

## Brand Identity

Hanooty est une marketplace B2B où chaque boutique a sa propre identité visuelle. Le design doit être **professionnel, moderne, épuré** avec une dominante **violet/indigo** (#3525cd primary, #4f46e5 primaryContainer).

## Design Tokens

```json
{
  "colors": {
    "primary": "#3525cd",
    "primaryContainer": "#4f46e5",
    "onPrimary": "#ffffff",
    "secondary": "#505f76",
    "secondaryContainer": "#d0e1fb",
    "background": "#fcf8ff",
    "surface": "#ffffff",
    "surfaceContainer": "#f0ecf9",
    "surfaceContainerHigh": "#eae6f4",
    "text": "#1b1b24",
    "textMuted": "#464555",
    "outline": "#c7c4d8",
    "error": "#ba1a1a",
    "success": "#047857",
    "amber": "#b45309"
  },
  "fonts": {
    "family": "Inter",
    "headings": "Inter, 600",
    "body": "Inter, 400",
    "small": "Inter, 400, 0.875rem"
  },
  "radius": {
    "sm": "6px",
    "md": "10px",
    "lg": "16px",
    "xl": "24px",
    "full": "9999px"
  },
  "shadows": {
    "card": "0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)",
    "modal": "0 20px 60px rgba(0,0,0,0.12)",
    "elevated": "0 4px 12px rgba(0,0,0,0.08)"
  }
}
```

---

## Screens à générer (Stitch prompts)

---

### FRONT OFFICE — Pages publiques

#### 1. Listing des boutiques (marketplace)
```
Créer une page d'accueil marketplace professionnelle.

HEADER:
- Logo "Hanooty" à gauche, navigation: Boutiques, Assistant, Connexion
- Barre de recherche avec icône de loupe, placeholder "Rechercher une boutique..."

HERO SECTION:
- Titre: "Découvrez nos boutiques partenaires"
- Sous-titre: "Des produits et services de qualité sélectionnés pour vous"
- Bouton CTA: "Explorer les boutiques" (primary #3525cd)

BOUTIQUE GRID:
- Grille responsive 3 colonnes (desktop), 2 (tablette), 1 (mobile)
- Chaque carte de boutique:
  - Image/logo arrondie en haut
  - Nom de la boutique en gras
  - Slug/catégorie en texte muted
  - Badge de statut "Ouvert" ou "Fermé"
  - Bouton "Voir la boutique"
  - La carte utilise la couleur primaire de la boutique comme accent (border-top ou tag)

FOOTER:
- Liens: À propos, Contact, CGV, Confidentialité
- Copyright Hanooty

COULEURS: Fond #fcf8ff, cartes #ffffff, texte #1b1b24
TYPO: Titres Inter 600, Corps Inter 400
```

#### 2. Page boutique (storefront)
```
Créer la page vitrine d'une boutique individuelle avec header personnalisé.

BANNER/HEADER:
- Grande zone d'en-tête avec la couleur primaire de la boutique en fond
- Logo de la boutique centré ou aligné à gauche
- Nom de la boutique, description courte
- Boutons: "Nous contacter", "Voir les produits"
- Navigation secondaire: Accueil, Produits, Offres, Fidélité

SECTION PRODUITS:
- Barre de filtres: catégorie (dropdown), prix min/max, tri
- Grille de produits 3-4 colonnes
- Chaque carte produit:
  - Image produit (ratio 1:1)
  - Nom du produit
  - Prix en €
  - État stock: "En stock" (vert) ou "Rupture" (rouge)
  - Bouton "Ajouter au panier"

PANEL LATÉRAL (desktop):
- Mini panier récapitulatif
- Total et bouton "Voir le panier"

COULEURS: Boutique personnalisables via variables CSS
```

#### 3. Système de devis (Quoter)
```
Créer un formulaire de demande de devis en plusieurs étapes.

ÉTAPE 1 — Sélection produits:
- Liste des produits de la boutique avec cases à cocher
- Quantité pour chaque produit sélectionné (input numérique)
- Résumé en temps réel: nombre d'articles, estimation

ÉTAPE 2 — Informations client:
- Champs: Nom, Email, Téléphone, Société
- Message optionnel (textarea)
- Upload de fichiers (devis, cahier des charges)

ÉTAPE 3 — Récapitulatif & confirmation:
- Tableau récapitulatif: produit, quantité, prix unitaire, total
- Mention "Ceci est une estimation. Le vendeur vous contactera."
- Bouton "Envoyer la demande"

ÉTAT FINAL:
- Écran de confirmation: "Votre demande a été envoyée"
- Numéro de devis généré
- "Un commercial vous répondra sous 48h"

BARRE DE PROGRESSION en haut avec les 3 étapes.
COULEURS: Progress step actif en primary, complété en success, futur en outline.
```

#### 4. Panier
```
Créer la page panier d'une boutique.

EN-TÊTE:
- Titre "Mon panier" avec nombre d'articles
- Bouton "Continuer mes achats" (lien retour boutique)

LISTE DES ARTICLES:
- Image produit (petite vignette)
- Nom, SKU, Prix unitaire
- Quantité (input +/- avec boutons)
- Sous-total par ligne
- Bouton supprimer (icône poubelle)

RÉCAPITULATIF (colonne droite):
- Sous-total
- Frais de livraison (ou "Calculés à l'étape suivante")
- Total
- Bouton "Commander" (primary, large, full-width)
- Bouton "Demander un devis" (secondary)

ÉTAT VIDE:
- Illustration ou icône de panier vide
- "Votre panier est vide"
- Bouton "Découvrir nos produits"

MOBILE: Layout empilé, récapitulatif en bas avec fixed bottom.
```

#### 5. Checkout / Paiement
```
Créer la page de finalisation de commande.

SECTIONS (accordéon ou étapes visuelles):

1. LIVRAISON:
  - Champs: Adresse, Ville, Code postal, Pays
  - Option "Utiliser l'adresse du compte"

2. MODE DE LIVRAISON:
  - Cartes sélectionnables: Livraison standard (3-5j, gratuit), Express (24h, 9.90€)
  - Chaque carte: icône, nom, délai, prix

3. PAIEMENT:
  - Cartes de paiement (Carte bancaire, Virement)
  - Si CB: champ numéro carte, date exp., CVV stylisés
  - Si virement: IBAN et RIB affichés

4. CONFIRMATION:
  - Bouton "Confirmer et payer" (primary, large)
  - Case à cocher "J'accepte les conditions générales"
  - Récapitulatif de la commande

COLONNE LATÉRALE DROITE:
- Résumé de commande figé (scroll)
- Articles, total, livraison
```

#### 6. Confirmation de commande
```
Créer la page de succès après paiement.

CONTENU PRINCIPAL:
- Icône de vérification/coche verte (#047857) grande
- Titre: "Commande confirmée !"
- Numéro de commande: #ORD-XXXXX
- Message: "Un email de confirmation a été envoyé à votre adresse."

DÉTAILS:
- Tableau des articles commandés
- Mode de livraison
- Adresse de livraison
- Total payé

SUIVI:
- Barre de progression: Confirmée → Préparée → Expédiée → Livrée
- Statut actuel mis en évidence

ACTIONS:
- Bouton "Suivre ma commande"
- Bouton "Retourner à la boutique"

COULEURS: Barre de suivi: complété en primary, futur en outline.
```

---

### BACK OFFICE — Pages d'administration

#### 7. Dashboard admin
```
Créer le tableau de bord back-office.

BARRE LATÉRALE GAUCHE (260px):
- Logo Hanooty en haut
- Menu de navigation avec icônes:
  - Dashboard (actif) 🏠
  - Boutiques 🏪
  - Commandes 📦
  - Produits 🛒
  - Clients 👥
  - Marketing 📊
  - Chat 💬
  - Paramètres ⚙️
- Bas de sidebar: profil utilisateur (avatar, nom, email)

CONTENU PRINCIPAL:
- Top bar: titre "Dashboard", icône notification, avatar
- 4 cartes de métriques (KPI) en ligne:
  - "Boutiques actives" — 12 (+2) [icône store]
  - "Commandes du jour" — 45 (+8%) [icône shopping]
  - "Revenu mensuel" — 12 450 € (+12%) [icône euro]
  - "Nouveaux clients" — 89 (+5%) [icône users]

GRAPHIQUES:
- Graphique d'évolution des ventes (7 derniers jours)
- Graphique en secteurs: répartition par catégorie

TABLEAU RÉCENT:
- Dernières commandes: N°, Client, Montant, Statut, Date
- Statuts avec badges colorés: Payé (vert), Expédié (bleu), Livré (gris), Annulé (rouge)
```

#### 8. Gestion des commandes (détail)
```
Créer la page détail d'une commande pour l'admin.

EN-TÊTE:
- Numéro de commande: #ORD-4521
- Statut: badge coloré
- Date de commande
- Bouton "Imprimer" et "..." (menu actions)

SECTIONS TABS ou COLONNES:

INFORMATIONS CLIENT (carte):
- Nom, Email, Téléphone
- Adresse de livraison complète

ARTICLES COMMANDÉS (tableau):
- Image, Produit, SKU, Qté, Prix unitaire, Total
- Sous-total, Livraison, Total général

LIVRAISON (carte):
- Statut livraison: "Expédié" avec tracking
- Numéro de suivi: lien cliquable
- Date d'expédition et date estimée de livraison

HISTORIQUE (timeline verticale):
- Commande passée — 15/03/2025 14:32
- Paiement confirmé — 15/03/2025 14:33
- En préparation — 16/03/2025 09:00
- Expédiée — 16/03/2025 14:00
- Livrée — (à venir)

ACTIONS:
- Bouton "Marquer comme expédié" (si statut = payé)
- Bouton "Marquer comme livré" (si statut = expédié)
- Bouton "Rembourser" (si besoin)
```

#### 9. Abonnements boutique
```
Créer la page de gestion des abonnements.

EN-TÊTE:
- Titre "Abonnements"
- Filtres: Tous, Actifs, Expirés, En attente

TABLEAU:
- Colonnes: Boutique, Plan (Gratuit/3 mois/6 mois/1 an), Statut, Début, Fin, Accepté par, Actions
- Statuts avec badges: Actif (vert), En attente (orange), Expiré (rouge), Refusé (gris)

CARTE DÉTAIL D'UN ABONNEMENT (modal ou panneau latéral):
- Informations boutique (nom, email, téléphone)
- Plan choisi avec prix
- Période: date début → date fin
- Boutons d'action: "Accepter", "Refuser" (si en attente)

DEMANDES EN ATTENTE (section en haut si existantes):
- Mise en avant des abonnements pending
- Action rapide: Accepter/Refuser

COULEURS: En-tête de carte avec la couleur primaire de la boutique.
```

#### 10. Paramètres boutique
```
Créer la page des paramètres d'une boutique.

FORMULAIRE (divisé en sections visuelles):

INFORMATIONS GÉNÉRALES:
- Nom de la boutique (input text)
- Slug (input text, prérempli)
- Email de contact
- Téléphone
- Adresse (textarea)

APPARENCE:
- Couleur primaire (color picker)
- Couleur secondaire (color picker)
- Logo (upload avec preview)
- Thème (select: Moderne, Classique, Minimal)

LIVRAISON:
- Toggle "Utiliser API livraison"
- Si activé: champ URL de l'API (input URL)
- Délai de livraison estimé (jours)

RÉSEAUX SOCIAUX:
- Instagram, Facebook, Twitter (inputs avec préfixe @)

BOUTONS: "Enregistrer" (primary), "Annuler" (outline)
```

#### 11. Design system visuel
```
Créer la page du design system interne.

PALETTE DE COULEURS:
- Swatches avec noms: Primary, PrimaryContainer, Secondary, SecondaryContainer
- Background, Surface, Surface variants
- Error, Success, Amber
- Chaque swatch: couleur visible + code hex en dessous

TYPOGRAPHIE:
- Afficher Inter en différents poids/tailles:
  - Display: 32px Bold — titre
  - Heading: 24px SemiBold — sous-titre
  - Body: 16px Regular — texte courant
  - Small: 14px Regular — texte secondaire

COMPOSANTS:
- Boutons: Primary, Secondary, Outline, Ghost, Danger
  - États: normal, hover, disabled
- Inputs: text, search, select, textarea (normal, focus, error)
- Badges: success, warning, error, info, neutral
- Cartes: card simple, card with image, card interactive
- Modales: overlay + contenu centré
- Tableaux: en-tête, lignes, hover, pagination

ICONOGRAPHIE:
- Liste des icônes FontAwesome utilisées (shop, store, bag-shopping, users, etc.)
```

---

## Notes pour Stitch

1. **Design System first** — Appliquer le design system (couleurs, fonts, radius) avant de générer les écrans
2. **Consistency** — Tous les écrans doivent utiliser les mêmes composants (cards, buttons, inputs, badges)
3. **Responsive** — chaque écran doit être pensé desktop d'abord, avec adaptation mobile
4. **Accessibilité** — contrastes suffisants, labels visibles, états focus
5. **E-commerce UX** — parcours client fluide: découverte → sélection → panier → checkout → confirmation
6. **Admin UX** — tables triables, actions rapides, timeline visuelle, statuts colorés
