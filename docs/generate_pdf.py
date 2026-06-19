#!/usr/bin/env python3
"""Generate Hanooty user guide PDF."""

from fpdf import FPDF

FONT_DIR = '/usr/share/fonts/truetype/dejavu/'


class PDF(FPDF):
    def __init__(self):
        super().__init__(orientation='P', unit='mm', format='A4')
        self.set_auto_page_break(True, 20)
        self.tbl_w = []
        self.add_font('DJS', '', FONT_DIR + 'DejaVuSans.ttf', uni=True)
        self.add_font('DJS', 'B', FONT_DIR + 'DejaVuSans-Bold.ttf', uni=True)
        self.add_font('DJR', '', FONT_DIR + 'DejaVuSerif.ttf', uni=True)
        self.add_font('DJR', 'B', FONT_DIR + 'DejaVuSerif-Bold.ttf', uni=True)
        self.add_font('DJM', '', FONT_DIR + 'DejaVuSansMono.ttf', uni=True)
        self.add_font('DJM', 'B', FONT_DIR + 'DejaVuSansMono-Bold.ttf', uni=True)

    def header(self):
        if self.page_no() > 1:
            self.set_font('DJR', '', 8)
            self.set_text_color(130, 130, 130)
            self.cell(0, 8, 'Hanooty \u2014 Guide d\'utilisation', align='R')
            self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font('DJR', '', 8)
        self.set_text_color(150, 150, 150)
        self.cell(0, 10, f'Page {self.page_no()}/{{nb}}', align='C')

    def title_page(self):
        self.add_page()
        self.ln(60)
        self.set_font('DJS', 'B', 36)
        self.set_text_color(53, 37, 205)
        self.cell(0, 15, 'Hanooty', align='C')
        self.ln(16)
        self.set_font('DJS', '', 20)
        self.set_text_color(80, 95, 118)
        self.cell(0, 12, "Guide d'utilisation", align='C')
        self.ln(8)
        self.set_font('DJS', '', 14)
        self.cell(0, 10, 'Super Admin & Administrateurs Boutique', align='C')
        self.ln(30)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.5)
        mid = 105
        self.line(mid - 30, self.get_y(), mid + 30, self.get_y())
        self.ln(15)
        self.set_font('DJS', '', 11)
        self.set_text_color(100, 100, 100)
        self.cell(0, 7, 'Plateforme SaaS multi-boutique', align='C')
        self.ln(7)
        self.cell(0, 7, 'Gestion des ventes, stocks, livraisons et abonnements', align='C')
        self.ln(20)
        self.set_font('DJR', '', 9)
        self.cell(0, 7, 'Document genere le 25 juin 2026', align='C')

    def h1(self, title):
        self.add_page()
        self.set_font('DJS', 'B', 22)
        self.set_text_color(53, 37, 205)
        self.set_y(50)
        self.cell(0, 12, title, align='C')
        self.ln(8)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.6)
        self.line(65, self.get_y(), 145, self.get_y())
        self.ln(8)

    def h2(self, title):
        self.ln(4)
        self.set_font('DJS', 'B', 14)
        self.set_text_color(53, 37, 205)
        self.cell(0, 10, title)
        self.ln(6)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.3)
        self.line(10, self.get_y(), 200, self.get_y())
        self.ln(4)

    def h3(self, title):
        self.ln(3)
        self.set_font('DJS', 'B', 12)
        self.set_text_color(60, 60, 60)
        self.cell(0, 8, title)
        self.ln(6)

    def p(self, text):
        self.set_font('DJS', '', 10)
        self.set_text_color(50, 50, 50)
        self.multi_cell(0, 5.5, text)
        self.ln(2)

    def b(self, items):
        self.set_font('DJS', '', 10)
        self.set_text_color(50, 50, 50)
        for item in items:
            self.cell(6, 5.5, '')
            self.cell(5, 5.5, '\u2022 ')
            self.multi_cell(0, 5.5, item)
            self.ln(1)

    def code(self, text):
        self.ln(2)
        self.set_fill_color(245, 245, 250)
        self.set_font('DJM', '', 9)
        self.set_text_color(40, 40, 40)
        self.set_x(15)
        self.multi_cell(180, 5, text, fill=True, border=0)
        self.ln(2)

    def warn(self, text):
        self.ln(2)
        self.set_fill_color(255, 243, 205)
        self.set_text_color(133, 100, 4)
        self.set_font('DJS', 'B', 10)
        self.set_x(12)
        self.multi_cell(186, 5.5, '\u26a0 ' + text, fill=True)
        self.set_text_color(50, 50, 50)
        self.ln(2)

    def info(self, text):
        self.ln(2)
        self.set_fill_color(230, 245, 255)
        self.set_text_color(0, 80, 150)
        self.set_font('DJS', '', 9)
        self.set_x(12)
        self.multi_cell(186, 5, '\u2139 ' + text, fill=True)
        self.set_text_color(50, 50, 50)
        self.ln(2)

    def th(self, cols, widths):
        self.tbl_w = widths
        self.set_font('DJS', 'B', 9)
        self.set_fill_color(53, 37, 205)
        self.set_text_color(255, 255, 255)
        for i, col in enumerate(cols):
            self.cell(widths[i], 8, ' ' + col, border=1, fill=True, align='C')
        self.ln()

    def tr(self, cols, gray=False):
        w = self.tbl_w
        if gray:
            self.set_fill_color(245, 242, 255)
        self.set_font('DJS', '', 9)
        self.set_text_color(50, 50, 50)
        for i, col in enumerate(cols):
            a = 'L' if i == 0 else 'C'
            if gray:
                self.cell(w[i], 7, ' ' + col, border=1, fill=True, align=a)
            else:
                self.cell(w[i], 7, ' ' + col, border=1, align=a)
        self.ln()


