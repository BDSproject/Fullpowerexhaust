<?php
/**
 * FULLPOWEREXHAUST — traduit le contenu intégral des 4 pages légales
 * (CGV, CGU, Politique de confidentialité, Mentions légales) en en/es/it.
 *
 * Usage : php -d memory_limit=768M tools/translate_legal_pages.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langIds = [
    'en' => (int) Language::getIdByIso('en'),
    'es' => (int) Language::getIdByIso('es'),
    'it' => (int) Language::getIdByIso('it'),
];

$pages = [
    'conditions-generales-de-vente' => [
        'en' => <<<'HTML'
<p>Last updated: 30 September 2026.</p>
<p>These General Terms and Conditions of Sale (GTCS) apply to all orders placed on the website fullpowerexhaust.com, operated by POWEREXHAUST, specialised in the online sale of used exhaust lines and exhaust parts for motor vehicles.</p>

<h2>Article 1 — Purpose and scope</h2>
<p>These GTCS govern the contractual relationship between POWEREXHAUST (hereinafter "the Seller") and any adult natural person or legal entity making a purchase on fullpowerexhaust.com (hereinafter "the Customer").</p>
<p>Any order placed on the website implies full and unreserved acceptance of these GTCS. The Customer acknowledges having read them before confirming the order. The applicable GTCS are those in force on the date of the order.</p>

<h2>Article 2 — Seller identification</h2>
<p>POWEREXHAUST — online sale of used exhaust parts.<br>Email: contact@power-exhaust.com<br>Website: fullpowerexhaust.com</p>
<p>Full company details (legal form, capital, registered office, SIREN, VAT) are provided in the <a href="{url entity='cms' id=2}">Legal notice</a>.</p>

<h2>Article 3 — Products: used parts</h2>
<p>All products offered for sale are <strong>used parts</strong> (complete exhaust lines, silencers, catalytic converters, manifolds, exhaust tips, etc.). Unless expressly stated otherwise, they are not new parts.</p>
<p>Each part is subject, before being put on sale, to a visual and functional inspection intended to check: general condition, presence of cracks or perforations, condition of fixings and flanges, and conformity with the published photographs.</p>
<p>The photographs and descriptions are as accurate as possible but are not contractually binding. Due to the nature of used parts, slight signs of wear (scratches, tarnishing, deposits) may remain. Parts are sold <strong>as is</strong>, in accordance with their description.</p>
<p>The Seller reserves the right to withdraw from sale any product deemed non-compliant, or to correct any obvious error in description or price.</p>

<h2>Article 4 — Vehicle compatibility</h2>
<p>It is the Customer's responsibility to check, before ordering, the compatibility of the part with their vehicle: make, model, engine, year, manufacturer reference (OEM reference) and the product reference shown on the listing.</p>
<p>In case of doubt, the Customer is invited to contact the Seller before purchase via contact@power-exhaust.com, providing the OEM reference of the part sought or the chassis number (VIN) of their vehicle.</p>
<p>A compatibility error not reported before purchase does not constitute a product defect and does not entitle the Customer to a refund under the legal conformity warranty.</p>

<h2>Article 5 — Price</h2>
<p>Prices are stated in euros, all taxes included (TTC), excluding delivery costs. Delivery costs are calculated and displayed before final confirmation of the order.</p>
<p>The Seller reserves the right to modify its prices at any time; products are invoiced on the basis of the price in force at the time the order is confirmed.</p>

<h2>Article 6 — Order</h2>
<p>The order is deemed concluded when the Customer validates their cart, accepts these GTCS and proceeds to payment. A confirmation email summarising the order is sent to the Customer. The Seller reserves the right to refuse or cancel any order in the event of a prior dispute, irregular payment or suspected fraud.</p>

<h2>Article 7 — Payment</h2>
<p>Payment is made online by the means offered on the website (bank card, PayPal, and any other means indicated at the time of ordering). Transactions are secured by certified providers; the Seller retains no bank data.</p>
<p>The order is processed only upon receipt of full payment. In the event of payment refusal, the order is automatically cancelled.</p>

<h2>Article 8 — Delivery and receipt</h2>
<p>Products are dispatched to the address indicated by the Customer when ordering. Delivery times are indicative; dispatch normally occurs within 3 to 5 working days after payment validation.</p>
<p>The Customer must check the condition of the parcel upon receipt. In the event of damage, they must make precise reservations with the carrier and inform the Seller within 48 hours, with supporting photographs.</p>

<h2>Article 9 — Right of withdrawal</h2>
<p>In accordance with Articles L.221-18 et seq. of the French Consumer Code, the Customer has a period of <strong>14 calendar days</strong> from receipt of the product to exercise their right of withdrawal, without having to give any reason.</p>
<p>To exercise it, the Customer must notify their decision in writing (email to contact@power-exhaust.com) before the expiry of the period. The part must be returned <strong>unfitted, unmodified and in its original condition</strong>, in its packaging. Return costs are borne by the Customer, unless otherwise provided in the return conditions.</p>
<p>The refund is made within 14 days from recovery of the part or proof of shipment, via the original payment method.</p>

<h2>Article 10 — Legal warranties</h2>
<p><strong>Legal warranty of conformity</strong> (Articles L.217-3 to L.217-14 of the French Consumer Code): the Customer has a period of 2 years from delivery to act. For used goods, this period may be reduced to <strong>12 months</strong> by agreement between the parties. In the event of a lack of conformity, the Customer may obtain repair, replacement or, failing that, a price reduction or cancellation of the sale.</p>
<p><strong>Warranty against hidden defects</strong> (Articles 1641 to 1649 of the French Civil Code): the Seller is liable for hidden defects of the item sold which render it unfit for its intended use. The action must be brought within 2 years from discovery of the defect.</p>
<p>These warranties do not cover: normal wear and tear, visible defects mentioned in the description, damage resulting from improper installation, unsuitable use or failure to comply with the manufacturer's recommendations.</p>

<h2>Article 11 — Liability and installation</h2>
<p>Exhaust parts must be fitted by a qualified professional, in compliance with current standards (particularly regarding emissions and noise). The Seller declines all liability in the event of non-compliant installation, modification of the part, or use of the vehicle on public roads in breach of applicable regulations.</p>
<p>The Seller's liability is limited to the amount of the order; it cannot be held liable for indirect or immaterial damage.</p>

<h2>Article 12 — Complaints and after-sales service</h2>
<p>Any complaint must be sent by email to contact@power-exhaust.com, stating the order number and attaching photographs. The Seller undertakes to respond within a reasonable time.</p>

<h2>Article 13 — Mediation and disputes</h2>
<p>In the event of a dispute, the Customer is invited to contact customer service first in order to seek an amicable solution. In accordance with Article L.612-1 of the French Consumer Code, the Customer may have free recourse to a consumer mediator with a view to the amicable resolution of the dispute.</p>
<p>Failing an amicable agreement, the dispute will be brought before the competent French courts. These GTCS are governed by French law.</p>
HTML,
        'es' => <<<'HTML'
<p>Última actualización: 30 de septiembre de 2026.</p>
<p>Las presentes Condiciones Generales de Venta (CGV) se aplican a todos los pedidos realizados en el sitio web fullpowerexhaust.com, operado por POWEREXHAUST, especializado en la venta en línea de líneas de escape y piezas de escape de ocasión para vehículos.</p>

<h2>Artículo 1 — Objeto y ámbito de aplicación</h2>
<p>Las presentes CGV rigen la relación contractual entre POWEREXHAUST (en adelante, «el Vendedor») y toda persona física mayor de edad o jurídica que realice una compra en fullpowerexhaust.com (en adelante, «el Cliente»).</p>
<p>Todo pedido realizado en el sitio implica la aceptación plena, entera y sin reservas de las presentes CGV. El Cliente reconoce haberlas leído antes de validar su pedido. Las CGV aplicables son las vigentes en la fecha del pedido.</p>

<h2>Artículo 2 — Identificación del Vendedor</h2>
<p>POWEREXHAUST — venta en línea de piezas de escape de ocasión.<br>Correo electrónico: contact@power-exhaust.com<br>Sitio web: fullpowerexhaust.com</p>
<p>Los datos completos de la empresa (forma jurídica, capital, domicilio social, SIREN, IVA) figuran en el <a href="{url entity='cms' id=2}">Aviso legal</a>.</p>

<h2>Artículo 3 — Productos: piezas de ocasión</h2>
<p>Todos los productos ofrecidos a la venta son <strong>piezas de ocasión</strong> (líneas de escape completas, silenciosos, catalizadores, colectores, salidas de escape, etc.). Salvo mención expresa en contrario, no son piezas nuevas.</p>
<p>Cada pieza es objeto, antes de su puesta a la venta, de un control visual y funcional destinado a verificar: el estado general, la presencia de fisuras o perforaciones, el estado de las fijaciones y bridas, y la conformidad con las fotografías publicadas.</p>
<p>Las fotografías y descripciones son lo más fieles posible pero no tienen valor contractual. Debido a la naturaleza de las piezas de ocasión, pueden subsistir ligeras huellas de uso (arañazos, decoloración, depósitos). Las piezas se venden <strong>en el estado en que se encuentran</strong>, conforme a su descripción.</p>
<p>El Vendedor se reserva el derecho de retirar de la venta cualquier producto considerado no conforme, o de corregir cualquier error manifiesto de descripción o de precio.</p>

<h2>Artículo 4 — Compatibilidad con el vehículo</h2>
<p>Corresponde al Cliente verificar, antes de cualquier pedido, la compatibilidad de la pieza con su vehículo: marca, modelo, motorización, año, referencia del fabricante (referencia OEM) y referencia del producto indicada en la ficha.</p>
<p>En caso de duda, se invita al Cliente a contactar con el Vendedor antes de la compra a través de contact@power-exhaust.com, comunicando la referencia OEM de la pieza buscada o el número de bastidor (VIN) de su vehículo.</p>
<p>Un error de compatibilidad no señalado antes de la compra no constituye un defecto del producto y no da derecho a reembolso en virtud de la garantía legal de conformidad.</p>

<h2>Artículo 5 — Precio</h2>
<p>Los precios se indican en euros, impuestos incluidos (TTC), sin incluir los gastos de envío. Los gastos de envío se calculan y muestran antes de la validación definitiva del pedido.</p>
<p>El Vendedor se reserva el derecho de modificar sus precios en cualquier momento; los productos se facturan según la tarifa vigente en el momento de la validación del pedido.</p>

<h2>Artículo 6 — Pedido</h2>
<p>El pedido se considera formalizado cuando el Cliente valida su carrito, acepta las presentes CGV y procede al pago. Se envía al Cliente un correo electrónico de confirmación con el resumen del pedido. El Vendedor se reserva el derecho de rechazar o anular cualquier pedido en caso de litigio anterior, pago irregular o sospecha de fraude.</p>

<h2>Artículo 7 — Pago</h2>
<p>El pago se realiza en línea por los medios ofrecidos en el sitio (tarjeta bancaria, PayPal y cualquier otro medio indicado en el momento del pedido). Las transacciones están aseguradas por proveedores certificados; el Vendedor no conserva ningún dato bancario.</p>
<p>El pedido solo se tramita tras la recepción del pago íntegro. En caso de rechazo del pago, el pedido se anula de pleno derecho.</p>

<h2>Artículo 8 — Entrega y recepción</h2>
<p>Los productos se envían a la dirección indicada por el Cliente al realizar el pedido. Los plazos de entrega son orientativos; el envío se realiza en principio en un plazo de 3 a 5 días laborables tras la validación del pago.</p>
<p>El Cliente debe verificar el estado del paquete en el momento de la recepción. En caso de daños, deberá formular reservas precisas ante el transportista e informar al Vendedor en un plazo de 48 horas, adjuntando fotografías.</p>

<h2>Artículo 9 — Derecho de desistimiento</h2>
<p>De conformidad con los artículos L.221-18 y siguientes del Código de Consumo francés, el Cliente dispone de un plazo de <strong>14 días naturales</strong> desde la recepción del producto para ejercer su derecho de desistimiento, sin necesidad de justificar su decisión.</p>
<p>Para ejercerlo, el Cliente debe notificar su decisión por escrito (correo electrónico a contact@power-exhaust.com) antes de que expire el plazo. La pieza debe devolverse <strong>sin montar, sin modificar y en su estado original</strong>, en su embalaje. Los gastos de devolución corren a cargo del Cliente, salvo disposición contraria de las condiciones de devolución.</p>
<p>El reembolso se realiza en un plazo de 14 días desde la recuperación de la pieza o la prueba de envío, mediante el medio de pago original.</p>

<h2>Artículo 10 — Garantías legales</h2>
<p><strong>Garantía legal de conformidad</strong> (artículos L.217-3 a L.217-14 del Código de Consumo francés): el Cliente dispone de un plazo de 2 años desde la entrega para actuar. Para los bienes de ocasión, este plazo puede reducirse a <strong>12 meses</strong> por acuerdo entre las partes. En caso de falta de conformidad, el Cliente puede obtener la reparación, la sustitución o, en su defecto, la reducción del precio o la resolución de la venta.</p>
<p><strong>Garantía por vicios ocultos</strong> (artículos 1641 a 1649 del Código Civil francés): el Vendedor responde de los vicios ocultos de la cosa vendida que la hagan impropia para el uso al que se destina. La acción debe ejercitarse en un plazo de 2 años desde el descubrimiento del vicio.</p>
<p>Estas garantías no cubren: el desgaste normal, los defectos visibles mencionados en la descripción, los daños derivados de una instalación incorrecta, de un uso inadecuado o del incumplimiento de las recomendaciones del fabricante.</p>

<h2>Artículo 11 — Responsabilidad e instalación</h2>
<p>El montaje de las piezas de escape debe ser realizado por un profesional cualificado, respetando las normas vigentes (en particular en materia de emisiones y ruido). El Vendedor declina toda responsabilidad en caso de instalación no conforme, modificación de la pieza o uso del vehículo en vía pública en infracción de la normativa aplicable.</p>
<p>La responsabilidad del Vendedor se limita al importe del pedido; no responderá de los daños indirectos o inmateriales.</p>

<h2>Artículo 12 — Reclamaciones y servicio posventa</h2>
<p>Toda reclamación debe dirigirse por correo electrónico a contact@power-exhaust.com, indicando el número de pedido y adjuntando fotografías. El Vendedor se compromete a responder en un plazo razonable.</p>

<h2>Artículo 13 — Mediación y litigios</h2>
<p>En caso de litigio, se invita al Cliente a contactar previamente con el servicio de atención al cliente para buscar una solución amistosa. De conformidad con el artículo L.612-1 del Código de Consumo francés, el Cliente puede recurrir gratuitamente a un mediador de consumo para la resolución amistosa del litigio.</p>
<p>A falta de acuerdo amistoso, el litigio se someterá a los tribunales franceses competentes. Las presentes CGV se rigen por el derecho francés.</p>
HTML,
        'it' => <<<'HTML'
<p>Ultimo aggiornamento: 30 settembre 2026.</p>
<p>Le presenti Condizioni Generali di Vendita (CGV) si applicano a tutti gli ordini effettuati sul sito fullpowerexhaust.com, gestito da POWEREXHAUST, specializzato nella vendita online di linee di scarico e parti di scarico usate per veicoli.</p>

<h2>Articolo 1 — Oggetto e ambito di applicazione</h2>
<p>Le presenti CGV disciplinano il rapporto contrattuale tra POWEREXHAUST (di seguito «il Venditore») e qualsiasi persona fisica maggiorenne o giuridica che effettui un acquisto su fullpowerexhaust.com (di seguito «il Cliente»).</p>
<p>Qualsiasi ordine effettuato sul sito implica l'accettazione piena, intera e senza riserve delle presenti CGV. Il Cliente riconosce di averne preso visione prima della convalida dell'ordine. Le CGV applicabili sono quelle in vigore alla data dell'ordine.</p>

<h2>Articolo 2 — Identificazione del Venditore</h2>
<p>POWEREXHAUST — vendita online di parti di scarico usate.<br>Email: contact@power-exhaust.com<br>Sito: fullpowerexhaust.com</p>
<p>I dati completi della società (forma giuridica, capitale, sede legale, SIREN, IVA) sono riportati nelle <a href="{url entity='cms' id=2}">Note legali</a>.</p>

<h2>Articolo 3 — Prodotti: parti usate</h2>
<p>Tutti i prodotti offerti in vendita sono <strong>parti usate</strong> (linee di scarico complete, silenziatori, catalizzatori, collettori, terminali di scarico, ecc.). Salvo espressa indicazione contraria, non si tratta di parti nuove.</p>
<p>Ogni parte è sottoposta, prima della messa in vendita, a un controllo visivo e funzionale volto a verificare: lo stato generale, la presenza di crepe o fori, lo stato di fissaggi e flange e la conformità alle fotografie pubblicate.</p>
<p>Le fotografie e le descrizioni sono il più possibile fedeli ma non hanno valore contrattuale. A causa della natura delle parti usate, possono permanere lievi segni di utilizzo (graffi, opacità, depositi). Le parti sono vendute <strong>nello stato in cui si trovano</strong>, conformemente alla loro descrizione.</p>
<p>Il Venditore si riserva il diritto di ritirare dalla vendita qualsiasi prodotto ritenuto non conforme, o di correggere qualsiasi errore manifesto di descrizione o di prezzo.</p>

<h2>Articolo 4 — Compatibilità con il veicolo</h2>
<p>Spetta al Cliente verificare, prima di qualsiasi ordine, la compatibilità della parte con il proprio veicolo: marca, modello, motorizzazione, anno, riferimento del costruttore (riferimento OEM) e riferimento del prodotto indicato sulla scheda.</p>
<p>In caso di dubbio, il Cliente è invitato a contattare il Venditore prima dell'acquisto tramite contact@power-exhaust.com, comunicando il riferimento OEM della parte cercata o il numero di telaio (VIN) del proprio veicolo.</p>
<p>Un errore di compatibilità non segnalato prima dell'acquisto non costituisce un difetto del prodotto e non dà diritto al rimborso ai sensi della garanzia legale di conformità.</p>

<h2>Articolo 5 — Prezzo</h2>
<p>I prezzi sono indicati in euro, IVA inclusa (TTC), escluse le spese di consegna. Le spese di consegna sono calcolate e visualizzate prima della convalida definitiva dell'ordine.</p>
<p>Il Venditore si riserva il diritto di modificare i propri prezzi in qualsiasi momento; i prodotti sono fatturati in base alla tariffa in vigore al momento della convalida dell'ordine.</p>

<h2>Articolo 6 — Ordine</h2>
<p>L'ordine si intende concluso quando il Cliente convalida il carrello, accetta le presenti CGV e procede al pagamento. Al Cliente viene inviata un'email di conferma con il riepilogo dell'ordine. Il Venditore si riserva il diritto di rifiutare o annullare qualsiasi ordine in caso di controversia precedente, pagamento irregolare o sospetto di frode.</p>

<h2>Articolo 7 — Pagamento</h2>
<p>Il pagamento avviene online tramite i mezzi offerti sul sito (carta di credito, PayPal e qualsiasi altro mezzo indicato al momento dell'ordine). Le transazioni sono protette da fornitori certificati; il Venditore non conserva alcun dato bancario.</p>
<p>L'ordine viene evaso solo al ricevimento del pagamento integrale. In caso di rifiuto del pagamento, l'ordine è automaticamente annullato.</p>

<h2>Articolo 8 — Consegna e ricezione</h2>
<p>I prodotti sono spediti all'indirizzo indicato dal Cliente al momento dell'ordine. I tempi di consegna sono indicativi; la spedizione avviene di norma entro 3-5 giorni lavorativi dalla convalida del pagamento.</p>
<p>Il Cliente deve verificare lo stato del pacco alla ricezione. In caso di danneggiamento, deve formulare precise riserve al corriere e informare il Venditore entro 48 ore, allegando fotografie.</p>

<h2>Articolo 9 — Diritto di recesso</h2>
<p>Conformemente agli articoli L.221-18 e seguenti del Codice del Consumo francese, il Cliente dispone di un termine di <strong>14 giorni di calendario</strong> dalla ricezione del prodotto per esercitare il diritto di recesso, senza dover motivare la propria decisione.</p>
<p>Per esercitarlo, il Cliente deve notificare la propria decisione per iscritto (email a contact@power-exhaust.com) prima della scadenza del termine. La parte deve essere restituita <strong>non montata, non modificata e nel suo stato originale</strong>, nell'imballaggio. Le spese di reso sono a carico del Cliente, salvo diversa disposizione delle condizioni di reso.</p>
<p>Il rimborso avviene entro 14 giorni dal recupero della parte o dalla prova di spedizione, tramite il mezzo di pagamento originale.</p>

<h2>Articolo 10 — Garanzie legali</h2>
<p><strong>Garanzia legale di conformità</strong> (articoli L.217-3 a L.217-14 del Codice del Consumo francese): il Cliente dispone di un termine di 2 anni dalla consegna per agire. Per i beni usati, tale durata può essere ridotta a <strong>12 mesi</strong> per accordo tra le parti. In caso di difetto di conformità, il Cliente può ottenere la riparazione, la sostituzione o, in mancanza, la riduzione del prezzo o la risoluzione della vendita.</p>
<p><strong>Garanzia per vizi occulti</strong> (articoli 1641 a 1649 del Codice Civile francese): il Venditore risponde dei vizi occulti della cosa venduta che la rendano inadatta all'uso cui è destinata. L'azione deve essere esercitata entro 2 anni dalla scoperta del vizio.</p>
<p>Tali garanzie non coprono: la normale usura, i difetti visibili menzionati nella descrizione, i danni derivanti da installazione errata, uso inadeguato o mancato rispetto delle raccomandazioni del costruttore.</p>

<h2>Articolo 11 — Responsabilità e installazione</h2>
<p>Il montaggio delle parti di scarico deve essere eseguito da un professionista qualificato, nel rispetto delle norme vigenti (in particolare in materia di emissioni e rumorosità). Il Venditore declina ogni responsabilità in caso di installazione non conforme, modifica della parte o uso del veicolo su strada pubblica in violazione della normativa applicabile.</p>
<p>La responsabilità del Venditore è limitata all'importo dell'ordine; non potrà essere ritenuto responsabile dei danni indiretti o immateriali.</p>

<h2>Articolo 12 — Reclami e assistenza post-vendita</h2>
<p>Qualsiasi reclamo deve essere inviato via email a contact@power-exhaust.com, indicando il numero d'ordine e allegando fotografie. Il Venditore si impegna a rispondere entro un termine ragionevole.</p>

<h2>Articolo 13 — Mediazione e controversie</h2>
<p>In caso di controversia, il Cliente è invitato a contattare preventivamente il servizio clienti per cercare una soluzione amichevole. Conformemente all'articolo L.612-1 del Codice del Consumo francese, il Cliente può ricorrere gratuitamente a un mediatore del consumo ai fini della risoluzione amichevole della controversia.</p>
<p>In mancanza di accordo amichevole, la controversia sarà portata dinanzi ai tribunali francesi competenti. Le presenti CGV sono disciplinate dal diritto francese.</p>
HTML,
    ],
    'conditions-generales-d-utilisation' => [
        'en' => <<<'HTML'
<p>Last updated: 30 September 2026.</p>
<p>These General Terms and Conditions of Use (GTCU) define the conditions of access to and use of the website fullpowerexhaust.com (hereinafter "the Site"), published by POWEREXHAUST.</p>

<h2>Article 1 — Purpose</h2>
<p>The Site is an online shop selling used exhaust lines and exhaust parts. The GTCU apply to any visitor or user (hereinafter "the User") of the Site, independently of any purchase. Purchases are governed by the <a href="{url entity='cms' id=3}">General Terms and Conditions of Sale</a>.</p>

<h2>Article 2 — Acceptance of the GTCU</h2>
<p>Access to and use of the Site imply full and unreserved acceptance of these GTCU. If the User does not accept these conditions, they are invited not to use the Site.</p>

<h2>Article 3 — Access to the Site</h2>
<p>Access to the Site is free of charge. The Seller reserves the right to suspend, restrict or interrupt access to the Site, in particular for maintenance, updates or in the event of force majeure, without its liability being engaged.</p>

<h2>Article 4 — Customer account</h2>
<p>Creating a customer account is required to place an order and track purchases. The User undertakes to provide accurate, complete and up-to-date information, and to keep it updated. They are solely responsible for the confidentiality of their credentials and for any activity carried out from their account. Any fraudulent access or use must be reported immediately to contact@power-exhaust.com.</p>

<h2>Article 5 — Intellectual property</h2>
<p>All elements of the Site (texts, photographs, illustrations, logos, trademarks, graphics, structure, database, software) are the exclusive property of POWEREXHAUST or its partners, and are protected by intellectual property law.</p>
<p>Any reproduction, representation, modification, adaptation, distribution or exploitation, in whole or in part, without prior written authorisation, is strictly prohibited and constitutes an infringement within the meaning of Articles L.335-2 et seq. of the French Intellectual Property Code.</p>

<h2>Article 6 — Authorised use of the Site</h2>
<p>The User undertakes not to:</p>
<ul>
<li>use the Site for unlawful or fraudulent purposes;</li>
<li>harm the operation of the Site (intrusion, data extraction, denial of service…);</li>
<li>bypass the technical security measures;</li>
<li>publish unlawful, defamatory, insulting or misleading content.</li>
</ul>

<h2>Article 7 — Content published by the User</h2>
<p>Any reviews or comments published by the User must be genuine and compliant with the regulations. The Seller reserves the right to remove, without notice, any clearly unlawful or inappropriate content.</p>

<h2>Article 8 — Liability</h2>
<p>The Seller endeavours to ensure the accuracy of the information published on the Site but cannot guarantee the absence of errors or omissions. The information is provided for information purposes and may be modified without notice.</p>
<p>The Seller declines all liability for any damage resulting from an inability to access the Site, an interruption of service or the use of the information it contains.</p>

<h2>Article 9 — Hyperlinks</h2>
<p>The Site may contain links to third-party sites. The Seller exercises no control over these sites and declines all liability for their content, availability or practices.</p>

<h2>Article 10 — Availability and maintenance</h2>
<p>The Seller reserves the right to modify, update or suspend the Site, at any time and without notice, in particular for technical or security reasons.</p>

<h2>Article 11 — Modification of the GTCU</h2>
<p>The Seller reserves the right to modify these GTCU at any time. The applicable GTCU are those in force at the time of browsing. Continued use of the Site after modification constitutes acceptance of the new GTCU.</p>

<h2>Article 12 — Applicable law</h2>
<p>These GTCU are governed by French law. In the event of a dispute, the French courts shall have exclusive jurisdiction.</p>
HTML,
        'es' => <<<'HTML'
<p>Última actualización: 30 de septiembre de 2026.</p>
<p>Las presentes Condiciones Generales de Uso (CGU) definen las condiciones de acceso y uso del sitio web fullpowerexhaust.com (en adelante, «el Sitio»), editado por POWEREXHAUST.</p>

<h2>Artículo 1 — Objeto</h2>
<p>El Sitio es una tienda en línea de venta de líneas de escape y piezas de escape de ocasión. Las CGU se aplican a todo visitante o usuario (en adelante, «el Usuario») del Sitio, con independencia de cualquier compra. Las compras se rigen por las <a href="{url entity='cms' id=3}">Condiciones Generales de Venta</a>.</p>

<h2>Artículo 2 — Aceptación de las CGU</h2>
<p>El acceso y el uso del Sitio implican la aceptación plena y entera de las presentes CGU. Si el Usuario no acepta estas condiciones, se le invita a no utilizar el Sitio.</p>

<h2>Artículo 3 — Acceso al Sitio</h2>
<p>El acceso al Sitio es libre y gratuito. El Vendedor se reserva el derecho de suspender, restringir o interrumpir el acceso al Sitio, en particular por mantenimiento, actualizaciones o fuerza mayor, sin que pueda comprometerse su responsabilidad.</p>

<h2>Artículo 4 — Cuenta de cliente</h2>
<p>La creación de una cuenta de cliente es necesaria para realizar pedidos y hacer su seguimiento. El Usuario se compromete a proporcionar información exacta, completa y actualizada, y a mantenerla actualizada. Es el único responsable de la confidencialidad de sus credenciales y de toda actividad realizada desde su cuenta. Cualquier acceso o uso fraudulento deberá comunicarse de inmediato a contact@power-exhaust.com.</p>

<h2>Artículo 5 — Propiedad intelectual</h2>
<p>El conjunto de los elementos del Sitio (textos, fotografías, ilustraciones, logotipos, marcas, gráficos, estructura, base de datos, software) es propiedad exclusiva de POWEREXHAUST o de sus socios, y está protegido por el derecho de propiedad intelectual.</p>
<p>Toda reproducción, representación, modificación, adaptación, distribución o explotación, total o parcial, sin autorización escrita previa, está estrictamente prohibida y constituye una infracción en el sentido de los artículos L.335-2 y siguientes del Código de Propiedad Intelectual francés.</p>

<h2>Artículo 6 — Uso autorizado del Sitio</h2>
<p>El Usuario se compromete a no:</p>
<ul>
<li>utilizar el Sitio con fines ilícitos o fraudulentos;</li>
<li>perjudicar el funcionamiento del Sitio (intrusión, extracción de datos, denegación de servicio…);</li>
<li>eludir las medidas técnicas de seguridad;</li>
<li>publicar contenidos ilícitos, difamatorios, injuriosos o engañosos.</li>
</ul>

<h2>Artículo 7 — Contenidos publicados por el Usuario</h2>
<p>Las eventuales reseñas o comentarios publicados por el Usuario deben ser sinceros y conformes a la normativa. El Vendedor se reserva el derecho de eliminar sin previo aviso cualquier contenido manifiestamente ilícito o inapropiado.</p>

<h2>Artículo 8 — Responsabilidad</h2>
<p>El Vendedor se esfuerza por garantizar la exactitud de la información publicada en el Sitio, pero no puede garantizar la ausencia de errores u omisiones. La información se facilita a título indicativo y puede modificarse sin previo aviso.</p>
<p>El Vendedor declina toda responsabilidad por los daños derivados de la imposibilidad de acceder al Sitio, de una interrupción del servicio o del uso de la información que contiene.</p>

<h2>Artículo 9 — Enlaces de hipertexto</h2>
<p>El Sitio puede contener enlaces a sitios de terceros. El Vendedor no ejerce ningún control sobre dichos sitios y declina toda responsabilidad en cuanto a su contenido, disponibilidad o prácticas.</p>

<h2>Artículo 10 — Disponibilidad y mantenimiento</h2>
<p>El Vendedor se reserva el derecho de modificar, actualizar o suspender el Sitio, en cualquier momento y sin previo aviso, en particular por razones técnicas o de seguridad.</p>

<h2>Artículo 11 — Modificación de las CGU</h2>
<p>El Vendedor se reserva el derecho de modificar las presentes CGU en cualquier momento. Las CGU aplicables son las vigentes en el momento de la navegación. La continuación del uso del Sitio tras la modificación constituye la aceptación de las nuevas CGU.</p>

<h2>Artículo 12 — Derecho aplicable</h2>
<p>Las presentes CGU se rigen por el derecho francés. En caso de litigio, los tribunales franceses serán los únicos competentes.</p>
HTML,
        'it' => <<<'HTML'
<p>Ultimo aggiornamento: 30 settembre 2026.</p>
<p>Le presenti Condizioni Generali d'Uso (CGU) definiscono le condizioni di accesso e utilizzo del sito fullpowerexhaust.com (di seguito «il Sito»), pubblicato da POWEREXHAUST.</p>

<h2>Articolo 1 — Oggetto</h2>
<p>Il Sito è un negozio online di vendita di linee di scarico e parti di scarico usate. Le CGU si applicano a qualsiasi visitatore o utente (di seguito «l'Utente») del Sito, indipendentemente da qualsiasi acquisto. Gli acquisti sono disciplinati dalle <a href="{url entity='cms' id=3}">Condizioni Generali di Vendita</a>.</p>

<h2>Articolo 2 — Accettazione delle CGU</h2>
<p>L'accesso e l'utilizzo del Sito implicano l'accettazione piena e incondizionata delle presenti CGU. Se l'Utente non accetta tali condizioni, è invitato a non utilizzare il Sito.</p>

<h2>Articolo 3 — Accesso al Sito</h2>
<p>L'accesso al Sito è libero e gratuito. Il Venditore si riserva il diritto di sospendere, limitare o interrompere l'accesso al Sito, in particolare per manutenzione, aggiornamenti o in caso di forza maggiore, senza che la propria responsabilità possa essere chiamata in causa.</p>

<h2>Articolo 4 — Account cliente</h2>
<p>La creazione di un account cliente è necessaria per effettuare ordini e seguirne lo stato. L'Utente si impegna a fornire informazioni accurate, complete e aggiornate e a mantenerle aggiornate. È l'unico responsabile della riservatezza delle proprie credenziali e di qualsiasi attività svolta dal proprio account. Qualsiasi accesso o uso fraudolento deve essere immediatamente segnalato a contact@power-exhaust.com.</p>

<h2>Articolo 5 — Proprietà intellettuale</h2>
<p>Tutti gli elementi del Sito (testi, fotografie, illustrazioni, loghi, marchi, grafica, struttura, database, software) sono di proprietà esclusiva di POWEREXHAUST o dei suoi partner e sono protetti dal diritto della proprietà intellettuale.</p>
<p>Qualsiasi riproduzione, rappresentazione, modifica, adattamento, distribuzione o sfruttamento, totale o parziale, senza previa autorizzazione scritta, è severamente vietata e costituisce contraffazione ai sensi degli articoli L.335-2 e seguenti del Codice della Proprietà Intellettuale francese.</p>

<h2>Articolo 6 — Uso autorizzato del Sito</h2>
<p>L'Utente si impegna a non:</p>
<ul>
<li>utilizzare il Sito per scopi illeciti o fraudolenti;</li>
<li>danneggiare il funzionamento del Sito (intrusione, estrazione di dati, negazione del servizio…);</li>
<li>aggirare le misure tecniche di sicurezza;</li>
<li>pubblicare contenuti illeciti, diffamatori, ingiuriosi o ingannevoli.</li>
</ul>

<h2>Articolo 7 — Contenuti pubblicati dall'Utente</h2>
<p>Eventuali recensioni o commenti pubblicati dall'Utente devono essere veritieri e conformi alla normativa. Il Venditore si riserva il diritto di rimuovere senza preavviso qualsiasi contenuto manifestamente illecito o inappropriato.</p>

<h2>Articolo 8 — Responsabilità</h2>
<p>Il Venditore si adopera per garantire l'accuratezza delle informazioni pubblicate sul Sito ma non può garantire l'assenza di errori o omissioni. Le informazioni sono fornite a titolo indicativo e possono essere modificate senza preavviso.</p>
<p>Il Venditore declina ogni responsabilità per eventuali danni derivanti dall'impossibilità di accedere al Sito, da un'interruzione del servizio o dall'uso delle informazioni in esso contenute.</p>

<h2>Articolo 9 — Collegamenti ipertestuali</h2>
<p>Il Sito può contenere collegamenti a siti di terzi. Il Venditore non esercita alcun controllo su tali siti e declina ogni responsabilità in merito al loro contenuto, disponibilità o pratiche.</p>

<h2>Articolo 10 — Disponibilità e manutenzione</h2>
<p>Il Venditore si riserva il diritto di modificare, aggiornare o sospendere il Sito, in qualsiasi momento e senza preavviso, in particolare per ragioni tecniche o di sicurezza.</p>

<h2>Articolo 11 — Modifica delle CGU</h2>
<p>Il Venditore si riserva il diritto di modificare le presenti CGU in qualsiasi momento. Le CGU applicabili sono quelle in vigore al momento della navigazione. La prosecuzione dell'utilizzo del Sito dopo la modifica costituisce accettazione delle nuove CGU.</p>

<h2>Articolo 12 — Diritto applicabile</h2>
<p>Le presenti CGU sono disciplinate dal diritto francese. In caso di controversia, i tribunali francesi saranno gli unici competenti.</p>
HTML,
    ],
    'politique-de-confidentialite' => [
        'en' => <<<'HTML'
<p>Last updated: 30 September 2026.</p>
<p>This privacy policy describes how POWEREXHAUST collects, uses and protects your personal data, in accordance with Regulation (EU) 2016/679 ("GDPR") and French Law No. 78-17 of 6 January 1978 as amended ("Informatique et Libertés").</p>

<h2>1. Data controller</h2>
<p>The data controller is POWEREXHAUST, which can be contacted at: contact@power-exhaust.com.</p>

<h2>2. Data collected</h2>
<p>In the course of your browsing and orders, we may collect:</p>
<ul>
<li><strong>Identification data</strong>: surname, first name;</li>
<li><strong>Contact data</strong>: email address, postal address, telephone number (optional);</li>
<li><strong>Order data</strong>: purchase history, invoices;</li>
<li><strong>Browsing data</strong>: IP address, pages viewed, technical data (via cookies).</li>
</ul>
<p>No sensitive data within the meaning of the GDPR is collected. Payment data is processed by PCI-DSS certified providers; we never store your bank details.</p>

<h2>3. Purposes and legal bases</h2>
<ul>
<li><strong>Performance of the contract</strong>: order processing and tracking, delivery, after-sales service, customer account management;</li>
<li><strong>Legal obligations</strong>: invoicing, accounting (10-year retention);</li>
<li><strong>Legitimate interest</strong>: site improvement, security, fraud prevention;</li>
<li><strong>Consent</strong>: sending newsletters and commercial offers (only if you have consented).</li>
</ul>

<h2>4. Retention periods</h2>
<ul>
<li>Customer account data: 3 years from the last order;</li>
<li>Invoicing data: 10 years (accounting and tax obligations);</li>
<li>Browsing data and cookies: 13 months maximum;</li>
<li>Prospecting data: 3 years from the last contact.</li>
</ul>

<h2>5. Recipients of data</h2>
<p>Your data is never sold. It may be transmitted solely to the following recipients, within the limits of their duties:</p>
<ul>
<li>transport and delivery providers;</li>
<li>payment providers;</li>
<li>technical and hosting providers;</li>
<li>audience analysis tools (anonymised data).</li>
</ul>
<p>All our subcontractors are bound by strict confidentiality and security obligations and process the data solely on our behalf.</p>

<h2>6. Cookies</h2>
<p>The Site uses technical cookies necessary for its operation (session, cart, authentication), as well as, subject to your consent, audience measurement and marketing cookies. For more information, see our <a href="{url entity='cms' id=8}">Cookie policy</a>.</p>

<h2>7. Your rights</h2>
<p>In accordance with the GDPR, you have the following rights over your data: right of access, rectification, erasure ("right to be forgotten"), restriction of processing, objection, and portability.</p>
<p>To exercise these rights, contact us at contact@power-exhaust.com, proving your identity. You also have the right to lodge a complaint with the CNIL (www.cnil.fr).</p>

<h2>8. Data security</h2>
<p>We implement appropriate technical and organisational measures (encryption of exchanges, restricted access, secure hosting) to protect your data against loss, alteration, disclosure or unauthorised access.</p>

<h2>9. Transfers outside the European Union</h2>
<p>Insofar as certain providers may be located outside the European Union, appropriate safeguards (standard contractual clauses) are put in place in accordance with the GDPR.</p>

<h2>10. Contact and complaints</h2>
<p>For any question relating to the protection of your data, you may write to us at contact@power-exhaust.com.</p>

<h2>11. Policy modification</h2>
<p>This policy may be updated at any time to reflect changes in our practices or the regulations. The applicable version is the one published on the Site.</p>
HTML,
        'es' => <<<'HTML'
<p>Última actualización: 30 de septiembre de 2026.</p>
<p>La presente política de privacidad describe cómo POWEREXHAUST recopila, utiliza y protege sus datos personales, de conformidad con el Reglamento (UE) 2016/679 («RGPD») y la Ley francesa n.º 78-17 de 6 de enero de 1978 modificada («Informatique et Libertés»).</p>

<h2>1. Responsable del tratamiento</h2>
<p>El responsable del tratamiento de los datos es POWEREXHAUST, con el que puede contactarse en: contact@power-exhaust.com.</p>

<h2>2. Datos recopilados</h2>
<p>En el marco de su navegación y de sus pedidos, podemos recopilar:</p>
<ul>
<li><strong>Datos de identificación</strong>: apellidos, nombre;</li>
<li><strong>Datos de contacto</strong>: dirección de correo electrónico, dirección postal, número de teléfono (opcional);</li>
<li><strong>Datos de pedido</strong>: historial de compras, facturas;</li>
<li><strong>Datos de navegación</strong>: dirección IP, páginas consultadas, datos técnicos (mediante cookies).</li>
</ul>
<p>No se recopila ningún dato sensible en el sentido del RGPD. Los datos de pago son tratados por proveedores certificados PCI-DSS; nunca almacenamos sus datos bancarios.</p>

<h2>3. Finalidades y bases legales</h2>
<ul>
<li><strong>Ejecución del contrato</strong>: tratamiento y seguimiento de pedidos, entrega, servicio posventa, gestión de la cuenta de cliente;</li>
<li><strong>Obligaciones legales</strong>: facturación, contabilidad (conservación 10 años);</li>
<li><strong>Interés legítimo</strong>: mejora del sitio, seguridad, prevención del fraude;</li>
<li><strong>Consentimiento</strong>: envío de boletines y ofertas comerciales (solo si lo ha consentido).</li>
</ul>

<h2>4. Plazos de conservación</h2>
<ul>
<li>Datos de la cuenta de cliente: 3 años desde el último pedido;</li>
<li>Datos de facturación: 10 años (obligaciones contables y fiscales);</li>
<li>Datos de navegación y cookies: 13 meses como máximo;</li>
<li>Datos de prospección: 3 años desde el último contacto.</li>
</ul>

<h2>5. Destinatarios de los datos</h2>
<p>Sus datos nunca se venden. Pueden transmitirse únicamente a los siguientes destinatarios, dentro del límite de sus funciones:</p>
<ul>
<li>proveedores de transporte y entrega;</li>
<li>proveedores de pago;</li>
<li>proveedores técnicos y de alojamiento;</li>
<li>herramientas de análisis de audiencia (datos anonimizados).</li>
</ul>
<p>Todos nuestros subcontratistas están sujetos a obligaciones estrictas de confidencialidad y seguridad y tratan los datos exclusivamente por nuestra cuenta.</p>

<h2>6. Cookies</h2>
<p>El Sitio utiliza cookies técnicas necesarias para su funcionamiento (sesión, carrito, autenticación), así como, previo consentimiento, cookies de medición de audiencia y de marketing. Para más información, consulte nuestra <a href="{url entity='cms' id=8}">Política de cookies</a>.</p>

<h2>7. Sus derechos</h2>
<p>De conformidad con el RGPD, usted dispone de los siguientes derechos sobre sus datos: derecho de acceso, rectificación, supresión («derecho al olvido»), limitación del tratamiento, oposición y portabilidad.</p>
<p>Para ejercer estos derechos, contáctenos en contact@power-exhaust.com, justificando su identidad. También tiene derecho a presentar una reclamación ante la CNIL (www.cnil.fr).</p>

<h2>8. Seguridad de los datos</h2>
<p>Implementamos medidas técnicas y organizativas apropiadas (cifrado de los intercambios, accesos restringidos, alojamiento seguro) para proteger sus datos contra toda pérdida, alteración, divulgación o acceso no autorizado.</p>

<h2>9. Transferencias fuera de la Unión Europea</h2>
<p>En la medida en que algunos proveedores puedan estar situados fuera de la Unión Europea, se establecen garantías apropiadas (cláusulas contractuales tipo) de conformidad con el RGPD.</p>

<h2>10. Contacto y reclamaciones</h2>
<p>Para cualquier cuestión relativa a la protección de sus datos, puede escribirnos a contact@power-exhaust.com.</p>

<h2>11. Modificación de la política</h2>
<p>La presente política puede actualizarse en cualquier momento para reflejar la evolución de nuestras prácticas o de la normativa. La versión aplicable es la publicada en el Sitio.</p>
HTML,
        'it' => <<<'HTML'
<p>Ultimo aggiornamento: 30 settembre 2026.</p>
<p>La presente informativa sulla privacy descrive le modalità con cui POWEREXHAUST raccoglie, utilizza e protegge i tuoi dati personali, conformemente al Regolamento (UE) 2016/679 («GDPR») e alla Legge francese n. 78-17 del 6 gennaio 1978 come modificata («Informatique et Libertés»).</p>

<h2>1. Titolare del trattamento</h2>
<p>Il titolare del trattamento dei dati è POWEREXHAUST, contattabile all'indirizzo: contact@power-exhaust.com.</p>

<h2>2. Dati raccolti</h2>
<p>Nell'ambito della tua navigazione e dei tuoi ordini, possiamo raccogliere:</p>
<ul>
<li><strong>Dati identificativi</strong>: cognome, nome;</li>
<li><strong>Dati di contatto</strong>: indirizzo email, indirizzo postale, numero di telefono (facoltativo);</li>
<li><strong>Dati d'ordine</strong>: cronologia acquisti, fatture;</li>
<li><strong>Dati di navigazione</strong>: indirizzo IP, pagine consultate, dati tecnici (tramite cookie).</li>
</ul>
<p>Non vengono raccolti dati sensibili ai sensi del GDPR. I dati di pagamento sono trattati da fornitori certificati PCI-DSS; non conserviamo mai i tuoi dati bancari.</p>

<h2>3. Finalità e basi giuridiche</h2>
<ul>
<li><strong>Esecuzione del contratto</strong>: trattamento e tracciamento degli ordini, consegna, assistenza post-vendita, gestione dell'account cliente;</li>
<li><strong>Obblighi legali</strong>: fatturazione, contabilità (conservazione 10 anni);</li>
<li><strong>Legittimo interesse</strong>: miglioramento del sito, sicurezza, prevenzione delle frodi;</li>
<li><strong>Consenso</strong>: invio di newsletter e offerte commerciali (solo se hai prestato il consenso).</li>
</ul>

<h2>4. Periodi di conservazione</h2>
<ul>
<li>Dati dell'account cliente: 3 anni dall'ultimo ordine;</li>
<li>Dati di fatturazione: 10 anni (obblighi contabili e fiscali);</li>
<li>Dati di navigazione e cookie: massimo 13 mesi;</li>
<li>Dati di prospecting: 3 anni dall'ultimo contatto.</li>
</ul>

<h2>5. Destinatari dei dati</h2>
<p>I tuoi dati non vengono mai venduti. Possono essere trasmessi unicamente ai seguenti destinatari, nei limiti delle loro attribuzioni:</p>
<ul>
<li>fornitori di trasporto e consegna;</li>
<li>fornitori di pagamento;</li>
<li>fornitori tecnici e di hosting;</li>
<li>strumenti di analisi dell'audience (dati anonimizzati).</li>
</ul>
<p>Tutti i nostri subappaltatori sono soggetti a rigorosi obblighi di riservatezza e sicurezza e trattano i dati esclusivamente per nostro conto.</p>

<h2>6. Cookie</h2>
<p>Il Sito utilizza cookie tecnici necessari al suo funzionamento (sessione, carrello, autenticazione), nonché, previo consenso, cookie di misurazione dell'audience e di marketing. Per maggiori informazioni, consulta la nostra <a href="{url entity='cms' id=8}">Politica dei cookie</a>.</p>

<h2>7. I tuoi diritti</h2>
<p>Conformemente al GDPR, disponi dei seguenti diritti sui tuoi dati: diritto di accesso, rettifica, cancellazione («diritto all'oblio»), limitazione del trattamento, opposizione e portabilità.</p>
<p>Per esercitare tali diritti, contattaci all'indirizzo contact@power-exhaust.com, comprovando la tua identità. Hai inoltre il diritto di proporre reclamo alla CNIL (www.cnil.fr).</p>

<h2>8. Sicurezza dei dati</h2>
<p>Adottiamo misure tecniche e organizzative appropriate (crittografia degli scambi, accessi limitati, hosting sicuro) per proteggere i tuoi dati da perdita, alterazione, divulgazione o accesso non autorizzato.</p>

<h2>9. Trasferimenti al di fuori dell'Unione europea</h2>
<p>Nella misura in cui alcuni fornitori possano essere situati al di fuori dell'Unione europea, vengono predisposte adeguate garanzie (clausole contrattuali tipo) conformemente al GDPR.</p>

<h2>10. Contatto e reclami</h2>
<p>Per qualsiasi domanda relativa alla protezione dei tuoi dati, puoi scriverci all'indirizzo contact@power-exhaust.com.</p>

<h2>11. Modifica dell'informativa</h2>
<p>La presente informativa può essere aggiornata in qualsiasi momento per riflettere l'evoluzione delle nostre pratiche o della normativa. La versione applicabile è quella pubblicata sul Sito.</p>
HTML,
    ],
    'mentions-legales' => [
        'en' => <<<'HTML'
<p>In accordance with the provisions of French Law No. 2004-575 of 21 June 2004 on confidence in the digital economy (LCEN), users of the website fullpowerexhaust.com are hereby informed of the identity of the various parties involved in its creation and monitoring.</p>

<h2>1. Site publisher</h2>
<p>The website fullpowerexhaust.com is published by <strong>POWEREXHAUST</strong>.</p>
<ul>
<li><strong>Company name:</strong> POWEREXHAUST</li>
<li><strong>Legal form:</strong> Société par actions simplifiée (SAS)</li>
<li><strong>Share capital:</strong> €10,000</li>
<li><strong>Registered office:</strong> 6 rue de la Brie, 77410 Claye-Souilly, France</li>
<li><strong>Registration:</strong> SIREN 104 257 449 · SIRET 104 257 449 00017</li>
<li><strong>Intra-community VAT number:</strong> FR 30 104 257 449</li>
<li><strong>APE code:</strong> 4532Z — Retail trade of motor vehicle equipment</li>
<li><strong>Email:</strong> contact@power-exhaust.com</li>
</ul>

<h2>2. Publication director</h2>
<p>The publication director is <strong>Rodrigue BOISDUR-SALBRIS</strong>, President of POWEREXHAUST, acting with the necessary powers as President.</p>

<h2>3. Hosting</h2>
<p>The website is hosted by <strong>O2switch</strong>, SAS, whose registered office is located at 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France. Telephone: 04 44 44 60 40.</p>

<h2>4. Activity</h2>
<p>POWEREXHAUST carries out the activity of online sale of used exhaust lines and exhaust parts for motor vehicles (APE code 4532Z — Retail trade of motor vehicle equipment). Sales are subject to the <a href="{url entity='cms' id=3}">General Terms and Conditions of Sale</a>.</p>

<h2>5. Intellectual property</h2>
<p>All content of the website (texts, photographs, illustrations, logos, trademarks, graphics, structure, database) is the exclusive property of POWEREXHAUST, with the exception of elements belonging to third parties (motor vehicle brands cited for information purposes, in particular).</p>
<p>Any reproduction, representation, modification or exploitation, in whole or in part, without prior written authorisation, is prohibited and constitutes an infringement sanctioned by Articles L.335-2 et seq. of the French Intellectual Property Code.</p>

<h2>6. Personal data</h2>
<p>The collection and processing of users' personal data are governed by our <a href="{url entity='cms' id=7}">Privacy policy</a>. In accordance with the GDPR, you have the right of access, rectification, erasure and objection regarding the data concerning you, exercisable at contact@power-exhaust.com.</p>

<h2>7. Cookies</h2>
<p>The website uses cookies, the terms of which are described in the <a href="{url entity='cms' id=8}">Cookie policy</a>.</p>

<h2>8. Liability</h2>
<p>The content of the website is provided for information purposes. Although the information is carefully updated, POWEREXHAUST cannot guarantee the accuracy, completeness or timeliness of the information published, nor be held liable for damage resulting from its use.</p>

<h2>9. Applicable law</h2>
<p>This legal notice is governed by French law. In the event of a dispute, the French courts shall have exclusive jurisdiction.</p>
HTML,
        'es' => <<<'HTML'
<p>De conformidad con las disposiciones de la Ley francesa n.º 2004-575 de 21 de junio de 2004 para la confianza en la economía digital (LCEN), se informa a los usuarios del sitio fullpowerexhaust.com de la identidad de los distintos intervinientes en su realización y seguimiento.</p>

<h2>1. Editor del sitio</h2>
<p>El sitio fullpowerexhaust.com es editado por la sociedad <strong>POWEREXHAUST</strong>.</p>
<ul>
<li><strong>Denominación social:</strong> POWEREXHAUST</li>
<li><strong>Forma jurídica:</strong> Société par actions simplifiée (SAS)</li>
<li><strong>Capital social:</strong> 10 000 €</li>
<li><strong>Domicilio social:</strong> 6 rue de la Brie, 77410 Claye-Souilly, Francia</li>
<li><strong>Registro:</strong> SIREN 104 257 449 · SIRET 104 257 449 00017</li>
<li><strong>N.º de IVA intracomunitario:</strong> FR 30 104 257 449</li>
<li><strong>Código APE:</strong> 4532Z — Comercio al por menor de equipos de vehículos de motor</li>
<li><strong>Correo electrónico:</strong> contact@power-exhaust.com</li>
</ul>

<h2>2. Director de la publicación</h2>
<p>El director de la publicación es <strong>Rodrigue BOISDUR-SALBRIS</strong>, Presidente de POWEREXHAUST, actuando con los poderes necesarios en calidad de Presidente.</p>

<h2>3. Alojamiento</h2>
<p>El sitio está alojado por <strong>O2switch</strong>, SAS, con domicilio social en 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, Francia. Teléfono: 04 44 44 60 40.</p>

<h2>4. Actividad</h2>
<p>POWEREXHAUST ejerce la actividad de venta en línea de líneas de escape y piezas de escape de ocasión para vehículos de motor (código APE 4532Z — Comercio al por menor de equipos de vehículos de motor). Las ventas se someten a las <a href="{url entity='cms' id=3}">Condiciones Generales de Venta</a>.</p>

<h2>5. Propiedad intelectual</h2>
<p>El conjunto del contenido del sitio (textos, fotografías, ilustraciones, logotipos, marcas, gráficos, estructura, base de datos) es propiedad exclusiva de POWEREXHAUST, a excepción de los elementos pertenecientes a terceros (marcas de vehículos citadas a título informativo, en particular).</p>
<p>Toda reproducción, representación, modificación o explotación, total o parcial, sin autorización escrita previa, está prohibida y constituye una infracción sancionada por los artículos L.335-2 y siguientes del Código de Propiedad Intelectual francés.</p>

<h2>6. Datos personales</h2>
<p>La recogida y el tratamiento de los datos personales de los usuarios se rigen por nuestra <a href="{url entity='cms' id=7}">Política de privacidad</a>. De conformidad con el RGPD, usted dispone de un derecho de acceso, rectificación, supresión y oposición sobre los datos que le conciernen, ejercitable en contact@power-exhaust.com.</p>

<h2>7. Cookies</h2>
<p>El sitio utiliza cookies cuyas modalidades se describen en la <a href="{url entity='cms' id=8}">Política de cookies</a>.</p>

<h2>8. Responsabilidad</h2>
<p>El contenido del sitio se facilita a título informativo. Aunque la información se actualiza con esmero, POWEREXHAUST no puede garantizar la exactitud, exhaustividad o actualidad de la información difundida, ni ser considerada responsable de los daños derivados de su uso.</p>

<h2>9. Derecho aplicable</h2>
<p>El presente aviso legal se rige por el derecho francés. En caso de litigio, los tribunales franceses serán los únicos competentes.</p>
HTML,
        'it' => <<<'HTML'
<p>Conformemente alle disposizioni della Legge francese n. 2004-575 del 21 giugno 2004 per la fiducia nell'economia digitale (LCEN), si informano gli utenti del sito fullpowerexhaust.com dell'identità dei diversi soggetti coinvolti nella sua realizzazione e nel suo monitoraggio.</p>

<h2>1. Editore del sito</h2>
<p>Il sito fullpowerexhaust.com è edito dalla società <strong>POWEREXHAUST</strong>.</p>
<ul>
<li><strong>Denominazione sociale:</strong> POWEREXHAUST</li>
<li><strong>Forma giuridica:</strong> Société par actions simplifiée (SAS)</li>
<li><strong>Capitale sociale:</strong> 10 000 €</li>
<li><strong>Sede legale:</strong> 6 rue de la Brie, 77410 Claye-Souilly, Francia</li>
<li><strong>Registrazione:</strong> SIREN 104 257 449 · SIRET 104 257 449 00017</li>
<li><strong>Partita IVA intracomunitaria:</strong> FR 30 104 257 449</li>
<li><strong>Codice APE:</strong> 4532Z — Commercio al dettaglio di attrezzature per autoveicoli</li>
<li><strong>Email:</strong> contact@power-exhaust.com</li>
</ul>

<h2>2. Direttore della pubblicazione</h2>
<p>Il direttore della pubblicazione è <strong>Rodrigue BOISDUR-SALBRIS</strong>, Presidente di POWEREXHAUST, che agisce con i poteri necessari in qualità di Presidente.</p>

<h2>3. Hosting</h2>
<p>Il sito è ospitato da <strong>O2switch</strong>, SAS, con sede legale in 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, Francia. Telefono: 04 44 44 60 40.</p>

<h2>4. Attività</h2>
<p>POWEREXHAUST svolge l'attività di vendita online di linee di scarico e parti di scarico usate per autoveicoli (codice APE 4532Z — Commercio al dettaglio di attrezzature per autoveicoli). Le vendite sono soggette alle <a href="{url entity='cms' id=3}">Condizioni Generali di Vendita</a>.</p>

<h2>5. Proprietà intellettuale</h2>
<p>L'insieme dei contenuti del sito (testi, fotografie, illustrazioni, loghi, marchi, grafica, struttura, database) è di proprietà esclusiva di POWEREXHAUST, a eccezione degli elementi appartenenti a terzi (marchi di veicoli citati a titolo informativo, in particolare).</p>
<p>Qualsiasi riproduzione, rappresentazione, modifica o sfruttamento, totale o parziale, senza previa autorizzazione scritta, è vietata e costituisce contraffazione sanzionata dagli articoli L.335-2 e seguenti del Codice della Proprietà Intellettuale francese.</p>

<h2>6. Dati personali</h2>
<p>La raccolta e il trattamento dei dati personali degli utenti sono disciplinati dalla nostra <a href="{url entity='cms' id=7}">Informativa sulla privacy</a>. Conformemente al GDPR, disponi del diritto di accesso, rettifica, cancellazione e opposizione sui dati che ti riguardano, esercitabile all'indirizzo contact@power-exhaust.com.</p>

<h2>7. Cookie</h2>
<p>Il sito utilizza cookie le cui modalità sono descritte nella <a href="{url entity='cms' id=8}">Politica dei cookie</a>.</p>

<h2>8. Responsabilità</h2>
<p>Il contenuto del sito è fornito a titolo informativo. Sebbene le informazioni siano aggiornate con cura, POWEREXHAUST non può garantire l'accuratezza, la completezza o l'attualità delle informazioni pubblicate, né essere ritenuta responsabile dei danni derivanti dal loro utilizzo.</p>

<h2>9. Diritto applicabile</h2>
<p>Le presenti note legali sono disciplinate dal diritto francese. In caso di controversia, i tribunali francesi saranno gli unici competenti.</p>
HTML,
    ],
];

$updated = 0;
foreach ($pages as $slug => $translations) {
    $idCms = (int) Db::getInstance()->getValue('SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_lang WHERE link_rewrite = "' . pSQL($slug) . '" AND id_lang = 2');
    if (!$idCms) {
        continue;
    }
    foreach ($langIds as $iso => $idLang) {
        if (!isset($translations[$iso])) {
            continue;
        }
        Db::getInstance()->update('cms_lang', ['content' => pSQL($translations[$iso], true)], 'id_cms = ' . $idCms . ' AND id_lang = ' . $idLang);
        $updated++;
        echo "✔ CMS #{$idCms} ({$slug}) — contenu traduit en {$iso}\n";
    }
}

echo "Terminé : {$updated} contenus traduits.\n";
