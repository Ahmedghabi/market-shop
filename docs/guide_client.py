#!/usr/bin/env python3
"""Generate client/boutique-admin user guide PDF."""

from fpdf import FPDF

FONT_DIR = '/usr/share/fonts/truetype/dejavu/'


class PDF(FPDF):
    def __init__(self):
        super().__init__(orientation='P', unit='mm', format='A4')
        self.set_auto_page_break(True, 20)
        self.tbl_w = []
        self.add_font('S', '', FONT_DIR + 'DejaVuSans.ttf')
        self.add_font('S', 'B', FONT_DIR + 'DejaVuSans-Bold.ttf')
        self.add_font('R', '', FONT_DIR + 'DejaVuSerif.ttf')
        self.add_font('R', 'B', FONT_DIR + 'DejaVuSerif-Bold.ttf')
        self.add_font('M', '', FONT_DIR + 'DejaVuSansMono.ttf')

    def header(self):
        if self.page_no() > 1:
            self.set_font('R', '', 8)
            self.set_text_color(130, 130, 130)
            self.cell(0, 8, "Hanooti \u2014 Guide utilisateur", align='R')
            self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font('R', '', 8)
        self.set_text_color(150, 150, 150)
        self.cell(0, 10, f'Page {self.page_no()}/{{nb}}', align='C')

    def title_page(self):
        self.add_page()
        self.ln(50)
        self.set_font('S', 'B', 32)
        self.set_text_color(53, 37, 205)
        self.cell(0, 14, 'Hanooti', align='C')
        self.ln(14)
        self.set_font('S', '', 18)
        self.set_text_color(80, 95, 118)
        self.cell(0, 11, "Guide utilisateur", align='C')
        self.ln(8)
        self.set_font('S', '', 13)
        self.cell(0, 9, 'Boutique & Client', align='C')
        self.ln(25)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.5)
        m = 105
        self.line(m - 25, self.get_y(), m + 25, self.get_y())
        self.ln(12)
        self.set_font('S', '', 10)
        self.set_text_color(100, 100, 100)
        self.cell(0, 7, 'Naviguer, gerer sa boutique, passer commande', align='C')
        self.ln(7)
        self.cell(0, 7, "et suivre ses achats sur Hanooti", align='C')
        self.ln(18)
        self.set_font('R', '', 9)
        self.cell(0, 7, 'Document genere le 25 juin 2026', align='C')

    def h1(self, title):
        self.add_page()
        self.set_font('S', 'B', 20)
        self.set_text_color(53, 37, 205)
        self.set_y(50)
        self.cell(0, 11, title, align='C')
        self.ln(7)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.6)
        self.line(65, self.get_y(), 145, self.get_y())
        self.ln(7)

    def h2(self, title):
        self.ln(3)
        self.set_font('S', 'B', 14)
        self.set_text_color(53, 37, 205)
        self.cell(0, 9, title)
        self.ln(5)
        self.set_draw_color(53, 37, 205)
        self.set_line_width(0.3)
        self.line(10, self.get_y(), 200, self.get_y())
        self.ln(3)

    def h3(self, title):
        self.ln(2)
        self.set_font('S', 'B', 11)
        self.set_text_color(60, 60, 60)
        self.cell(0, 7, title)
        self.ln(5)

    def p(self, text):
        self.set_font('S', '', 10)
        self.set_text_color(50, 50, 50)
        self.multi_cell(0, 5.5, text)
        self.ln(2)

    def b(self, items):
        self.set_font('S', '', 10)
        self.set_text_color(50, 50, 50)
        for item in items:
            self.cell(6, 5.5, '')
            self.cell(5, 5.5, '\u2022 ')
            self.multi_cell(0, 5.5, item)
            self.ln(1)

    def code(self, text):
        self.ln(2)
        self.set_fill_color(245, 245, 250)
        self.set_font('M', '', 9)
        self.set_text_color(40, 40, 40)
        self.set_x(15)
        self.multi_cell(180, 5, text, fill=True, border=0)
        self.ln(2)

    def warn(self, text):
        self.ln(2)
        self.set_fill_color(255, 243, 205)
        self.set_text_color(133, 100, 4)
        self.set_font('S', 'B', 10)
        self.set_x(12)
        self.multi_cell(186, 5.5, '\u26a0 ' + text, fill=True)
        self.set_text_color(50, 50, 50)
        self.ln(2)

    def info(self, text):
        self.ln(2)
        self.set_fill_color(230, 245, 255)
        self.set_text_color(0, 80, 150)
        self.set_font('S', '', 9)
        self.set_x(12)
        self.multi_cell(186, 5, '\u2139 ' + text, fill=True)
        self.set_text_color(50, 50, 50)
        self.ln(2)

    def th(self, cols, widths):
        self.tbl_w = widths
        self.set_font('S', 'B', 9)
        self.set_fill_color(53, 37, 205)
        self.set_text_color(255, 255, 255)
        for i, col in enumerate(cols):
            self.cell(widths[i], 8, ' ' + col, border=1, fill=True, align='C')
        self.ln()

    def tr(self, cols, gray=False):
        w = self.tbl_w
        if gray:
            self.set_fill_color(245, 242, 255)
        self.set_font('S', '', 9)
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

    # ---- 1 ----
    pdf.h1('1. Introduction')
    pdf.p("Hanooti est une plateforme de commerce en ligne qui permet a chaque boutique de gerer son catalogue, ses commandes, ses stocks et sa relation client. Ce guide est destine aux administrateurs boutique, caissiers et clients.")
    pdf.info("Vous avez ete invite sur la plateforme par le Super Administrateur. Contactez-le si vous n'arrivez pas a vous connecter.")

    # ---- 2 ----
    pdf.h1('2. Premiers pas')
    pdf.h2('Connexion')
    pdf.p('Rendez-vous sur la page de connexion et saisissez votre email et mot de passe. Si vous n\'avez pas encore de compte, utilisez le lien "Creer un compte".')
    pdf.h2('Tableau de bord')
    pdf.p('Apres connexion, vous arrivez sur votre tableau de bord. Il resume l\'activite de votre boutique :')
    pdf.b(['Nombre de commandes recentes', 'Produits en stock critique', 'Performance commerciale', 'Acces rapide aux modules'])

    # ---- 3 ----
    pdf.h1('3. Gerer son catalogue')
    pdf.h2('Produits')
    pdf.p('Depuis la section Produits, vous pouvez :')
    pdf.b(['Ajouter un nouveau produit (nom, description, prix, images)', 'Modifier un produit existant', 'Gerer la visibilite (publie/brouillon)', 'Organiser les variantes (tailles, couleurs, etc.)'])
    pdf.h2('Categories')
    pdf.p('Les categories permettent d\'organiser votre catalogue pour que les clients trouvent facilement ce qu\'ils cherchent.')
    pdf.b(['Creer des categories (ex: Vetements, Accessoires)', 'Organiser la hierarchie (sous-categories)', 'Ajouter des categories en vedette sur la vitrine'])
    pdf.h2('Filtres')
    pdf.p('Configurez des filtres pour aider les clients a affiner leur recherche : taille, couleur, prix, etc.')

    # ---- 4 ----
    pdf.h1('4. Gerer les stocks')
    pdf.h2('Inventaire')
    pdf.p("La page Inventaire affiche tous vos produits avec leur quantite en stock. Les produits en stock critique sont signales pour vous alerter.")
    pdf.h2('Mouvements de stock')
    pdf.p('Chaque entree, sortie ou correction est tracee :')
    pdf.b(['Entree stock : reception de marchandise', 'Sortie stock : vente ou retour', 'Correction : ajustement manuel'])
    pdf.warn('Un stock negatif peut bloquer les ventes. Verifiez regulierement vos niveaux.')

    # ---- 5 ----
    pdf.h1('5. Gerer les commandes')
    pdf.h2('Liste des commandes')
    pdf.p("Depuis la section Commandes, consultez l'ensemble des commandes web et caisse.")
    pdf.h2('Statuts')
    pdf.th(['Statut', 'Description'], [50, 130])
    pdf.tr(['Brouillon', 'Commande en cours de creation'])
    pdf.tr(['Confirmee', 'Client a valide sa commande'], True)
    pdf.tr(['Payee', 'Paiement accepte, preparation en cours'])
    pdf.tr(['Expediee', 'Colis remis au transporteur'], True)
    pdf.tr(['Livree', 'Commande recue par le client'])
    pdf.tr(['Annulee', 'Commande annulee'], True)
    pdf.ln(4)
    pdf.h2('Detail d\'une commande')
    pdf.p("Sur la page de detail, retrouvez :")
    pdf.b(["Les informations client et l'adresse de livraison", 'Les articles commandes', 'La chronologie des evenements', 'Le numero de suivi (si expedie)'])

    # ---- 6 ----
    pdf.h1('6. Caisse POS')
    pdf.p('Le module Caisse permet de vendre en magasin (vente comptoir).')
    pdf.b(['Ajouter rapidement des produits au panier', 'Encaisser en especes, carte ou autre moyen', 'Imprimer ou envoyer le ticket par email'])

    # ---- 7 ----
    pdf.h1('7. Marketing & Fidelite')
    pdf.h2('Promotions')
    pdf.p('Creez des offres speciales pour booster vos ventes :')
    pdf.b(['Reduction sur un produit ou une categorie', 'Activation ou desactivation a tout moment', 'Priorite pour gerer les conflits entre offres'])
    pdf.h2('Fidelite')
    pdf.p('Le programme de fidelite permet d\'attribuer des points a vos clients.')
    pdf.b(['Les clients cumulent des points a chaque achat', 'Consultez l\'historique des transactions', 'Les points peuvent etre echanges contre des avantages'])

    # ---- 8 ----
    pdf.h1('8. Parametres boutique')
    pdf.p("Depuis la section Parametres, personnalisez votre boutique :")
    pdf.b(['Modifier le nom et le domaine', 'Changer le logo et les couleurs', 'Configurer les reseaux sociaux', 'Gerer les notifications (email, alertes stock)'])
    pdf.h2('Personnalisation du Front-office')
    pdf.p('La vitrine de votre boutique est entierement personnalisable :')
    pdf.b(['Couleurs et theme visuel', 'Pages mises en avant', 'Categories en vedette', 'Icones de navigation'])

    # ---- 9 ----
    pdf.h1('9. Messagerie & Chatbot')
    pdf.h2('Messagerie temps reel')
    pdf.p('Echangez avec vos clients en direct depuis la messagerie integree.')
    pdf.b(['Repondre aux questions des clients', 'Consulter l\'historique des conversations', 'Notifications en temps reel'])
    pdf.h2('Chatbot automatique')
    pdf.p("Configurez l'assistant automatique pour repondre aux questions frequentes :")
    pdf.b(['Scenarios et reponses predefinies', 'Regles de declenchement (horaire, mot-cle)', 'Ton de l\'assistant (formel, detendu)'])

    # ---- 10 ----
    pdf.h1('10. Comptes livraison')
    pdf.p("Pour expedier vos commandes, vous devez configurer au moins un compte transporteur.")
    pdf.b(["Saisissez vos identifiants de connexion au transporteur (Chronopost, DHL, etc.)", "Les mots de passe sont cryptes (AES-256-GCM)", "Cliquez sur 'Verifier' pour tester la connexion", "Activez le compte pour qu'il soit utilise automatiquement"])
    pdf.info("Les commandes payees sont automatiquement transmises au transporteur actif. En cas d'echec, le systeme reessaie jusqu'a 5 fois.")

    # ---- 11 ----
    pdf.h1('11. Front-office client')
    pdf.p("Les clients naviguent sur la plateforme publique sans avoir besoin de se connecter.")
    pdf.h2('Marketplace')
    pdf.p('La page d\'accueil presente toutes les boutiques actives. Le client peut les parcourir et decouvrir leurs produits.')
    pdf.h2('Vitrine boutique')
    pdf.p("Chaque boutique a sa propre vitrine avec son identite visuelle.")
    pdf.b(['Parcourir les produits par categorie', 'Utiliser les filtres de recherche', 'Voir les promotions en cours', 'Contacter la boutique via le chatbot'])
    pdf.h2('Fiche produit')
    pdf.p('Sur chaque produit, le client peut :')
    pdf.b(['Voir les photos et la description', 'Choisir une variante (taille, couleur)', 'Ajouter au panier', 'Demander un devis'])
    pdf.h2('Panier et commande')
    pdf.p('Le client peut consulter son panier, modifier les quantites et passer commande.')
    pdf.b(['Resume de la commande', 'Saisie de l\'adresse de livraison', 'Paiement securise', 'Confirmation avec numero de commande'])
    pdf.h2('Suivi de commande')
    pdf.p("Apres paiement, le client recoit un numero de commande et peut suivre l'avancement de sa livraison.")

    # ---- 12 ----
    pdf.h1('12. Depannage')
    pdf.th(['Probleme', 'Solution'], [55, 125])
    pdf.tr(['Mot de passe oublie', 'Contacter le Super Admin pour le reinitialiser'])
    pdf.tr(['Produit non visible', 'Verifier qu\'il est publie et en stock'], True)
    pdf.tr(['Commande bloquee', 'Verifier le statut et le paiement'])
    pdf.tr(['Stock negatif', 'Faire une correction de stock'], True)
    pdf.tr(['Livraison non envoyee', 'Verifier le compte transporteur et sa verification'])
    pdf.tr(['Notification non recue', 'Verifier les parametres de la boutique'], True)
    pdf.ln(4)
    pdf.h2('Support')
    pdf.p("Si vous rencontrez un probleme non resolu par ce guide, contactez le Super Administrateur de la plateforme.")

    # Save
    out = '/home/databiz174/Documents/autobiz/market-shop/docs/guide_client.pdf'
    pdf.output(out)
    print(f'PDF generated: {out}')
    return out


if __name__ == '__main__':
    build()