def build():
    pdf = PDF()
    pdf.alias_nb_pages()
    pdf.title_page()

    pdf.h1('1. Presentation')
    pdf.p('Hanooty est une plateforme SaaS multi-boutique permettant de gerer des boutiques en ligne, leurs produits, commandes, stocks, livraisons, abonnements et programmes de fidelite.')

    pdf.h2('Roles et hierarchie')
    pdf.code('ROLE_SUPER_ADMIN -> ROLE_BOUTIQUE_ADMIN -> ROLE_CAISSIER -> ROLE_USER\n                                       -> ROLE_CUSTOMER')
    pdf.b([
        'Super Admin : pilotage global de la plateforme, acces a toutes les boutiques',
        'Boutique Admin : gestion d\'une ou plusieurs boutiques (produits, commandes, stocks)',
        'Caissier : caisse POS, gestion des ventes comptoir',
        'Client (Customer) : navigation, achat sur le front-office',
    ])

    pdf.h1('2. Dashboard')
    pdf.h3('Tableau de bord Super Admin')
    pdf.p('Chemin : /admin/super-admin-dashboard')
    pdf.b(['Vue d\'ensemble de la plateforme', 'Nombre de boutiques actives', 'Revenus totaux et repartition', 'Commandes recentes toutes boutiques confondues'])
    pdf.h3('Tableau de bord Boutique')
    pdf.p('Chemin : /admin/boutique-dashboard')
    pdf.b(['KPI de la boutique selectionnee', 'Commandes recentes', 'Performance commerciale'])
    pdf.h3('Dashboard Back-office')
    pdf.p('Chemin : /admin - Vue generale SaaS multi-boutique, acces rapide aux modules principaux.')

    pdf.h1('3. Gestion des Boutiques')
    pdf.h3('Creation et administration')
    pdf.p('Chemin : /admin/boutiques')
    pdf.b(['Creer une nouvelle boutique (nom, slug)', 'Activer/desactiver une boutique', 'Statuts disponibles : Brouillon, Publiee, Suspendue', 'Associer des administrateurs'])
    pdf.h3('Parametres boutique')
    pdf.p('Chemin : /admin/settings')
    pdf.b(['Identite : nom, domaine, email, telephone, adresse', 'Branding : logo, couleurs principales', 'Livraison : configuration des comptes transporteurs', 'Reseaux sociaux : Instagram, Facebook', 'Meta Pixel : identifiant Facebook Pixel pour suivi conversions (par boutique)', 'Notifications : email, alerte stock bas, rapport hebdo'])
    pdf.h3('Personnalisation Front-office')
    pdf.p('Chemin : /admin/theme')
    pdf.b(['Couleurs et theme visuel', 'Navigation et pages mises en avant', 'Categories en vedette et icones personnalisees'])

    pdf.h1('4. Gestion des Utilisateurs')
    pdf.h3('Utilisateurs boutique')
    pdf.p('Chemin : /admin/users')
    pdf.b(['Liste des administrateurs et caissiers', 'Creation et modification des comptes', 'Attribution des roles (admin, caissier)'])
    pdf.h3('Clients')
    pdf.p('Chemin : /admin/customers')
    pdf.b(['Comptes clients enregistres', 'Historique d\'achats', 'Segmentation client'])

    pdf.h1('5. Catalogue Produits')
    pdf.p('Le catalogue permet de gerer l\'ensemble des produits proposes a la vente, leur organisation par categories et les filtres de recherche.')
    pdf.b(['Produits (/admin/products) : creation, prix, images, descriptions, visibilite', 'Categories (/admin/categories) : organisation, hierarchie, arborescence', 'Filtres : filtres personnalises par boutique (tailles, couleurs, etc.)'])

    pdf.h1('6. Stock & Inventaire')
    pdf.h3('Inventaire')
    pdf.p('Chemin : /admin/product-inventory')
    pdf.b(['Tableau de bord des stocks', 'Stock critique et alertes de reapprovisionnement', 'Vue d\'ensemble des niveaux de stock'])
    pdf.h3('Mouvements de stock')
    pdf.p('Chemin : /admin/stock-movements')
    pdf.b(['Entrees (receptions fournisseur)', 'Sorties (ventes, retours)', 'Corrections et ajustements', 'Tracabilite complete'])

    pdf.h1('7. Commandes & Livraison')
    pdf.h2('Gestion des commandes')
    pdf.p('Chemin : /admin/orders')
    pdf.b(['Liste des commandes web et POS', 'Preparation et traitement', 'Statuts : Brouillon, Confirmee, Payee, Expediee, Livree, Annulee', 'Paiement securise'])
    pdf.h3('Detail de commande')
    pdf.p('Chemin : /admin/orders/ord-xxxx')
    pdf.b(['Vue detaillee avec timeline', 'Informations client et adresse de livraison', 'Articles commandes', 'Tracking et statut de livraison'])
    pdf.h3('Caisse POS')
    pdf.p('Chemin : /admin/pos')
    pdf.b(['Vente comptoir', 'Panier rapide', 'Encaissement local'])

    pdf.h2('Systeme de livraison')
    pdf.h3('Transporteurs')
    pdf.p('Chemin : /admin/delivery-companies (reserve au Super Admin)')
    pdf.b(['Configuration des societes de livraison disponibles', 'Endpoints API (authentification, soumission, tracking)', 'Activer/desactiver un transporteur'])
    pdf.h3('Comptes livraison')
    pdf.p('Chemin : /admin/delivery-accounts')
    pdf.b(['Identifiants cryptes par boutique et transporteur', 'Chiffrement AES-256-GCM', 'Verification des comptes (test de connexion)', 'Statut : verifie / non verifie / erreur', 'Activation/desactivation'])
    pdf.h3('Processus automatique')
    pdf.p('Les commandes sont soumises aux transporteurs automatiquement :\n1. Soumission : des qu\'une commande est payee, elle est envoyee au transporteur (cadence : 60s)\n2. Livraison : 60s apres soumission, la commande est marquee livree\n3. Reessai : les echecs de soumission sont retentes jusqu\'a 5 fois\n4. Tracking : le numero de suivi est stocke sur la commande')

    pdf.h1('8. Marketing')
    pdf.h3('Promotions')
    pdf.p('Chemin : /admin/promotions')
    pdf.b(['Creation de promotions globales ou par categorie', 'Types : reduction, offre speciale', 'Priorite metier et activation programmee'])
    pdf.h3('Fidelite')
    pdf.p('Chemin : /admin/loyalty')
    pdf.b(['Comptes fidelite clients', 'Attribution et gestion des points', 'Transactions de points'])
    pdf.h3('Sponsors')
    pdf.p('Chemin : /admin/sponsors')
    pdf.b(['Gestion des sponsors de la plateforme', 'Association sponsor / boutique', 'Visibilite et ordre d\'affichage'])

    pdf.h3('Meta Pixel')
    pdf.p('Le tracking Facebook Pixel est disponible a deux niveaux :')
    pdf.h3('Pixel boutique')
    pdf.p('Configure par le boutique admin dans /admin/settings. ID Pixel propre a chaque boutique, tire sur toutes les pages publiques.')
    pdf.h3('Pixel applicatif')
    pdf.p('Configure par le super admin dans /admin/super-admin-dashboard. ID Pixel global tire sur toutes les boutiques de la plateforme.')
    pdf.info('Les deux pixels sont initialises automatiquement cote front-end. La librairie Facebook Pixel est chargee dynamiquement lors de la premiere visite.')

    pdf.h1('9. Abonnements')
    pdf.p('Les plans disponibles permettent de souscrire a la plateforme pour une duree determinee.')
    pdf.th(['Plan', 'Duree', 'Prix'], [40, 40, 40])
    pdf.tr(['Gratuit', '1 mois', '0 EUR'])
    pdf.tr(['3 mois', '3 mois', '29,99 EUR'], True)
    pdf.tr(['6 mois', '6 mois', '49,99 EUR'])
    pdf.tr(['1 an', '12 mois', '89,99 EUR'], True)
    pdf.ln(4)
    pdf.h3('Gestion des abonnements')
    pdf.p('Chemin : /admin/subscriptions')
    pdf.b(['Creation d\'abonnement pour une boutique', 'Acceptation (valide l\'abonnement)', 'Statuts : En attente, Actif, Expire, Refuse', 'Dates de debut, fin et validation', 'Expiration automatique via cron'])
    pdf.warn('Seuls les Super Admins peuvent accepter les abonnements')

    pdf.h1('10. Chat & Assistance')
    pdf.h3('Messagerie temps reel')
    pdf.p('Chemin : /admin/chat')
    pdf.b(['Messages clients/professionnels', 'Historique des conversations', 'Notifications en temps reel (Mercure)'])
    pdf.h3('Configuration du Chatbot')
    pdf.p('Chemin : /admin/chatbot-config')
    pdf.b(['Configuration de l\'assistant automatique', 'Scenarios et reponses predefinies', 'Ton de l\'assistant', 'Regles de declenchement'])

    pdf.h1('11. Configuration Systeme')
    pdf.h3('Design System')
    pdf.p('Chemin : /admin/design-system')
    pdf.p('Tokens de design du back-office : couleurs, composants et regles visuelles. Permet la personnalisation de l\'interface d\'administration.')

    pdf.h1('12. API et Integrations')
    pdf.p('L\'API REST est accessible via /api/ avec authentification Bearer Token.')
    pdf.h3('Endpoints principaux')
    pdf.th(['Endpoint', 'Description', 'Acces'], [60, 70, 50])
    pdf.tr(['GET /api/boutiques', 'Liste publique (inclut metaPixelId)', 'Public'])
    pdf.tr(['GET /api/public/meta-pixel', 'ID Pixel applicatif', 'Public'], True)
    pdf.tr(['GET /api/boutiques/{slug}/products', 'Catalogue public', 'Public'])
    pdf.tr(['GET /api/admin/*', 'Administration', 'Admin'], True)
    pdf.tr(['GET/POST /api/admin/app-config', 'Config applicative', 'Super Admin'])
    pdf.tr(['POST /api/auth/login', 'Authentification', 'Public'], True)
    pdf.tr(['POST /api/auth/register', 'Inscription', 'Public'])
    pdf.ln(3)
    pdf.h3('Endpoints Livraison')
    pdf.code('GET    /api/delivery/companies\nGET    /api/boutiques/{id}/delivery-accounts\nPOST   /api/boutiques/{id}/delivery-accounts\nPATCH  /api/boutiques/{id}/delivery-accounts/{id}\nDELETE /api/boutiques/{id}/delivery-accounts/{id}\nPOST   /api/boutiques/{id}/delivery-accounts/{id}/verify')

    pdf.h1('13. Commandes Console')
    pdf.th(['Commande', 'Description', 'Frequence'], [65, 70, 45])
    pdf.tr(['app:create-super-admin', 'Creer un Super Admin', 'Manuelle'])
    pdf.tr(['app:subscription-expiry', 'Expirer abonnements', '1 min'], True)
    pdf.tr(['app:delivery-process', 'Soumettre commandes', '1 min'])
    pdf.tr(['app:delivery-retry', 'Reessayer soumissions', '1 min'], True)
    pdf.tr(['app:delivery-verify-accounts', 'Verifier comptes', 'Manuelle'])
    pdf.tr(['app:cleanup-old-data', 'Nettoyer donnees', 'Manuelle'], True)
    pdf.ln(4)
    pdf.h3('Creation d\'un Super Admin')
    pdf.code('php bin/console app:create-super-admin')
    pdf.p('Commande interactive qui guide la creation du premier administrateur de la plateforme.')

    pdf.h1('14. Infrastructure')
    pdf.p('La plateforme s\'appuie sur une stack technique moderne et conteneurisee.')
    pdf.h3('Stack technique')
    pdf.info('Backend : PHP 8.3 / Symfony 7 / API Platform 3\nFrontend : React 18 (TypeScript) avec Webpack Encore\nBase de donnees : PostgreSQL 16\nCache : Redis 7\nServeur : FrankenPHP / Caddy / Traefik\nTemps reel : Mercure\nInfrastructure : Docker / docker-compose')
    pdf.h3('Services')
    pdf.th(['Service', 'Port', 'Description'], [40, 40, 100])
    pdf.tr(['App', '8082', 'Application web'])
    pdf.tr(['Traefik', '8083/84', 'Reverse proxy'], True)
    pdf.tr(['PostgreSQL', '5432', 'Base de donnees'])
    pdf.tr(['Redis', '6379', 'Cache'], True)
    pdf.tr(['Mercure', 'interne', 'Notifications temps reel'])
    pdf.ln(4)
    pdf.h3('Taches cron')
    pdf.p('Les taches suivantes sont executees automatiquement chaque minute :')
    pdf.b(['app:subscription-expiry : expire les abonnements termines', 'app:delivery-process : soumet les nouvelles commandes aux transporteurs', 'app:delivery-retry : retente les soumissions echouees (max 5 tentatives)'])

    pdf.h1('15. Depannage')

    pdf.h3('Erreurs courantes')
    pdf.th(['Probleme', 'Cause possible', 'Solution'], [50, 60, 70])
    pdf.tr(['Compte livraison non verifie', 'Identifiants incorrects', 'Verifier login/password'])
    pdf.tr(['Abonnement expire', 'Plan gratuit arrive a terme', 'Souscrire nouveau plan'], True)
    pdf.tr(['Commande non soumise', 'Aucun compte transporteur actif', 'Configurer un compte livraison'])
    pdf.tr(['Stock negatif', 'Correction manuelle eronnee', 'Verifier mouvements stock'], True)
    pdf.tr(['Connexion impossible', 'Token expire ou invalide', 'Se reconnecter via /admin'])
    pdf.ln(5)

    pdf.h3('Securite')
    pdf.b([
        'Mots de passe hashes avec l\'algorithme par defaut de Symfony',
        'Identifiants transporteurs chiffres en AES-256-GCM',
        'Authentification API via Bearer Token',
        'Acces aux endpoints controle par hierarchie de roles',
        'Donnees sensibles jamais exposees dans les reponses API',
    ])

    out = '/home/databiz174/Documents/autobiz/market-shop/docs/guide_utilisateur.pdf'
    pdf.output(out)
    print(f'PDF generated: {out}')
    return out


if __name__ == '__main__':
    build()
