import { useState } from "react";
import {
  ShoppingCart, User, Menu, X, Search, ChevronRight,
  Gauge, Volume2, Shield, Wrench, Check, AlertCircle,
  LayoutDashboard, Package, BarChart2, Bell, LogOut,
  Eye, ChevronLeft, RefreshCw, Download, Truck,
  TrendingUp, Clock, Euro, Plus, Pencil, Trash2,
  Save, Users, Store, CreditCard, Tag,
} from "lucide-react";
import bannerHero from "@/imports/banner-hero.png";
import newHero from "@/imports/WhatsApp_Image_2026-08-26_at_17.53.30.jpeg";
import { ImageWithFallback } from "@/app/components/figma/ImageWithFallback";

type Page =
  | "home"
  | "catalog"
  | "auth"
  | "cgv"
  | "cgu"
  | "privacy"
  | "cookies"
  | "refund"
  | "returns"
  | "contact"
  | "mentions"
  | "admin";
type AuthTab = "login" | "register";
type OrderStatus = "pending" | "processing" | "shipped" | "delivered" | "cancelled";
type AdminSection = "orders" | "stats";

interface Order {
  id: string;
  client: string;
  email: string;
  phone: string;
  product: string;
  brand: string;
  amount: number;
  date: string;
  status: OrderStatus;
  address: string;
}

const MOCK_ORDERS: Order[] = [
  { id: "PE-2025-001", client: "Marie Dupont", email: "marie.dupont@gmail.com", phone: "06 12 34 56 78", product: "BMW Série 3 E90 — Ligne arrière", brand: "BMW", amount: 185, date: "2025-06-14", status: "delivered", address: "12 rue des Lilas, 75011 Paris" },
  { id: "PE-2025-002", client: "Thomas Bernard", email: "t.bernard@hotmail.com", phone: "07 98 76 54 32", product: "Audi A4 B8 2.0 TDI — Ligne complète", brand: "Audi", amount: 220, date: "2025-06-16", status: "shipped", address: "7 avenue Foch, 69006 Lyon" },
  { id: "PE-2025-003", client: "Lucas Martin", email: "lucas.martin@outlook.fr", phone: "06 55 44 33 22", product: "Volkswagen Golf VI — Ligne sport inox", brand: "Volkswagen", amount: 165, date: "2025-06-17", status: "processing", address: "34 bd Gambetta, 33000 Bordeaux" },
  { id: "PE-2025-004", client: "Camille Moreau", email: "camille.moreau@free.fr", phone: "06 71 82 93 04", product: "Peugeot 308 II — Silencieux arrière", brand: "Peugeot", amount: 95, date: "2025-06-18", status: "pending", address: "9 rue du Port, 44000 Nantes" },
  { id: "PE-2025-005", client: "Antoine Roux", email: "a.roux@gmail.com", phone: "07 34 56 78 90", product: "Mercedes Classe C W204 — Double sortie", brand: "Mercedes", amount: 210, date: "2025-06-19", status: "pending", address: "22 allée des Roses, 31000 Toulouse" },
  { id: "PE-2025-006", client: "Sophie Lefevre", email: "sophie.l@gmail.com", phone: "06 91 23 45 67", product: "Renault Mégane III — Ligne complète", brand: "Renault", amount: 120, date: "2025-06-20", status: "shipped", address: "5 rue Pasteur, 13008 Marseille" },
  { id: "PE-2025-007", client: "Nicolas Petit", email: "nico.petit@yahoo.fr", phone: "07 12 34 56 78", product: "Ford Focus MK3 — Pot central + arrière", brand: "Ford", amount: 85, date: "2025-06-21", status: "processing", address: "18 cours de la Marne, 33000 Bordeaux" },
  { id: "PE-2025-008", client: "Emma Dubois", email: "emma.d@hotmail.com", phone: "06 45 67 89 01", product: "Audi A3 8P — Ligne inox sportive", brand: "Audi", amount: 190, date: "2025-06-22", status: "pending", address: "3 impasse du Moulin, 59000 Lille" },
  { id: "PE-2025-009", client: "Julien Simon", email: "julien.simon@gmail.com", phone: "06 78 90 12 34", product: "BMW Série 3 E90 — Ligne arrière", brand: "BMW", amount: 185, date: "2025-06-23", status: "cancelled", address: "11 rue Jean Jaurès, 67000 Strasbourg" },
  { id: "PE-2025-010", client: "Chloé Laurent", email: "chloe.l@gmail.com", phone: "07 56 78 90 12", product: "Volkswagen Golf VI — Ligne sport inox", brand: "Volkswagen", amount: 165, date: "2025-06-24", status: "pending", address: "29 rue des Martyrs, 75009 Paris" },
];

const STATUS_LABELS: Record<OrderStatus, string> = {
  pending: "En attente",
  processing: "En préparation",
  shipped: "Expédiée",
  delivered: "Livrée",
  cancelled: "Annulée",
};

const STATUS_COLORS: Record<OrderStatus, string> = {
  pending: "bg-amber-900/30 text-amber-400 border border-amber-700/40",
  processing: "bg-blue-900/30 text-blue-400 border border-blue-700/40",
  shipped: "bg-violet-900/30 text-violet-400 border border-violet-700/40",
  delivered: "bg-green-900/30 text-green-400 border border-green-700/40",
  cancelled: "bg-red-900/20 text-red-400 border border-red-900/40",
};

const PRODUCTS = [
  { id:1, brand:"BMW",        model:"Série 3 E90 320d",       piece:"Ligne d'échappement arrière",  price:185, year:2008, km:"92 000 km",  category:"Moto", condition:"Bon état",       img:"https://images.unsplash.com/photo-1746393464150-91dabe60bf23?w=600&h=380&fit=crop&auto=format" },
  { id:2, brand:"Audi",       model:"A4 B8 2.0 TDI",          piece:"Ligne complète d'origine",     price:220, year:2010, km:"118 000 km", category:"Moto", condition:"Bon état",       img:"https://images.unsplash.com/photo-1679621167140-69a9c8c1b87e?w=600&h=380&fit=crop&auto=format" },
  { id:3, brand:"Volkswagen", model:"Golf VI 1.6 TDI",         piece:"Ligne sport inox",             price:165, year:2011, km:"86 000 km",  category:"Moto", condition:"Très bon état", img:"https://images.unsplash.com/photo-1580014317999-e9f1936787a5?w=600&h=380&fit=crop&auto=format" },
  { id:4, brand:"Peugeot",    model:"308 II 1.6 BlueHDi",      piece:"Silencieux arrière",           price:95,  year:2015, km:"74 000 km",  category:"Moto", condition:"Très bon état", img:"https://images.unsplash.com/photo-1486006920555-c77dcf18193c?w=600&h=380&fit=crop&auto=format" },
  { id:5, brand:"Mercedes",   model:"Classe C W204 220 CDI",   piece:"Silencieux double sortie",     price:210, year:2009, km:"131 000 km", category:"Moto", condition:"Bon état",       img:"https://images.unsplash.com/photo-1746393464150-91dabe60bf23?w=600&h=380&fit=crop&auto=format" },
  { id:6, brand:"Renault",    model:"Mégane III 1.5 dCi",      piece:"Ligne complète",               price:120, year:2012, km:"97 000 km",  category:"Moto", condition:"État correct",  img:"https://images.unsplash.com/photo-1679621167140-69a9c8c1b87e?w=600&h=380&fit=crop&auto=format" },
  { id:7, brand:"Ford",       model:"Focus MK3 1.6 TDCi",      piece:"Pot central + arrière",        price:85,  year:2013, km:"82 000 km",  category:"Moto", condition:"Bon état",       img:"https://images.unsplash.com/photo-1580014317999-e9f1936787a5?w=600&h=380&fit=crop&auto=format" },
  { id:8, brand:"Audi",       model:"A3 8P 2.0 TDI",           piece:"Ligne inox sportive",          price:190, year:2007, km:"108 000 km", category:"Moto", condition:"Bon état",       img:"https://images.unsplash.com/photo-1486006920555-c77dcf18193c?w=600&h=380&fit=crop&auto=format" },
  { id:9, brand:"Yoshimura",  model:"Suzuki GSX-R1000 2020",   piece:"Ligne complète Slip-On Alpha T",price:340, year:2020, km:"8 000 km",   category:"Moto",        condition:"Très bon état", img:"https://images.unsplash.com/photo-1690210425456-dedc2dd13d75?w=600&h=380&fit=crop&auto=format" },
  { id:10,brand:"Akrapovič",  model:"Kawasaki Z900 2019",       piece:"Ligne complète inox titane",   price:295, year:2019, km:"12 000 km",  category:"Moto",        condition:"Très bon état", img:"https://images.unsplash.com/photo-1690210425456-dedc2dd13d75?w=600&h=380&fit=crop&auto=format" },
  { id:11,brand:"Arrow",      model:"Honda CBR600RR 2015",      piece:"Silencieux carbone",           price:185, year:2015, km:"24 000 km",  category:"Moto",        condition:"Bon état",       img:"https://images.unsplash.com/photo-1690210425456-dedc2dd13d75?w=600&h=380&fit=crop&auto=format" },
  { id:12,brand:"Leovince",   model:"Yamaha MT-07 2021",        piece:"Silencieux inox GP Style",     price:155, year:2021, km:"6 500 km",   category:"Moto",        condition:"Très bon état", img:"https://images.unsplash.com/photo-1690210425456-dedc2dd13d75?w=600&h=380&fit=crop&auto=format" },
];

const BRANDS = [
  "Tous",
  "Akrapovič",
  "Arrow",
  "SC-Project",
  "Yoshimura",
  "Leovince",
  "Remus",
  "Ixil",
  "Mivv",
  "Two Brothers",
  "Termignoni",
  "Bos Exhaust",
];

const displayFont = {
  fontFamily: "'Barlow Condensed', sans-serif",
};

// ─── Header ────────────────────────────────────────────────────────────────

function Header({
  page,
  setPage,
  cartCount,
}: {
  page: Page;
  setPage: (p: Page) => void;
  cartCount: number;
}) {
  const [menuOpen, setMenuOpen] = useState(false);

  const nav = [
    { label: "Accueil", value: "home" as Page },
    { label: "Catalogue", value: "catalog" as Page },
    { label: "Contact", value: "contact" as Page },
  ];

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-background border-b border-border">
      <div className="max-w-[1320px] mx-auto px-5 flex items-center justify-between h-16">
        {/* Logo */}
        <button
          onClick={() => {
            setPage("home");
            setMenuOpen(false);
          }}
          className="flex items-center gap-2 focus:outline-none group"
        >
          <span
            style={displayFont}
            className="text-2xl font-bold tracking-widest text-foreground group-hover:text-primary transition-colors duration-200"
          >
            FULL<span className="text-primary">POWER</span>EXHAUST
          </span>
        </button>

        {/* Desktop nav */}
        <nav className="hidden md:flex items-center gap-8">
          {nav.map((item) => (
            <button
              key={item.value}
              onClick={() => setPage(item.value)}
              className={`text-sm font-medium tracking-widest uppercase transition-colors duration-200 pb-0.5 ${
                page === item.value
                  ? "text-primary border-b border-primary"
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              {item.label}
            </button>
          ))}
        </nav>

        {/* Actions */}
        <div className="flex items-center gap-4">
          <button className="text-muted-foreground hover:text-foreground transition-colors duration-200">
            <Search size={18} />
          </button>
          <button
            onClick={() => setPage("auth")}
            className="text-muted-foreground hover:text-foreground transition-colors duration-200"
          >
            <User size={18} />
          </button>
          <button className="relative text-muted-foreground hover:text-foreground transition-colors duration-200">
            <ShoppingCart size={18} />
            {cartCount > 0 && (
              <span className="absolute -top-1.5 -right-1.5 bg-primary text-white text-[10px] w-4 h-4 flex items-center justify-center font-bold">
                {cartCount}
              </span>
            )}
          </button>
          {/* Mobile burger */}
          <button
            className="md:hidden text-muted-foreground hover:text-foreground transition-colors duration-200"
            onClick={() => setMenuOpen((v) => !v)}
          >
            {menuOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden border-t border-border bg-background px-5 py-4 flex flex-col gap-4">
          {nav.map((item) => (
            <button
              key={item.value}
              onClick={() => {
                setPage(item.value);
                setMenuOpen(false);
              }}
              className={`text-left text-sm font-medium tracking-widest uppercase ${
                page === item.value
                  ? "text-primary"
                  : "text-muted-foreground"
              }`}
            >
              {item.label}
            </button>
          ))}
          <button
            onClick={() => {
              setPage("auth");
              setMenuOpen(false);
            }}
            className="text-left text-sm font-medium tracking-widest uppercase text-muted-foreground"
          >
            Mon compte
          </button>
        </div>
      )}
    </header>
  );
}

// ─── Product Card ───────────────────────────────────────────────────────────

function ProductCard({
  product,
}: {
  product: (typeof PRODUCTS)[0];
}) {
  const [added, setAdded] = useState(false);

  function handleAdd() {
    setAdded(true);
    setTimeout(() => setAdded(false), 1800);
  }

  return (
    <article className="bg-card border border-border group flex flex-col">
      <div className="overflow-hidden aspect-[4/3] bg-muted">
        <img
          src={product.img}
          alt={`${product.brand} ${product.model} — ${product.piece}`}
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
      </div>
      <div className="p-5 flex flex-col flex-1 gap-3">
        <div>
          <span className="text-xs tracking-widest uppercase text-primary font-medium">
            {product.brand}
          </span>
          <h3
            style={displayFont}
            className="text-xl font-bold text-foreground mt-0.5 leading-tight"
          >
            {product.model}
          </h3>
          <p className="text-sm text-muted-foreground mt-1">
            {product.piece}
          </p>
        </div>
        <div className="flex items-center gap-4 text-xs text-muted-foreground">
          <span>{product.year}</span>
          <span className="w-px h-3 bg-border" />
          <span>{product.km}</span>
        </div>
        <div className="flex items-end justify-between mt-auto pt-3 border-t border-border">
          <span
            style={displayFont}
            className="text-3xl font-bold text-foreground"
          >
            {product.price}{" "}
            <span className="text-lg text-muted-foreground">
              €
            </span>
          </span>
          <button
            onClick={handleAdd}
            className={`px-4 py-2 text-xs font-semibold tracking-widest uppercase transition-all duration-200 ${
              added
                ? "bg-green-700 text-white"
                : "bg-primary text-white hover:bg-red-700"
            }`}
          >
            {added ? "Ajouté ✓" : "Ajouter"}
          </button>
        </div>
      </div>
    </article>
  );
}

// ─── Home Page ──────────────────────────────────────────────────────────────

function HomePage({ setPage }: { setPage: (p: Page) => void }) {
  return (
    <main>
      {/* Hero */}
      <section className="relative w-full" style={{ height: "100svh", minHeight: "520px" }}>
        <ImageWithFallback
          src={newHero}
          alt="FullPowerExhaust — Spécialiste de l'échappement performance"
          className="absolute inset-0 w-full h-full object-cover"
        />
        {/* Gradient scrim — left dark, fades right */}
        <div className="absolute inset-0" style={{ background: "linear-gradient(to right, rgba(13,13,13,0.92) 0%, rgba(13,13,13,0.75) 40%, rgba(13,13,13,0.2) 70%, transparent 100%)" }} />
        {/* Bottom fade to page background */}
        <div className="absolute inset-x-0 bottom-0 h-32" style={{ background: "linear-gradient(to top, #0d0d0d, transparent)" }} />

        <div className="absolute inset-0 flex items-center">
          <div className="max-w-[1320px] w-full mx-auto px-8 md:px-16">
            <p className="text-primary text-xs tracking-[0.3em] uppercase mb-5">
              Lignes d'occasion · Toutes marques
            </p>
            <h1
              style={displayFont}
              className="text-7xl md:text-9xl font-extrabold text-white leading-[0.9] tracking-tight mb-8"
            >
              SONORITÉ
              <br />
              <span className="text-primary">PERFORMANCE</span>
              <br />
              OCCASION
            </h1>
            <div className="w-10 h-0.5 bg-primary mb-8" />
            <div className="flex flex-wrap gap-4">
              <button
                onClick={() => setPage("catalog")}
                className="bg-primary text-white px-8 py-3 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200 flex items-center gap-2 rounded"
              >
                Voir le catalogue <ChevronRight size={14} />
              </button>
              <button
                onClick={() => setPage("auth")}
                className="border border-white/25 text-white/80 px-8 py-3 text-xs font-semibold tracking-widest uppercase hover:border-white hover:text-white transition-colors duration-200 rounded"
              >
                Mon compte
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Features strip */}
      <section className="border-y border-border bg-card">
        <div className="max-w-[1320px] mx-auto px-5 py-10 grid grid-cols-2 md:grid-cols-4 gap-6">
          {[
            {
              icon: (
                <Gauge size={22} className="text-primary" />
              ),
              title: "Performance",
              desc: "Contrôlée avant mise en vente",
            },
            {
              icon: (
                <Volume2 size={22} className="text-primary" />
              ),
              title: "Sonorité unique",
              desc: "Chaque ligne testée à l'oreille",
            },
            {
              icon: (
                <Shield size={22} className="text-primary" />
              ),
              title: "Qualité garantie",
              desc: "Pièces inspectées et certifiées",
            },
            {
              icon: (
                <Wrench size={22} className="text-primary" />
              ),
              title: "Expertise",
              desc: "5 ans dans l'aftermarket",
            },
          ].map((f) => (
            <div key={f.title} className="flex flex-col gap-2">
              {f.icon}
              <p
                style={displayFont}
                className="text-base font-bold tracking-wider text-foreground uppercase"
              >
                {f.title}
              </p>
              <p className="text-xs text-muted-foreground">
                {f.desc}
              </p>
            </div>
          ))}
        </div>
      </section>

      {/* Product preview */}
      <section className="max-w-[1320px] mx-auto px-5 py-20">
        <div className="flex items-end justify-between mb-10">
          <div>
            <p className="text-xs tracking-widest uppercase text-primary mb-1">
              Sélection
            </p>
            <h2
              style={displayFont}
              className="text-4xl font-extrabold text-foreground tracking-tight"
            >
              ARRIVAGES RÉCENTS
            </h2>
          </div>
          <button
            onClick={() => setPage("catalog")}
            className="hidden md:flex items-center gap-1 text-xs font-medium tracking-widest uppercase text-muted-foreground hover:text-primary transition-colors duration-200"
          >
            Tout voir <ChevronRight size={14} />
          </button>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-px bg-border">
          {PRODUCTS.slice(0, 3).map((p) => (
            <div key={p.id} className="bg-background">
              <ProductCard product={p} />
            </div>
          ))}
        </div>
        <button
          onClick={() => setPage("catalog")}
          className="mt-8 md:hidden w-full border border-border py-3 text-xs font-semibold tracking-widest uppercase text-muted-foreground hover:border-primary hover:text-primary transition-colors duration-200"
        >
          Voir tout le catalogue
        </button>
      </section>

      {/* CTA band */}
      <section className="border-t border-border bg-card">
        <div className="max-w-[1320px] mx-auto px-5 py-16 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <h2
              style={displayFont}
              className="text-3xl md:text-4xl font-extrabold text-foreground tracking-tight"
            >
              VOUS NE TROUVEZ PAS VOTRE MARQUE ?
            </h2>
            <p className="text-muted-foreground text-sm mt-2">
              Contactez-nous — nous sourçons pour toutes les
              marques européennes et asiatiques.
            </p>
          </div>
          <a
            href="mailto:contact@fullpowerexhaust.com"
            className="shrink-0 bg-primary text-white px-8 py-3 text-sm font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200"
          >
            Nous contacter
          </a>
        </div>
      </section>
    </main>
  );
}

// ─── Catalog helpers ─────────────────────────────────────────────────────────

function FacetSection({ title, open, onToggle, children }: { title: string; open: boolean; onToggle: () => void; children: React.ReactNode }) {
  return (
    <div className="border-t border-border">
      <button
        onClick={onToggle}
        className="w-full flex items-center justify-between py-3 group"
      >
        <span className="text-xs font-bold tracking-widest uppercase text-foreground group-hover:text-primary transition-colors">
          {title}
        </span>
        <svg
          width="10" height="10" viewBox="0 0 10 10" fill="currentColor"
          className={`text-muted-foreground transition-transform duration-200 ${open ? "rotate-180" : ""}`}
        >
          <path d="M1 3l4 4 4-4" stroke="currentColor" strokeWidth="1.5" fill="none" strokeLinecap="round"/>
        </svg>
      </button>
      {open && <div className="pb-4 space-y-2.5">{children}</div>}
    </div>
  );
}

function FacetCheckbox({ label, count, checked, onChange }: { label: string; count: number; checked: boolean; onChange: () => void }) {
  return (
    <label className="flex items-center justify-between cursor-pointer group gap-2">
      <div className="flex items-center gap-2.5">
        <div
          className={`w-3.5 h-3.5 border flex items-center justify-center shrink-0 transition-colors ${
            checked ? "bg-primary border-primary" : "border-muted-foreground/30 group-hover:border-primary"
          }`}
        >
          {checked && (
            <svg width="8" height="6" viewBox="0 0 8 6" fill="none">
              <path d="M1 3l2 2 4-4" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          )}
        </div>
        <span className={`text-xs leading-none transition-colors ${checked ? "text-primary font-semibold" : "text-muted-foreground group-hover:text-foreground"}`}>
          {label}
        </span>
      </div>
      <span className="text-[11px] text-muted-foreground/50 shrink-0">({count})</span>
    </label>
  );
}

function ProductListItem({ product }: { product: (typeof PRODUCTS)[0] }) {
  const [added, setAdded] = useState(false);
  function handleAdd() { setAdded(true); setTimeout(() => setAdded(false), 1800); }
  return (
    <article className="flex gap-5 p-5 bg-card group hover:bg-muted/10 transition-colors">
      <div className="w-36 h-28 shrink-0 overflow-hidden bg-muted">
        <img
          src={product.img}
          alt={`${product.brand} ${product.model}`}
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
      </div>
      <div className="flex-1 min-w-0 flex flex-col justify-between py-0.5">
        <div>
          <span className="text-xs tracking-widest uppercase text-primary font-medium">{product.brand}</span>
          <h3 style={displayFont} className="text-xl font-bold text-foreground leading-tight mt-0.5">{product.model}</h3>
          <p className="text-sm text-muted-foreground mt-0.5">{product.piece}</p>
          <div className="flex items-center gap-3 mt-2 text-xs text-muted-foreground">
            <span>{product.year}</span>
            <span className="w-px h-3 bg-border" />
            <span>{product.km}</span>
            <span className="w-px h-3 bg-border" />
            <span className="text-foreground/60">{(product as any).condition}</span>
          </div>
        </div>
        <div className="flex items-center gap-4 mt-3">
          <span style={displayFont} className="text-3xl font-bold text-foreground">
            {product.price} <span className="text-base text-muted-foreground">€</span>
          </span>
          <button
            onClick={handleAdd}
            className={`px-5 py-2 text-xs font-semibold tracking-widest uppercase transition-colors duration-200 ${
              added ? "bg-green-700 text-white" : "bg-primary text-white hover:bg-red-700"
            }`}
          >
            {added ? "Ajouté ✓" : "Ajouter au panier"}
          </button>
        </div>
      </div>
    </article>
  );
}

// ─── Catalog Page ─────────────────────────────────────────────────────────────

function CatalogPage() {
  const [filters, setFilters] = useState({
    brands: [] as string[],
    priceMin: "",
    priceMax: "",
    conditions: [] as string[],
  });
  const [sortBy, setSortBy] = useState("default");
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [openSections, setOpenSections] = useState({ brand: true, price: true, condition: true });
  const [showAllBrands, setShowAllBrands] = useState(false);
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);
  const [search, setSearch] = useState("");

  const allBrands = [...new Set(PRODUCTS.map((p) => p.brand))].sort();
  const allConditions = ["Très bon état", "Bon état", "État correct"];

  function toggleFilter(key: "brands" | "conditions", value: string) {
    setFilters((prev) => {
      const arr = prev[key];
      return { ...prev, [key]: arr.includes(value) ? arr.filter((v) => v !== value) : [...arr, value] };
    });
  }

  function clearAll() {
    setFilters({ brands: [], priceMin: "", priceMax: "", conditions: [] });
    setSearch("");
  }

  function toggleSection(key: keyof typeof openSections) {
    setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));
  }

  function getCount(key: "brand" | "condition", value: string) {
    return PRODUCTS.filter((p: any) => {
      if (key === "brand") return p.brand === value;
      if (key === "condition") return p.condition === value;
      return false;
    }).length;
  }

  const activeFilterCount =
    filters.brands.length + filters.conditions.length +
    (filters.priceMin ? 1 : 0) + (filters.priceMax ? 1 : 0) +
    (search.trim() ? 1 : 0);

  let filtered = PRODUCTS.filter((p: any) => {
    const matchBrand = filters.brands.length === 0 || filters.brands.includes(p.brand);
    const matchCond = filters.conditions.length === 0 || filters.conditions.includes(p.condition);
    const minP = filters.priceMin ? Number(filters.priceMin) : 0;
    const maxP = filters.priceMax ? Number(filters.priceMax) : 99999;
    const q = search.trim().toLowerCase();
    const matchSearch = !q || p.brand.toLowerCase().includes(q) || p.model.toLowerCase().includes(q) || p.piece.toLowerCase().includes(q);
    return matchBrand && matchCond && p.price >= minP && p.price <= maxP && matchSearch;
  });

  if (sortBy === "price-asc") filtered = [...filtered].sort((a, b) => a.price - b.price);
  if (sortBy === "price-desc") filtered = [...filtered].sort((a, b) => b.price - a.price);
  if (sortBy === "year-desc") filtered = [...filtered].sort((a, b) => b.year - a.year);
  if (sortBy === "name") filtered = [...filtered].sort((a, b) => a.brand.localeCompare(b.brand));

  const visibleBrands = showAllBrands ? allBrands : allBrands.slice(0, 6);

  const activeChips = [
    ...(search.trim() ? [{ key: "search" as const, value: "", label: `"${search.trim()}"` }] : []),
    ...filters.brands.map((v) => ({ key: "brands" as const, value: v, label: v })),
    ...filters.conditions.map((v) => ({ key: "conditions" as const, value: v, label: v })),
    ...(filters.priceMin ? [{ key: "priceMin" as const, value: "", label: `Min ${filters.priceMin} €` }] : []),
    ...(filters.priceMax ? [{ key: "priceMax" as const, value: "", label: `Max ${filters.priceMax} €` }] : []),
  ];

  const SidebarContent = () => (
    <>
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <h2 style={displayFont} className="text-sm font-bold tracking-widest uppercase text-foreground flex items-center gap-2">
          Filtres
          {activeFilterCount > 0 && (
            <span className="inline-flex items-center justify-center w-5 h-5 text-[10px] bg-primary text-white font-bold rounded-none">
              {activeFilterCount}
            </span>
          )}
        </h2>
        {activeFilterCount > 0 && (
          <button onClick={clearAll} className="text-[11px] text-primary hover:text-red-400 tracking-wide transition-colors">
            Effacer tout
          </button>
        )}
      </div>

      {/* Active chips */}
      {activeChips.length > 0 && (
        <div className="flex flex-wrap gap-1.5 mb-4 pb-4 border-b border-border">
          {activeChips.map((chip, i) => (
            <button
              key={i}
              onClick={() => {
                if (chip.key === "search") setSearch("");
                else if (chip.key === "priceMin") setFilters((p) => ({ ...p, priceMin: "" }));
                else if (chip.key === "priceMax") setFilters((p) => ({ ...p, priceMax: "" }));
                else toggleFilter(chip.key as "brands" | "conditions", chip.value);
              }}
              className="flex items-center gap-1 px-2 py-1 bg-primary/10 border border-primary/30 text-primary text-[11px] font-medium hover:bg-primary/20 transition-colors"
            >
              {chip.label}
              <X size={9} />
            </button>
          ))}
        </div>
      )}

      {/* Marque */}
      <FacetSection title="Marque" open={openSections.brand} onToggle={() => toggleSection("brand")}>
        {visibleBrands.map((brand) => (
          <FacetCheckbox
            key={brand} label={brand} count={getCount("brand", brand)}
            checked={filters.brands.includes(brand)}
            onChange={() => toggleFilter("brands", brand)}
          />
        ))}
        {allBrands.length > 6 && (
          <button
            onClick={() => setShowAllBrands((v) => !v)}
            className="text-[11px] text-primary hover:underline mt-1 block"
          >
            {showAllBrands ? "Voir moins" : `+${allBrands.length - 6} marques`}
          </button>
        )}
      </FacetSection>

      {/* Prix */}
      <FacetSection title="Prix" open={openSections.price} onToggle={() => toggleSection("price")}>
        <div className="flex items-center gap-2">
          <input
            type="number" placeholder="Min" value={filters.priceMin}
            onChange={(e) => setFilters((p) => ({ ...p, priceMin: e.target.value }))}
            className="w-full bg-input-background border border-border px-2 py-1.5 text-xs text-foreground focus:outline-none focus:border-primary transition-colors"
          />
          <span className="text-muted-foreground text-xs shrink-0">—</span>
          <input
            type="number" placeholder="Max" value={filters.priceMax}
            onChange={(e) => setFilters((p) => ({ ...p, priceMax: e.target.value }))}
            className="w-full bg-input-background border border-border px-2 py-1.5 text-xs text-foreground focus:outline-none focus:border-primary transition-colors"
          />
          <span className="text-xs text-muted-foreground shrink-0">€</span>
        </div>
        <p className="text-[11px] text-muted-foreground mt-1">
          De {Math.min(...PRODUCTS.map((p) => p.price))} € à {Math.max(...PRODUCTS.map((p) => p.price))} €
        </p>
      </FacetSection>

      {/* État */}
      <FacetSection title="État" open={openSections.condition} onToggle={() => toggleSection("condition")}>
        {allConditions.map((cond) => (
          <FacetCheckbox
            key={cond} label={cond} count={getCount("condition", cond)}
            checked={filters.conditions.includes(cond)}
            onChange={() => toggleFilter("conditions", cond)}
          />
        ))}
      </FacetSection>
    </>
  );

  return (
    <main className="pt-16 bg-background min-h-screen">
      {/* Page header */}
      <div className="border-b border-border">
        <div className="max-w-[1320px] mx-auto px-5 py-8 flex items-end justify-between">
          <div>
            <p className="text-xs tracking-widest uppercase text-primary mb-1">Stock disponible</p>
            <h1 style={displayFont} className="text-5xl font-extrabold text-foreground tracking-tight">
              CATALOGUE
            </h1>
          </div>
          {/* Mobile filter trigger */}
          <button
            onClick={() => setMobileFiltersOpen(true)}
            className="md:hidden flex items-center gap-2 border border-border px-4 py-2 text-xs font-semibold tracking-widest uppercase text-muted-foreground hover:border-primary hover:text-primary transition-colors"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="12" y1="18" x2="20" y2="18"/>
            </svg>
            Filtres {activeFilterCount > 0 && `(${activeFilterCount})`}
          </button>
        </div>
      </div>

      {/* Search bar */}
      <div className="border-b border-border bg-card">
        <div className="max-w-[1320px] mx-auto px-5 py-5">
          <div className="relative max-w-2xl mx-auto">
            <Search size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Rechercher par marque, modèle, type de pièce…"
              className="w-full bg-background border border-border pl-11 pr-10 py-3.5 text-sm text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary transition-colors rounded"
            />
            {search && (
              <button
                onClick={() => setSearch("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
              >
                <X size={14} />
              </button>
            )}
          </div>
        </div>
      </div>

      <div className="max-w-[1320px] mx-auto px-5 py-8 flex gap-8 items-start">

        {/* ── Sidebar desktop ── */}
        <aside className="w-60 shrink-0 hidden md:block">
          <SidebarContent />
        </aside>

        {/* ── Product section ── */}
        <div className="flex-1 min-w-0">
          {/* Sort & view bar */}
          <div className="flex items-center justify-between mb-5 pb-4 border-b border-border gap-4">
            <p className="text-sm text-muted-foreground shrink-0">
              <span className="text-foreground font-semibold">{filtered.length}</span>{" "}
              produit{filtered.length > 1 ? "s" : ""}
            </p>
            <div className="flex items-center gap-3 flex-wrap justify-end">
              <div className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground hidden sm:block">Trier par</span>
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                  className="bg-input-background border border-border text-xs text-foreground px-3 py-2 focus:outline-none focus:border-primary transition-colors cursor-pointer"
                >
                  <option value="default">Pertinence</option>
                  <option value="price-asc">Prix croissant</option>
                  <option value="price-desc">Prix décroissant</option>
                  <option value="year-desc">Année (récent)</option>
                  <option value="name">Marque A–Z</option>
                </select>
              </div>
              {/* View toggle */}
              <div className="flex border border-border">
                {(["grid", "list"] as const).map((mode) => (
                  <button
                    key={mode}
                    onClick={() => setViewMode(mode)}
                    className={`p-2 transition-colors ${viewMode === mode ? "bg-primary text-white" : "text-muted-foreground hover:text-foreground bg-transparent"}`}
                    title={mode === "grid" ? "Vue grille" : "Vue liste"}
                  >
                    {mode === "grid" ? (
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="3" width="8" height="8" rx="0"/><rect x="14" y="3" width="8" height="8" rx="0"/>
                        <rect x="3" y="14" width="8" height="8" rx="0"/><rect x="14" y="14" width="8" height="8" rx="0"/>
                      </svg>
                    ) : (
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="4" width="18" height="3"/><rect x="3" y="11" width="18" height="3"/><rect x="3" y="18" width="18" height="3"/>
                      </svg>
                    )}
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Results */}
          {filtered.length === 0 ? (
            <div className="py-20 text-center">
              <p className="text-muted-foreground text-sm mb-3">Aucun produit ne correspond à vos critères.</p>
              <button onClick={clearAll} className="text-primary text-xs font-semibold tracking-widest uppercase hover:underline">
                Effacer les filtres
              </button>
            </div>
          ) : viewMode === "grid" ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-px bg-border">
              {filtered.map((p) => (
                <div key={p.id} className="bg-background">
                  <ProductCard product={p} />
                </div>
              ))}
            </div>
          ) : (
            <div className="flex flex-col gap-px bg-border">
              {filtered.map((p) => (
                <div key={p.id} className="bg-background">
                  <ProductListItem product={p} />
                </div>
              ))}
            </div>
          )}

          <p className="mt-6 text-xs text-muted-foreground">
            {filtered.length} résultat{filtered.length > 1 ? "s" : ""} affiché{filtered.length > 1 ? "s" : ""}
          </p>
        </div>
      </div>

      {/* Mobile filter drawer */}
      {mobileFiltersOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden">
          <div className="flex-1 bg-black/50" onClick={() => setMobileFiltersOpen(false)} />
          <div className="w-72 bg-background border-l border-border flex flex-col overflow-y-auto p-5">
            <div className="flex items-center justify-between mb-4">
              <span style={displayFont} className="text-lg font-bold tracking-widest uppercase text-foreground">Filtres</span>
              <button onClick={() => setMobileFiltersOpen(false)} className="text-muted-foreground hover:text-foreground">
                <X size={20} />
              </button>
            </div>
            <SidebarContent />
            <button
              onClick={() => setMobileFiltersOpen(false)}
              className="mt-6 bg-primary text-white py-3 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors"
            >
              Voir les {filtered.length} résultat{filtered.length > 1 ? "s" : ""}
            </button>
          </div>
        </div>
      )}
    </main>
  );
}

// ─── Auth Page ──────────────────────────────────────────────────────────────

function AuthPage() {
  const [tab, setTab] = useState<AuthTab>("login");
  const [loginData, setLoginData] = useState({ email: "", password: "" });
  const [registerData, setRegisterData] = useState({ firstname: "", lastname: "", email: "", password: "", confirm: "", accept: false });
  const [submitted, setSubmitted] = useState(false);
  const [oauthLoading, setOauthLoading] = useState<"google" | "facebook" | null>(null);
  const [oauthMsg, setOauthMsg] = useState("");

  function handleLogin(e: React.FormEvent) {
    e.preventDefault();
    setSubmitted(true);
    setTimeout(() => setSubmitted(false), 2000);
  }

  function handleRegister(e: React.FormEvent) {
    e.preventDefault();
    setSubmitted(true);
    setTimeout(() => setSubmitted(false), 2000);
  }

  function handleOAuth(provider: "google" | "facebook") {
    setOauthLoading(provider);
    setOauthMsg("");
    // TODO: replace with real OAuth flow
    // Google  → import { GoogleAuthProvider, signInWithPopup } from "firebase/auth"
    // Facebook→ import { FacebookAuthProvider, signInWithPopup } from "firebase/auth"
    setTimeout(() => {
      setOauthLoading(null);
      setOauthMsg(`Connexion via ${provider === "google" ? "Google" : "Facebook"} — à configurer avec Firebase ou votre provider OAuth.`);
    }, 1400);
  }

  return (
    <main className="pt-28 pb-20 max-w-md mx-auto px-5">
      <p className="text-xs tracking-widest uppercase text-primary mb-1">
        Espace client
      </p>
      <h1
        style={displayFont}
        className="text-4xl font-extrabold text-foreground tracking-tight mb-8"
      >
        MON COMPTE
      </h1>

      {/* Tabs */}
      <div className="flex border-b border-border mb-8">
        {(["login", "register"] as AuthTab[]).map((t) => (
          <button
            key={t}
            onClick={() => {
              setTab(t);
              setSubmitted(false);
            }}
            className={`flex-1 pb-3 text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ${
              tab === t
                ? "text-foreground border-b border-primary -mb-px"
                : "text-muted-foreground"
            }`}
          >
            {t === "login" ? "Connexion" : "Inscription"}
          </button>
        ))}
      </div>

      {/* OAuth buttons */}
      <div className="flex flex-col gap-3 mb-6">
        <button
          onClick={() => handleOAuth("google")}
          disabled={oauthLoading !== null}
          className="flex items-center justify-center gap-3 w-full border border-border bg-card hover:border-white/30 hover:bg-muted/30 transition-colors duration-150 py-3 text-xs font-medium tracking-wide text-foreground disabled:opacity-50 rounded"
        >
          {oauthLoading === "google" ? (
            <svg className="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" strokeOpacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" /></svg>
          ) : (
            <svg width="16" height="16" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          )}
          Continuer avec Google
        </button>

        <button
          onClick={() => handleOAuth("facebook")}
          disabled={oauthLoading !== null}
          className="flex items-center justify-center gap-3 w-full border border-border bg-card hover:border-white/30 hover:bg-muted/30 transition-colors duration-150 py-3 text-xs font-medium tracking-wide text-foreground disabled:opacity-50 rounded"
        >
          {oauthLoading === "facebook" ? (
            <svg className="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" strokeOpacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" /></svg>
          ) : (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.269h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
          )}
          Continuer avec Facebook
        </button>
      </div>

      {oauthMsg && (
        <div className="flex items-start gap-2 text-xs text-amber-400 bg-amber-900/15 border border-amber-800/40 px-4 py-3 mb-6">
          <AlertCircle size={13} className="mt-0.5 shrink-0" />
          {oauthMsg}
        </div>
      )}

      {/* Divider */}
      <div className="flex items-center gap-4 mb-6">
        <div className="flex-1 h-px bg-border" />
        <span className="text-xs text-muted-foreground tracking-widest uppercase">ou</span>
        <div className="flex-1 h-px bg-border" />
      </div>

      {submitted && (
        <div className="flex items-center gap-2 text-xs text-green-400 bg-green-900/20 border border-green-800 px-4 py-3 mb-6">
          <Check size={14} />
          {tab === "login"
            ? "Connexion simulée avec succès."
            : "Compte créé avec succès."}
        </div>
      )}

      {tab === "login" ? (
        <form
          onSubmit={handleLogin}
          className="flex flex-col gap-5"
        >
          <div className="flex flex-col gap-1.5">
            <label className="text-xs tracking-widest uppercase text-muted-foreground">
              Adresse e-mail
            </label>
            <input
              type="email"
              required
              value={loginData.email}
              onChange={(e) =>
                setLoginData({
                  ...loginData,
                  email: e.target.value,
                })
              }
              className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
              placeholder="votre@email.com"
            />
          </div>
          <div className="flex flex-col gap-1.5">
            <label className="text-xs tracking-widest uppercase text-muted-foreground">
              Mot de passe
            </label>
            <input
              type="password"
              required
              value={loginData.password}
              onChange={(e) =>
                setLoginData({
                  ...loginData,
                  password: e.target.value,
                })
              }
              className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
              placeholder="••••••••"
            />
          </div>
          <button
            type="submit"
            className="mt-2 bg-primary text-white py-3 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200 rounded"
          >
            Se connecter
          </button>
          <button
            type="button"
            className="text-xs text-muted-foreground hover:text-primary transition-colors duration-150 text-center"
          >
            Mot de passe oublié ?
          </button>
        </form>
      ) : (
        <form
          onSubmit={handleRegister}
          className="flex flex-col gap-5"
        >
          <div className="grid grid-cols-2 gap-4">
            <div className="flex flex-col gap-1.5">
              <label className="text-xs tracking-widest uppercase text-muted-foreground">
                Prénom
              </label>
              <input
                type="text"
                required
                value={registerData.firstname}
                onChange={(e) =>
                  setRegisterData({
                    ...registerData,
                    firstname: e.target.value,
                  })
                }
                className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
                placeholder="Jean"
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-xs tracking-widest uppercase text-muted-foreground">
                Nom
              </label>
              <input
                type="text"
                required
                value={registerData.lastname}
                onChange={(e) =>
                  setRegisterData({
                    ...registerData,
                    lastname: e.target.value,
                  })
                }
                className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
                placeholder="Dupont"
              />
            </div>
          </div>
          <div className="flex flex-col gap-1.5">
            <label className="text-xs tracking-widest uppercase text-muted-foreground">
              Adresse e-mail
            </label>
            <input
              type="email"
              required
              value={registerData.email}
              onChange={(e) =>
                setRegisterData({
                  ...registerData,
                  email: e.target.value,
                })
              }
              className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
              placeholder="votre@email.com"
            />
          </div>
          <div className="flex flex-col gap-1.5">
            <label className="text-xs tracking-widest uppercase text-muted-foreground">
              Mot de passe
            </label>
            <input
              type="password"
              required
              value={registerData.password}
              onChange={(e) =>
                setRegisterData({
                  ...registerData,
                  password: e.target.value,
                })
              }
              className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
              placeholder="Min. 8 caractères"
            />
          </div>
          <div className="flex flex-col gap-1.5">
            <label className="text-xs tracking-widest uppercase text-muted-foreground">
              Confirmer
            </label>
            <input
              type="password"
              required
              value={registerData.confirm}
              onChange={(e) =>
                setRegisterData({
                  ...registerData,
                  confirm: e.target.value,
                })
              }
              className="bg-input-background border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary transition-colors duration-150 rounded"
              placeholder="••••••••"
            />
          </div>
          <label className="flex items-start gap-3 cursor-pointer">
            <input
              type="checkbox"
              required
              checked={registerData.accept}
              onChange={(e) =>
                setRegisterData({
                  ...registerData,
                  accept: e.target.checked,
                })
              }
              className="mt-0.5 accent-primary"
            />
            <span className="text-xs text-muted-foreground leading-relaxed">
              {"J'accepte les "}
              <span className="text-primary underline cursor-pointer">
                CGV
              </span>
              {", les "}
              <span className="text-primary underline cursor-pointer">
                CGU
              </span>
              {" et la "}
              <span className="text-primary underline cursor-pointer">
                politique de confidentialité
              </span>
              .
            </span>
          </label>
          <button
            type="submit"
            className="mt-2 bg-primary text-white py-3 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200 rounded"
          >
            Créer mon compte
          </button>
        </form>
      )}
    </main>
  );
}

// ─── Legal Layout ───────────────────────────────────────────────────────────

function LegalLayout({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <main className="max-w-3xl mx-auto px-5 pt-28 pb-20">
      <p className="text-xs tracking-widest uppercase text-primary mb-1">
        Informations légales
      </p>
      <h1
        style={displayFont}
        className="text-4xl font-extrabold text-foreground tracking-tight mb-10"
      >
        {title}
      </h1>
      <div className="prose prose-sm max-w-none text-sm text-muted-foreground leading-relaxed space-y-6">
        {children}
      </div>
    </main>
  );
}

function LegalSection({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <div>
      <h2
        style={displayFont}
        className="text-xl font-bold text-foreground tracking-wide mb-3 mt-8"
      >
        {title}
      </h2>
      <div className="space-y-3">{children}</div>
    </div>
  );
}

// ─── CGV ────────────────────────────────────────────────────────────────────

function CGVPage() {
  return (
    <LegalLayout title="CONDITIONS GÉNÉRALES DE VENTE">
      <p>
        Dernière mise à jour : 1er janvier 2025. Société
        FULLPOWEREXHAUST, e-commerce de pièces d'échappement
        d'occasion, immatriculée au RCS de Paris.
      </p>

      <LegalSection title="Article 1 — Objet et champ d'application">
        <p>
          Les présentes Conditions Générales de Vente (CGV)
          régissent l'ensemble des ventes de pièces
          d'échappement d'occasion réalisées via le site
          fullpowerexhaust.com. Toute commande passée implique
          l'acceptation pleine et entière des présentes CGV.
        </p>
      </LegalSection>

      <LegalSection title="Article 2 — Produits">
        <p>
          Tous les produits proposés sur le site sont des pièces
          d'occasion ayant été contrôlées visuellement et
          fonctionnellement avant mise en vente. Les photos et
          descriptions sont indicatives. Les pièces sont vendues
          en l'état, sans garantie constructeur. Un contrôle
          visuel est effectué par nos soins avant expédition.
        </p>
        <p>
          FULLPOWEREXHAUST se réserve le droit de retirer tout
          produit jugé non conforme à la vente.
        </p>
      </LegalSection>

      <LegalSection title="Article 3 — Prix">
        <p>
          Les prix sont indiqués en euros TTC. Les frais de
          livraison sont calculés et affichés au moment de la
          validation du panier. FULLPOWEREXHAUST se réserve le
          droit de modifier ses prix à tout moment, sans
          préavis. Les commandes sont facturées au prix en
          vigueur au moment de la validation.
        </p>
      </LegalSection>

      <LegalSection title="Article 4 — Commandes et paiement">
        <p>
          La commande est définitive à réception du paiement
          intégral. Modes de paiement acceptés : carte bancaire
          (Visa, Mastercard), PayPal, virement bancaire. En cas
          de refus de paiement, la commande est automatiquement
          annulée.
        </p>
      </LegalSection>

      <LegalSection title="Article 5 — Livraison">
        <p>
          Les délais de livraison sont indicatifs. Expédition
          sous 3 à 5 jours ouvrés après réception du paiement.
          FULLPOWEREXHAUST n'est pas responsable des retards
          imputables au transporteur. Tout colis endommagé doit
          être refusé à la livraison ou signalé par écrit au
          transporteur.
        </p>
      </LegalSection>

      <LegalSection title="Article 6 — Droit de rétractation">
        <p>
          Conformément à l'article L221-18 du Code de la
          consommation, le client dispose d'un délai de 14 jours
          calendaires à compter de la réception pour exercer son
          droit de rétractation, sans justification. Ce délai ne
          s'applique pas aux pièces endommagées ou montées après
          réception.
        </p>
        <p>
          Pour exercer ce droit, contactez-nous à
          retours@fullpowerexhaust.com. Les frais de retour sont à
          la charge du client. Voir notre politique de retour
          complète pour les conditions d'éligibilité.
        </p>
      </LegalSection>

      <LegalSection title="Article 7 — Garantie légale">
        <p>
          Conformément aux articles L217-4 et suivants du Code
          de la consommation, les produits bénéficient de la
          garantie légale de conformité. Pour les défauts
          cachés, la garantie légale s'applique selon l'article
          1641 du Code civil. Les pièces d'occasion bénéficient
          d'une garantie de 6 mois à compter de la livraison.
        </p>
      </LegalSection>

      <LegalSection title="Article 8 — Responsabilité">
        <p>
          FULLPOWEREXHAUST ne saurait être tenu responsable d'une
          mauvaise utilisation des pièces, d'une installation
          non conforme aux normes, ou de dommages indirects
          résultant de l'utilisation des produits. Le montage
          des pièces doit être réalisé par un professionnel
          habilité.
        </p>
      </LegalSection>

      <LegalSection title="Article 9 — Litiges">
        <p>
          En cas de litige, les parties s'efforceront de trouver
          une solution amiable. À défaut, les tribunaux
          compétents seront ceux du ressort du siège social de
          FULLPOWEREXHAUST. Le droit applicable est le droit
          français. Médiateur : FEVAD (www.fevad.com).
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── CGU ────────────────────────────────────────────────────────────────────

function CGUPage() {
  return (
    <LegalLayout title="CONDITIONS GÉNÉRALES D'UTILISATION">
      <p>
        Les présentes CGU définissent les conditions d'accès et
        d'utilisation du site fullpowerexhaust.com.
      </p>

      <LegalSection title="Article 1 — Accès au site">
        <p>
          L'accès au site est libre et gratuit. FULLPOWEREXHAUST se
          réserve le droit de suspendre ou d'interrompre l'accès
          au site pour maintenance ou force majeure, sans
          préavis ni indemnité.
        </p>
      </LegalSection>

      <LegalSection title="Article 2 — Création de compte">
        <p>
          La création d'un compte est nécessaire pour passer
          commande. L'utilisateur s'engage à fournir des
          informations exactes et à maintenir ses identifiants
          confidentiels. Tout accès frauduleux engage la
          responsabilité de l'utilisateur.
        </p>
      </LegalSection>

      <LegalSection title="Article 3 — Propriété intellectuelle">
        <p>
          L'ensemble des éléments du site (textes, images,
          logos, structure) est la propriété exclusive de
          FULLPOWEREXHAUST. Toute reproduction ou utilisation sans
          autorisation écrite est strictement interdite et
          constitue une contrefaçon.
        </p>
      </LegalSection>

      <LegalSection title="Article 4 — Comportement utilisateur">
        <p>
          Il est interdit d'utiliser le site à des fins
          illicites, de perturber son fonctionnement, ou de
          publier des contenus abusifs. FULLPOWEREXHAUST se réserve
          le droit de suspendre tout compte en violation de ces
          règles.
        </p>
      </LegalSection>

      <LegalSection title="Article 5 — Liens externes">
        <p>
          Le site peut contenir des liens vers des sites tiers.
          FULLPOWEREXHAUST n'est pas responsable du contenu de ces
          sites et décline toute responsabilité quant à leur
          utilisation.
        </p>
      </LegalSection>

      <LegalSection title="Article 6 — Modification des CGU">
        <p>
          FULLPOWEREXHAUST se réserve le droit de modifier les
          présentes CGU à tout moment. Les utilisateurs seront
          informés par email ou notification sur le site. La
          poursuite de l'utilisation du site après modification
          vaut acceptation des nouvelles CGU.
        </p>
      </LegalSection>

      <LegalSection title="Article 7 — Droit applicable">
        <p>
          Les présentes CGU sont soumises au droit français.
          Tout litige relève de la compétence des tribunaux
          français.
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Privacy ─────────────────────────────────────────────────────────────────

function PrivacyPage() {
  return (
    <LegalLayout title="POLITIQUE DE CONFIDENTIALITÉ">
      <p>
        FULLPOWEREXHAUST s'engage à protéger vos données
        personnelles conformément au RGPD (Règlement UE
        2016/679) et à la loi Informatique et Libertés.
      </p>

      <LegalSection title="1. Responsable du traitement">
        <p>
          FULLPOWEREXHAUST — contact@fullpowerexhaust.com. DPO :
          dpo@fullpowerexhaust.com.
        </p>
      </LegalSection>

      <LegalSection title="2. Données collectées">
        <p>
          Nous collectons les données suivantes : nom, prénom,
          adresse e-mail, adresse postale, numéro de téléphone
          (facultatif), données de navigation (cookies
          techniques), historique de commandes.
        </p>
        <p>
          Nous ne collectons aucune donnée sensible. Le paiement
          est traité par des prestataires certifiés PCI-DSS —
          nous ne stockons aucune donnée bancaire.
        </p>
      </LegalSection>

      <LegalSection title="3. Finalités du traitement">
        <p>
          Vos données sont utilisées pour : traitement et suivi
          des commandes, gestion de votre compte client,
          communication transactionnelle (confirmation,
          expédition), obligations légales et comptables,
          amélioration de notre service.
        </p>
        <p>
          Avec votre consentement explicite : envoi de
          newsletters et offres commerciales.
        </p>
      </LegalSection>

      <LegalSection title="4. Durée de conservation">
        <p>
          Données de compte : 3 ans après la dernière commande.
          Données de facturation : 10 ans (obligations légales).
          Données de navigation : 13 mois maximum.
        </p>
      </LegalSection>

      <LegalSection title="5. Partage des données">
        <p>
          Vos données ne sont pas vendues. Elles peuvent être
          transmises à : nos prestataires logistiques
          (livraison), notre prestataire de paiement, nos outils
          d'analyse (anonymisés). Tous nos sous-traitants sont
          soumis à des engagements de confidentialité stricts.
        </p>
      </LegalSection>

      <LegalSection title="6. Vos droits">
        <p>
          Conformément au RGPD, vous disposez des droits
          suivants : accès, rectification, effacement,
          limitation, portabilité, opposition. Pour exercer ces
          droits, contactez-nous à dpo@fullpowerexhaust.com. Vous
          pouvez également introduire une réclamation auprès de
          la CNIL (cnil.fr).
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Cookies ─────────────────────────────────────────────────────────────────

function CookiesPage() {
  const [analytics, setAnalytics] = useState(false);
  const [marketing, setMarketing] = useState(false);
  const [saved, setSaved] = useState(false);

  function savePrefs() {
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  return (
    <LegalLayout title="POLITIQUE DE COOKIES">
      <p>
        En naviguant sur fullpowerexhaust.com, vous acceptez
        l'utilisation de cookies conformément à la présente
        politique.
      </p>

      <LegalSection title="Qu'est-ce qu'un cookie ?">
        <p>
          Un cookie est un petit fichier déposé sur votre
          terminal lors de la visite d'un site web. Il permet de
          reconnaître votre navigateur lors de visites
          ultérieures et d'améliorer votre expérience.
        </p>
      </LegalSection>

      <LegalSection title="Cookies essentiels — Toujours actifs">
        <p>
          Ces cookies sont strictement nécessaires au
          fonctionnement du site : gestion de session, panier,
          authentification, sécurité. Ils ne peuvent pas être
          désactivés.
        </p>
        <div className="flex items-center justify-between bg-card border border-border px-4 py-3 mt-3">
          <span className="text-foreground text-xs font-medium">
            Cookies fonctionnels
          </span>
          <span className="text-xs text-primary font-semibold tracking-widest uppercase">
            Actif
          </span>
        </div>
      </LegalSection>

      <LegalSection title="Cookies analytiques — Optionnels">
        <p>
          Ces cookies nous permettent de mesurer l'audience du
          site (pages vues, sources de trafic) via Google
          Analytics. Les données sont anonymisées.
        </p>
        <div className="flex items-center justify-between bg-card border border-border px-4 py-3 mt-3">
          <span className="text-foreground text-xs font-medium">
            Google Analytics
          </span>
          <button
            onClick={() => setAnalytics((v) => !v)}
            className={`relative w-10 h-5 transition-colors duration-200 ${analytics ? "bg-primary" : "bg-muted"}`}
          >
            <span
              className={`absolute top-0.5 w-4 h-4 bg-white transition-transform duration-200 ${analytics ? "translate-x-5" : "translate-x-0.5"}`}
            />
          </button>
        </div>
      </LegalSection>

      <LegalSection title="Cookies marketing — Optionnels">
        <p>
          Ces cookies servent à personnaliser les publicités sur
          d'autres sites et à mesurer l'efficacité de nos
          campagnes (Meta Pixel, Google Ads).
        </p>
        <div className="flex items-center justify-between bg-card border border-border px-4 py-3 mt-3">
          <span className="text-foreground text-xs font-medium">
            Publicité ciblée
          </span>
          <button
            onClick={() => setMarketing((v) => !v)}
            className={`relative w-10 h-5 transition-colors duration-200 ${marketing ? "bg-primary" : "bg-muted"}`}
          >
            <span
              className={`absolute top-0.5 w-4 h-4 bg-white transition-transform duration-200 ${marketing ? "translate-x-5" : "translate-x-0.5"}`}
            />
          </button>
        </div>
      </LegalSection>

      <LegalSection title="Gestion de vos préférences">
        <p>
          Vous pouvez modifier vos préférences à tout moment via
          ce panneau ou via les paramètres de votre navigateur.
          La suppression des cookies peut affecter certaines
          fonctionnalités du site.
        </p>
        <button
          onClick={savePrefs}
          className="mt-4 bg-primary text-white px-6 py-2.5 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200"
        >
          {saved
            ? "Préférences sauvegardées ✓"
            : "Sauvegarder mes préférences"}
        </button>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Refund ───────────────────────────────────────────────────────────────────

function RefundPage() {
  return (
    <LegalLayout title="POLITIQUE DE REMBOURSEMENT">
      <div className="flex items-start gap-3 bg-card border border-primary/30 px-4 py-4">
        <AlertCircle
          size={18}
          className="text-primary shrink-0 mt-0.5"
        />
        <p className="text-foreground text-sm">
          En raison de la nature des pièces vendues (occasion),
          notre politique de remboursement est stricte. Lisez
          attentivement les conditions ci-dessous avant tout
          achat.
        </p>
      </div>

      <LegalSection title="1. Droit légal de rétractation (14 jours)">
        <p>
          Conformément à la loi, vous disposez de 14 jours
          calendaires à compter de la réception pour vous
          rétracter, sans motif. Ce droit est soumis aux
          conditions strictes suivantes :
        </p>
        <ul className="list-none space-y-2 mt-2">
          {[
            "La pièce n'a pas été montée ni essayée sur le véhicule.",
            "La pièce est retournée dans son emballage d'origine, en parfait état.",
            "Aucune modification ou découpe n'a été effectuée sur la pièce.",
            "La demande est formulée par écrit dans les 14 jours (retours@fullpowerexhaust.com).",
          ].map((item) => (
            <li
              key={item}
              className="flex items-start gap-2 text-sm"
            >
              <ChevronRight
                size={14}
                className="text-primary shrink-0 mt-0.5"
              />
              <span>{item}</span>
            </li>
          ))}
        </ul>
      </LegalSection>

      <LegalSection title="2. Remboursement pour défaut caché">
        <p>
          Si la pièce présente un défaut caché non visible lors
          de la réception et non mentionné dans la description
          (ex : fissure interne, corrosion masquée), un
          remboursement intégral est accordé sous conditions :
        </p>
        <ul className="list-none space-y-2 mt-2">
          {[
            "Signalement sous 48h après réception (photos exigées).",
            "Rapport d'un garagiste certifié attestant le défaut (facultatif mais recommandé).",
            "Retour de la pièce à nos frais après validation du dossier.",
          ].map((item) => (
            <li
              key={item}
              className="flex items-start gap-2 text-sm"
            >
              <ChevronRight
                size={14}
                className="text-primary shrink-0 mt-0.5"
              />
              <span>{item}</span>
            </li>
          ))}
        </ul>
      </LegalSection>

      <LegalSection title="3. Cas exclus du remboursement">
        <p>
          Aucun remboursement ne sera accordé dans les cas
          suivants :
        </p>
        <ul className="list-none space-y-2 mt-2">
          {[
            "Pièce montée, même partiellement.",
            "Incompatibilité avec le véhicule non vérifiée avant l'achat.",
            "Dommages causés lors du montage ou de l'utilisation.",
            "Rouille ou usure supplémentaire constatée après réception.",
            "Demande formulée après le délai de 14 jours.",
            "Pièce retournée sans autorisation préalable de notre service client.",
          ].map((item) => (
            <li
              key={item}
              className="flex items-start gap-2 text-sm"
            >
              <ChevronRight
                size={14}
                className="text-primary shrink-0 mt-0.5"
              />
              <span>{item}</span>
            </li>
          ))}
        </ul>
      </LegalSection>

      <LegalSection title="4. Modalités de remboursement">
        <p>
          Le remboursement est effectué sous 14 jours ouvrés
          après réception et validation de la pièce retournée,
          sur le moyen de paiement d'origine. Les frais de
          livraison initiaux ne sont pas remboursés, sauf en cas
          de défaut caché avéré.
        </p>
      </LegalSection>

      <LegalSection title="5. Contact">
        <p>
          Pour toute demande : retours@fullpowerexhaust.com — Objet
          : REMBOURSEMENT + numéro de commande.
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Returns ─────────────────────────────────────────────────────────────────

function ReturnsPage() {
  return (
    <LegalLayout title="CONDITIONS DE RETOUR">
      <LegalSection title="1. Procédure obligatoire">
        <p>
          Tout retour doit être préalablement autorisé par notre
          service client. Aucun colis non sollicité ne sera
          accepté. Pour initier un retour :
        </p>
        <div className="space-y-3 mt-4">
          {[
            {
              n: "01",
              text: "Envoyez un e-mail à retours@fullpowerexhaust.com avec votre numéro de commande et le motif du retour.",
            },
            {
              n: "02",
              text: "Attendez notre confirmation et le numéro de retour (RMA) sous 48h ouvrées.",
            },
            {
              n: "03",
              text: "Emballez soigneusement la pièce et apposez l'étiquette de retour fournie.",
            },
            {
              n: "04",
              text: "Déposez le colis chez le transporteur indiqué dans les 5 jours suivant l'obtention du RMA.",
            },
          ].map((step) => (
            <div
              key={step.n}
              className="flex gap-4 bg-card border border-border px-4 py-3"
            >
              <span
                style={displayFont}
                className="text-3xl font-bold text-primary leading-none shrink-0"
              >
                {step.n}
              </span>
              <p className="text-sm text-muted-foreground">
                {step.text}
              </p>
            </div>
          ))}
        </div>
      </LegalSection>

      <LegalSection title="2. État de la pièce retournée">
        <p>
          La pièce doit être retournée dans l'état exact dans
          lequel elle a été reçue :
        </p>
        <ul className="list-none space-y-2 mt-2">
          {[
            "Non montée, non modifiée, non découpée.",
            "Propre — sans graisse supplémentaire ni produits chimiques.",
            "Emballée de manière à éviter tout dommage lors du transport (mousse, carton renforcé).",
            "Accompagnée d'une copie de la facture et du numéro RMA.",
          ].map((item) => (
            <li
              key={item}
              className="flex items-start gap-2 text-sm"
            >
              <Check
                size={14}
                className="text-primary shrink-0 mt-0.5"
              />
              <span>{item}</span>
            </li>
          ))}
        </ul>
      </LegalSection>

      <LegalSection title="3. Frais de retour">
        <p>
          Les frais de retour sont à la charge du client, sauf
          en cas de défaut caché avéré ou d'erreur de notre part
          (mauvaise pièce expédiée). Dans ce cas, nous
          fournissons une étiquette prépayée.
        </p>
        <p>
          Nous recommandons l'envoi avec suivi et assurance pour
          les pièces de valeur supérieure à 100 €. FULLPOWEREXHAUST
          ne sera pas responsable des colis perdus ou endommagés
          lors du retour.
        </p>
      </LegalSection>

      <LegalSection title="4. Délai de traitement">
        <p>
          À réception de la pièce, notre équipe procède au
          contrôle sous 3 jours ouvrés. En cas de conformité, le
          remboursement ou l'échange est traité selon notre
          politique de remboursement. Tout retour non conforme
          sera renvoyé à l'expéditeur à ses frais.
        </p>
      </LegalSection>

      <LegalSection title="5. Échanges">
        <p>
          Les échanges sont possibles uniquement si la pièce
          demandée est disponible en stock. Dans le cas
          contraire, un remboursement sera effectué.
          Contactez-nous avant de retourner la pièce pour
          vérifier la disponibilité de l'échange souhaité.
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Contact Page ─────────────────────────────────────────────────────────────

function ContactPage() {
  const [form, setForm] = useState({ name: "", email: "", subject: "", message: "" });
  const [sent, setSent] = useState(false);
  const [error, setError] = useState("");

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!form.name || !form.email || !form.message) {
      setError("Veuillez remplir tous les champs obligatoires.");
      return;
    }
    setSent(true);
    setError("");
  }

  return (
    <main className="pt-24 pb-20 bg-background min-h-screen">
      <div className="max-w-[1320px] mx-auto px-5">
        {/* Page header */}
        <div className="mb-12">
          <p className="text-xs tracking-widest uppercase text-primary mb-1">Nous écrire</p>
          <h1 style={displayFont} className="text-5xl font-extrabold text-foreground tracking-tight">
            CONTACT
          </h1>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          {/* Infos */}
          <div>
            <p className="text-sm text-muted-foreground leading-relaxed mb-10 max-w-md">
              Une question sur une pièce, un délai d&apos;expédition ou une demande spécifique ? Notre équipe vous répond sous 24 h ouvrées.
            </p>

            <div className="space-y-6">
              {[
                {
                  icon: (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                  ),
                  label: "Email",
                  value: "contact@fullpowerexhaust.com",
                },
                {
                  icon: (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                  ),
                  label: "Téléphone",
                  value: "+33 1 23 45 67 89",
                },
                {
                  icon: (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  ),
                  label: "Adresse",
                  value: "Paris, France",
                },
                {
                  icon: (
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                  ),
                  label: "Horaires",
                  value: "Lun–Ven, 9h–18h",
                },
              ].map((item) => (
                <div key={item.label} className="flex items-start gap-4">
                  <div className="w-10 h-10 border border-border flex items-center justify-center text-primary shrink-0">
                    {item.icon}
                  </div>
                  <div>
                    <p className="text-xs tracking-widest uppercase text-muted-foreground mb-0.5">{item.label}</p>
                    <p className="text-sm text-foreground">{item.value}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Form */}
          <div>
            {sent ? (
              <div className="border border-primary/30 bg-primary/5 p-8 flex flex-col items-start gap-4">
                <div className="w-10 h-10 bg-primary flex items-center justify-center">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </div>
                <div>
                  <p style={displayFont} className="text-2xl font-bold text-foreground mb-1">Message envoyé !</p>
                  <p className="text-sm text-muted-foreground">Nous vous répondrons dans les 24 h ouvrées.</p>
                </div>
                <button
                  onClick={() => { setSent(false); setForm({ name: "", email: "", subject: "", message: "" }); }}
                  className="text-xs text-primary hover:underline tracking-wide mt-2"
                >
                  Envoyer un autre message
                </button>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                {error && (
                  <p className="text-xs text-red-500 border border-red-500/30 bg-red-500/5 px-4 py-3">{error}</p>
                )}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs tracking-widest uppercase text-muted-foreground mb-1.5">
                      Nom <span className="text-primary">*</span>
                    </label>
                    <input
                      type="text"
                      value={form.name}
                      onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))}
                      placeholder="Votre nom"
                      className="w-full bg-card border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary transition-colors rounded"
                    />
                  </div>
                  <div>
                    <label className="block text-xs tracking-widest uppercase text-muted-foreground mb-1.5">
                      Email <span className="text-primary">*</span>
                    </label>
                    <input
                      type="email"
                      value={form.email}
                      onChange={(e) => setForm((p) => ({ ...p, email: e.target.value }))}
                      placeholder="votre@email.com"
                      className="w-full bg-card border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary transition-colors rounded"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs tracking-widest uppercase text-muted-foreground mb-1.5">Sujet</label>
                  <input
                    type="text"
                    value={form.subject}
                    onChange={(e) => setForm((p) => ({ ...p, subject: e.target.value }))}
                    placeholder="Objet de votre message"
                    className="w-full bg-card border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary transition-colors rounded"
                  />
                </div>
                <div>
                  <label className="block text-xs tracking-widest uppercase text-muted-foreground mb-1.5">
                    Message <span className="text-primary">*</span>
                  </label>
                  <textarea
                    rows={6}
                    value={form.message}
                    onChange={(e) => setForm((p) => ({ ...p, message: e.target.value }))}
                    placeholder="Décrivez votre demande..."
                    className="w-full bg-card border border-border px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary transition-colors resize-none rounded"
                  />
                </div>
                <button
                  type="submit"
                  className="bg-primary text-white px-8 py-3 text-xs font-semibold tracking-widest uppercase hover:bg-red-700 transition-colors duration-200"
                >
                  Envoyer le message
                </button>
                <p className="text-[11px] text-muted-foreground">
                  * Champs obligatoires. Vos données ne seront jamais revendues.
                </p>
              </form>
            )}
          </div>
        </div>
      </div>
    </main>
  );
}

// ─── Mentions Légales Page ────────────────────────────────────────────────────

function MentionsLegalesPage() {
  return (
    <LegalLayout title="MENTIONS LÉGALES">
      <p>
        Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance en l'économie numérique, il est précisé aux utilisateurs du site fullpowerexhaust.com l'identité des différents intervenants dans le cadre de sa réalisation et de son suivi.
      </p>

      <LegalSection title="1. Éditeur du site">
        <p>Le site fullpowerexhaust.com est édité par la société FULLPOWEREXHAUST.</p>
        <ul>
          <li><strong>Forme juridique :</strong> SARL</li>
          <li><strong>Capital social :</strong> 10 000 €</li>
          <li><strong>Siège social :</strong> Paris, France</li>
          <li><strong>RCS :</strong> Paris B XXX XXX XXX</li>
          <li><strong>N° TVA intracommunautaire :</strong> FR XX XXX XXX XXX</li>
          <li><strong>Email :</strong> contact@fullpowerexhaust.com</li>
          <li><strong>Téléphone :</strong> +33 1 23 45 67 89</li>
        </ul>
      </LegalSection>

      <LegalSection title="2. Directeur de la publication">
        <p>
          Le directeur de la publication du site fullpowerexhaust.com est le gérant de la société FULLPOWEREXHAUST.
        </p>
      </LegalSection>

      <LegalSection title="3. Hébergement">
        <p>
          Le site est hébergé par un prestataire d'hébergement professionnel dont les coordonnées sont disponibles sur demande à l'adresse contact@fullpowerexhaust.com.
        </p>
      </LegalSection>

      <LegalSection title="4. Propriété intellectuelle">
        <p>
          L'ensemble du contenu du site fullpowerexhaust.com (textes, images, graphismes, logo, icônes, sons, logiciels…) est la propriété exclusive de FULLPOWEREXHAUST, à l'exception des marques, logos ou contenus appartenant à d'autres sociétés partenaires ou auteurs.
        </p>
        <p>
          Toute reproduction, distribution, modification, adaptation, retransmission ou publication, même partielle, de ces différents éléments est strictement interdite sans l'accord exprès par écrit de FULLPOWEREXHAUST. Cette représentation ou reproduction, par quelque procédé que ce soit, constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.
        </p>
      </LegalSection>

      <LegalSection title="5. Liens hypertextes">
        <p>
          Le site fullpowerexhaust.com peut contenir des liens vers d'autres sites. FULLPOWEREXHAUST n'a pas la possibilité de vérifier le contenu des sites ainsi visités, et n'assumera en conséquence aucune responsabilité de ce fait.
        </p>
      </LegalSection>

      <LegalSection title="6. Données personnelles">
        <p>
          La collecte et le traitement des données à caractère personnel des utilisateurs sont régis par notre Politique de confidentialité disponible sur ce site. Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification, de suppression et d'opposition aux données vous concernant. Pour exercer ces droits, contactez-nous à : contact@fullpowerexhaust.com.
        </p>
      </LegalSection>

      <LegalSection title="7. Droit applicable">
        <p>
          Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.
        </p>
      </LegalSection>
    </LegalLayout>
  );
}

// ─── Footer ───────────────────────────────────────────────────────────────────

function Footer({ setPage }: { setPage: (p: Page) => void }) {
  const legal = [
    { label: "CGV", value: "cgv" as Page },
    { label: "CGU", value: "cgu" as Page },
    { label: "Mentions légales", value: "mentions" as Page },
    { label: "Politique de confidentialité", value: "privacy" as Page },
  ];

  return (
    <footer className="border-t border-border bg-card mt-auto">
      <div className="max-w-[1320px] mx-auto px-5 py-10">
        <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
          <div>
            <span
              style={displayFont}
              className="text-xl font-bold tracking-widest text-foreground"
            >
              FULL<span className="text-primary">POWER</span>
              EXHAUST
            </span>
            <p className="text-xs text-muted-foreground mt-1 tracking-widest uppercase">
              L'échappement performance occasion.
            </p>
          </div>
          <div className="flex flex-col gap-1 text-xs text-muted-foreground">
            <span>contact@fullpowerexhaust.com</span>
            <span>fullpowerexhaust.com</span>
          </div>
        </div>

        <div className="border-t border-border pt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div className="flex flex-wrap gap-x-5 gap-y-2">
            {legal.map((l) => (
              <button
                key={l.value}
                onClick={() => {
                  setPage(l.value);
                  window.scrollTo(0, 0);
                }}
                className="text-xs text-muted-foreground hover:text-primary transition-colors duration-150 tracking-wide"
              >
                {l.label}
              </button>
            ))}
          </div>
          <div className="flex items-center gap-4">
            <p className="text-xs text-muted-foreground">© 2025 FULLPOWEREXHAUST</p>
            <button
              onClick={() => setPage("admin")}
              className="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest uppercase text-muted-foreground border border-border px-3 py-1.5 hover:border-primary hover:text-primary transition-colors duration-150"
            >
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              Back Office
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
}

// ─── PS Admin palette ────────────────────────────────────────────────────────

const PS = {
  sidebarBg: "#212432",
  sidebarHover: "#2d3049",
  sidebarText: "#9ea7b3",
  sidebarActiveText: "#ffffff",
  sidebarActiveBorder: "#25b9d7",
  topbarBg: "#ffffff",
  pageBg: "#f1f4f9",
  panelBg: "#ffffff",
  panelBorder: "#d6dae0",
  primary: "#25b9d7",
  primaryDark: "#1da7c3",
  textDark: "#373737",
  textMuted: "#6c757d",
  tableHead: "#f8f9fa",
  tableBorder: "#e2e5ea",
  rowHover: "#f5f9ff",
  green: "#70b580",
  orange: "#e08e4a",
  amber: "#dfb81c",
  blue: "#5d9cec",
  red: "#c05c67",
  gray: "#aab5c0",
} as const;

const PS_STATUS: Record<OrderStatus, { bg: string; color: string; border: string }> = {
  pending:    { bg: "#fff3cd", color: "#856404", border: "#ffc107" },
  processing: { bg: "#cce5ff", color: "#004085", border: "#004085" },
  shipped:    { bg: "#e2d9f3", color: "#432874", border: "#6f42c1" },
  delivered:  { bg: "#d4edda", color: "#155724", border: "#28a745" },
  cancelled:  { bg: "#f8d7da", color: "#721c24", border: "#dc3545" },
};

// ─── Admin Login ─────────────────────────────────────────────────────────────

function AdminLogin({ onLogin }: { onLogin: () => void }) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(false);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (email === "admin@fullpowerexhaust.com" && password === "admin123") {
      onLogin();
    } else {
      setError(true);
      setTimeout(() => setError(false), 2500);
    }
  }

  return (
    <div style={{ minHeight: "100vh", background: PS.pageBg, display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Outfit', sans-serif" }}>
      <div style={{ width: "100%", maxWidth: "420px", padding: "0 20px" }}>
        {/* Logo */}
        <div style={{ textAlign: "center", marginBottom: "28px" }}>
          <div style={{ display: "inline-flex", alignItems: "center", background: PS.sidebarBg, padding: "14px 32px", borderRadius: "3px" }}>
            <span style={{ ...displayFont, fontSize: "22px", fontWeight: 800, letterSpacing: "0.12em", color: "#fff" }}>
              POWER<span style={{ color: PS.primary }}>-</span>EXHAUST
            </span>
          </div>
          <p style={{ marginTop: "10px", fontSize: "12px", color: PS.textMuted, textTransform: "uppercase", letterSpacing: "0.1em" }}>
            Back Office
          </p>
        </div>

        {/* Card */}
        <div style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", overflow: "hidden", boxShadow: "0 2px 6px rgba(0,0,0,.08)" }}>
          <div style={{ background: PS.primary, padding: "14px 24px" }}>
            <p style={{ color: "#fff", fontSize: "14px", fontWeight: 600, margin: 0 }}>Connexion Administrateur</p>
          </div>
          <form onSubmit={handleSubmit} style={{ padding: "28px 24px" }}>
            {error && (
              <div style={{ display: "flex", alignItems: "center", gap: "8px", background: "#f8d7da", border: "1px solid #f5c6cb", color: "#721c24", padding: "10px 14px", borderRadius: "3px", marginBottom: "18px", fontSize: "13px" }}>
                <AlertCircle size={14} />
                Identifiants incorrects.
              </div>
            )}
            <div style={{ marginBottom: "18px" }}>
              <label style={{ display: "block", fontSize: "13px", fontWeight: 600, color: PS.textDark, marginBottom: "6px" }}>E-mail</label>
              <input
                type="email" required value={email}
                onChange={(e) => setEmail(e.target.value)}
                onFocus={(e) => (e.target.style.borderColor = PS.primary)}
                onBlur={(e) => (e.target.style.borderColor = PS.panelBorder)}
                style={{ width: "100%", padding: "9px 13px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "13px", color: PS.textDark, outline: "none", boxSizing: "border-box" as const }}
                placeholder="admin@fullpowerexhaust.com"
              />
            </div>
            <div style={{ marginBottom: "24px" }}>
              <label style={{ display: "block", fontSize: "13px", fontWeight: 600, color: PS.textDark, marginBottom: "6px" }}>Mot de passe</label>
              <input
                type="password" required value={password}
                onChange={(e) => setPassword(e.target.value)}
                onFocus={(e) => (e.target.style.borderColor = PS.primary)}
                onBlur={(e) => (e.target.style.borderColor = PS.panelBorder)}
                style={{ width: "100%", padding: "9px 13px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "13px", color: PS.textDark, outline: "none", boxSizing: "border-box" as const }}
                placeholder="••••••••"
              />
            </div>
            <button
              type="submit"
              style={{ width: "100%", padding: "11px", background: PS.primary, color: "#fff", border: "none", borderRadius: "3px", fontSize: "13px", fontWeight: 600, cursor: "pointer" }}
              onMouseOver={(e) => ((e.target as HTMLButtonElement).style.background = PS.primaryDark)}
              onMouseOut={(e) => ((e.target as HTMLButtonElement).style.background = PS.primary)}
            >
              Connexion →
            </button>
          </form>
        </div>
        <p style={{ textAlign: "center", fontSize: "11px", color: "#aaa", marginTop: "14px" }}>
          Démo&nbsp;: admin@fullpowerexhaust.com&nbsp;/&nbsp;admin123
        </p>
      </div>
    </div>
  );
}

// ─── Admin Dashboard ──────────────────────────────────────────────────────────

function AdminDashboard({ onLogout }: { onLogout: () => void }) {
  const [orders, setOrders] = useState<Order[]>(MOCK_ORDERS);
  const [section, setSection] = useState<AdminSection>("orders");
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [statusFilter, setStatusFilter] = useState<OrderStatus | "all">("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const perPage = 6;

  const pendingCount = orders.filter((o) => o.status === "pending").length;
  const totalRevenue = orders.filter((o) => o.status !== "cancelled").reduce((s, o) => s + o.amount, 0);
  const deliveredCount = orders.filter((o) => o.status === "delivered").length;
  const shippedCount = orders.filter((o) => o.status === "shipped").length;

  function updateStatus(id: string, status: OrderStatus) {
    setOrders((prev) => prev.map((o) => (o.id === id ? { ...o, status } : o)));
    setSelectedOrder((prev) => (prev?.id === id ? { ...prev, status } : prev));
  }

  const filtered = orders.filter((o) => {
    const matchStatus = statusFilter === "all" || o.status === statusFilter;
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || o.id.toLowerCase().includes(q) || o.client.toLowerCase().includes(q) || o.email.toLowerCase().includes(q) || o.product.toLowerCase().includes(q);
    return matchStatus && matchSearch;
  });

  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  const paginated = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);

  function toggleSelectAll() {
    if (selectedIds.size === paginated.length) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(paginated.map((o) => o.id)));
    }
  }
  function toggleSelect(id: string) {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });
  }

  const sidebarItems = [
    { label: "Tableau de bord", value: "orders" as AdminSection, icon: <LayoutDashboard size={16} />, badge: 0 },
    { label: "Commandes", value: "orders" as AdminSection, icon: <Package size={16} />, badge: pendingCount },
    { label: "Statistiques", value: "stats" as AdminSection, icon: <BarChart2 size={16} />, badge: 0 },
  ];

  return (
    <div style={{ minHeight: "100vh", background: PS.pageBg, fontFamily: "'Outfit', sans-serif", display: "flex", flexDirection: "column" }}>

      {/* Top bar */}
      <header style={{ position: "fixed", top: 0, left: 0, right: 0, zIndex: 100, background: PS.topbarBg, borderBottom: `1px solid ${PS.panelBorder}`, height: "54px", display: "flex", alignItems: "center", justifyContent: "space-between", padding: "0 24px 0 0", boxShadow: "0 1px 4px rgba(0,0,0,.08)" }}>
        {/* Left: sidebar toggle + breadcrumb */}
        <div style={{ display: "flex", alignItems: "center", gap: "0" }}>
          {/* Sidebar logo zone */}
          <div style={{ width: "220px", display: "flex", alignItems: "center", padding: "0 20px", borderRight: `1px solid ${PS.panelBorder}`, height: "54px" }}>
            <span style={{ ...displayFont, fontSize: "17px", fontWeight: 800, letterSpacing: "0.1em", color: PS.textDark }}>
              POWER<span style={{ color: PS.primary }}>-</span>EXHAUST
            </span>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: "6px", padding: "0 20px", fontSize: "13px", color: PS.textMuted }}>
            <span style={{ color: PS.primary, fontWeight: 600 }}>Back Office</span>
            <span>›</span>
            <span style={{ color: PS.textDark, fontWeight: 500, textTransform: "capitalize" }}>
              {section === "orders" ? "Commandes" : "Statistiques"}
            </span>
          </div>
        </div>

        {/* Right actions */}
        <div style={{ display: "flex", alignItems: "center", gap: "16px" }}>
          <button style={{ position: "relative", background: "none", border: "none", cursor: "pointer", color: PS.textMuted, padding: "4px" }}>
            <Bell size={18} />
            {pendingCount > 0 && (
              <span style={{ position: "absolute", top: "-2px", right: "-4px", background: PS.primary, color: "#fff", fontSize: "10px", width: "16px", height: "16px", borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700 }}>
                {pendingCount}
              </span>
            )}
          </button>
          <div style={{ display: "flex", alignItems: "center", gap: "8px", padding: "0 12px", borderLeft: `1px solid ${PS.panelBorder}`, height: "54px" }}>
            <div style={{ width: "28px", height: "28px", background: PS.primary, borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center" }}>
              <User size={14} color="#fff" />
            </div>
            <span style={{ fontSize: "13px", fontWeight: 600, color: PS.textDark }}>Administrateur</span>
          </div>
          <button
            onClick={onLogout}
            style={{ display: "flex", alignItems: "center", gap: "6px", background: "none", border: "none", cursor: "pointer", fontSize: "12px", fontWeight: 600, color: PS.red, padding: "4px 0" }}
          >
            <LogOut size={14} />
            Déconnexion
          </button>
        </div>
      </header>

      <div style={{ display: "flex", flex: 1, marginTop: "54px" }}>

        {/* Sidebar */}
        <aside style={{ width: "220px", background: PS.sidebarBg, minHeight: "calc(100vh - 54px)", position: "fixed", top: "54px", left: 0, bottom: 0, display: "flex", flexDirection: "column", overflowY: "auto", zIndex: 90 }}>
          <nav style={{ padding: "12px 0", flex: 1 }}>
            {sidebarItems.map((item, i) => {
              const isActive = section === item.value;
              return (
                <button
                  key={i}
                  onClick={() => setSection(item.value)}
                  style={{
                    width: "100%", display: "flex", alignItems: "center", justifyContent: "space-between",
                    padding: "11px 20px", background: isActive ? "rgba(37,185,215,.12)" : "transparent",
                    borderTop: "none", borderRight: "none", borderBottom: "none",
                    borderLeft: isActive ? `3px solid ${PS.sidebarActiveBorder}` : "3px solid transparent",
                    color: isActive ? PS.sidebarActiveText : PS.sidebarText,
                    cursor: "pointer", fontSize: "13px", fontWeight: isActive ? 600 : 400, textAlign: "left" as const, gap: "10px",
                  }}
                >
                  <span style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                    <span style={{ opacity: isActive ? 1 : 0.6 }}>{item.icon}</span>
                    {item.label}
                  </span>
                  {item.badge > 0 && (
                    <span style={{ background: PS.primary, color: "#fff", fontSize: "10px", fontWeight: 700, padding: "2px 6px", borderRadius: "10px", minWidth: "18px", textAlign: "center" }}>
                      {item.badge}
                    </span>
                  )}
                </button>
              );
            })}
          </nav>

          {/* Sidebar footer */}
          <div style={{ padding: "16px 20px", borderTop: "1px solid rgba(255,255,255,.07)" }}>
            <p style={{ fontSize: "11px", color: "rgba(255,255,255,.3)", marginBottom: "2px" }}>Version</p>
            <p style={{ fontSize: "12px", color: "rgba(255,255,255,.5)" }}>FULLPOWEREXHAUST BO v1.0</p>
          </div>
        </aside>

        {/* Main content */}
        <main style={{ marginLeft: "220px", flex: 1, padding: "24px", minHeight: "calc(100vh - 54px)" }}>

          {section === "orders" && (
            <div>
              {/* Page header */}
              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "20px" }}>
                <div>
                  <h1 style={{ ...displayFont, fontSize: "28px", fontWeight: 800, color: PS.textDark, margin: 0 }}>COMMANDES</h1>
                  <p style={{ fontSize: "12px", color: PS.textMuted, marginTop: "2px" }}>{orders.length} commandes au total</p>
                </div>
                <button style={{ display: "flex", alignItems: "center", gap: "7px", background: PS.primary, color: "#fff", border: "none", borderRadius: "3px", padding: "10px 18px", fontSize: "13px", fontWeight: 600, cursor: "pointer" }}>
                  <Download size={14} />
                  Exporter CSV
                </button>
              </div>

              {/* KPI row */}
              <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: "12px", marginBottom: "20px" }}>
                {[
                  { label: "Total commandes", value: orders.length, unit: "", icon: <Package size={20} />, color: PS.primary },
                  { label: "Chiffre d'affaires", value: `${totalRevenue}`, unit: "€", icon: <Euro size={20} />, color: PS.green },
                  { label: "En attente", value: pendingCount, unit: "", icon: <Clock size={20} />, color: PS.amber, alert: pendingCount > 0 },
                  { label: "Livrées", value: deliveredCount, unit: "", icon: <Truck size={20} />, color: PS.blue },
                ].map((kpi) => (
                  <div key={kpi.label} style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "16px 18px", display: "flex", alignItems: "center", gap: "14px", boxShadow: "0 1px 3px rgba(0,0,0,.05)" }}>
                    <div style={{ width: "42px", height: "42px", borderRadius: "50%", background: `${kpi.color}20`, display: "flex", alignItems: "center", justifyContent: "center", color: kpi.color, flexShrink: 0 }}>
                      {kpi.icon}
                    </div>
                    <div>
                      <p style={{ fontSize: "11px", color: PS.textMuted, textTransform: "uppercase", letterSpacing: "0.06em", marginBottom: "2px" }}>{kpi.label}</p>
                      <p style={{ ...displayFont, fontSize: "26px", fontWeight: 800, color: kpi.alert ? PS.amber : PS.textDark, lineHeight: 1 }}>
                        {kpi.value}{kpi.unit && <span style={{ fontSize: "15px", color: PS.textMuted, marginLeft: "3px" }}>{kpi.unit}</span>}
                      </p>
                    </div>
                  </div>
                ))}
              </div>

              {/* Orders panel */}
              <div style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", boxShadow: "0 1px 3px rgba(0,0,0,.05)" }}>
                {/* Panel header */}
                <div style={{ padding: "14px 20px", borderBottom: `1px solid ${PS.panelBorder}`, display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                  <h2 style={{ fontSize: "14px", fontWeight: 700, color: PS.textDark, margin: 0 }}>Liste des commandes</h2>
                  <button
                    onClick={() => { setSearchQuery(""); setStatusFilter("all"); setCurrentPage(1); }}
                    style={{ display: "flex", alignItems: "center", gap: "5px", background: "none", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "5px 12px", fontSize: "12px", color: PS.textMuted, cursor: "pointer" }}
                  >
                    <RefreshCw size={12} />
                    Réinitialiser
                  </button>
                </div>

                {/* Filter bar */}
                <div style={{ padding: "12px 20px", borderBottom: `1px solid ${PS.tableBorder}`, background: PS.tableHead, display: "flex", alignItems: "center", gap: "10px", flexWrap: "wrap" }}>
                  <div style={{ position: "relative", flex: 1, minWidth: "200px" }}>
                    <Search size={13} style={{ position: "absolute", left: "10px", top: "50%", transform: "translateY(-50%)", color: PS.textMuted }} />
                    <input
                      type="text"
                      placeholder="Rechercher par ID, client, produit…"
                      value={searchQuery}
                      onChange={(e) => { setSearchQuery(e.target.value); setCurrentPage(1); }}
                      style={{ width: "100%", padding: "7px 12px 7px 30px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "13px", color: PS.textDark, outline: "none", boxSizing: "border-box" as const }}
                    />
                  </div>
                  <select
                    value={statusFilter}
                    onChange={(e) => { setStatusFilter(e.target.value as OrderStatus | "all"); setCurrentPage(1); }}
                    style={{ padding: "7px 12px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "13px", color: PS.textDark, background: "#fff", outline: "none", cursor: "pointer" }}
                  >
                    <option value="all">Tous les statuts</option>
                    {(Object.keys(STATUS_LABELS) as OrderStatus[]).map((s) => (
                      <option key={s} value={s}>{STATUS_LABELS[s]}</option>
                    ))}
                  </select>
                  <span style={{ fontSize: "12px", color: PS.textMuted }}>{filtered.length} résultat{filtered.length > 1 ? "s" : ""}</span>
                </div>

                {/* Table */}
                <div style={{ overflowX: "auto" }}>
                  <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "13px" }}>
                    <thead>
                      <tr style={{ background: PS.tableHead, borderBottom: `2px solid ${PS.tableBorder}` }}>
                        <th style={{ padding: "10px 16px", width: "36px" }}>
                          <input
                            type="checkbox"
                            checked={selectedIds.size === paginated.length && paginated.length > 0}
                            onChange={toggleSelectAll}
                            style={{ accentColor: PS.primary, cursor: "pointer" }}
                          />
                        </th>
                        {["Réf.", "Client", "Produit", "Montant", "Date", "Statut", "Action"].map((col) => (
                          <th key={col} style={{ padding: "10px 14px", textAlign: "left", fontSize: "11px", fontWeight: 700, color: PS.textMuted, textTransform: "uppercase", letterSpacing: "0.06em", whiteSpace: "nowrap" }}>
                            {col}
                          </th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {paginated.map((order, i) => {
                        const st = PS_STATUS[order.status];
                        const isSelected = selectedIds.has(order.id);
                        return (
                          <tr
                            key={order.id}
                            style={{ background: isSelected ? "#eaf6fa" : i % 2 === 0 ? "#fff" : "#fafbfc", borderBottom: `1px solid ${PS.tableBorder}`, transition: "background .1s" }}
                            onMouseOver={(e) => { if (!isSelected) (e.currentTarget as HTMLTableRowElement).style.background = PS.rowHover; }}
                            onMouseOut={(e) => { if (!isSelected) (e.currentTarget as HTMLTableRowElement).style.background = i % 2 === 0 ? "#fff" : "#fafbfc"; }}
                          >
                            <td style={{ padding: "10px 16px" }}>
                              <input
                                type="checkbox"
                                checked={isSelected}
                                onChange={() => toggleSelect(order.id)}
                                style={{ accentColor: PS.primary, cursor: "pointer" }}
                              />
                            </td>
                            <td style={{ padding: "10px 14px", whiteSpace: "nowrap" }}>
                              <button
                                onClick={() => setSelectedOrder(order)}
                                style={{ background: "none", border: "none", color: PS.primary, fontWeight: 600, cursor: "pointer", fontSize: "13px", padding: 0, fontFamily: "monospace" }}
                              >
                                {order.id}
                              </button>
                            </td>
                            <td style={{ padding: "10px 14px" }}>
                              <p style={{ fontWeight: 600, color: PS.textDark, margin: 0, whiteSpace: "nowrap" }}>{order.client}</p>
                              <p style={{ fontSize: "12px", color: PS.textMuted, margin: 0 }}>{order.email}</p>
                            </td>
                            <td style={{ padding: "10px 14px", maxWidth: "180px" }}>
                              <p style={{ color: PS.textDark, margin: 0, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{order.product}</p>
                              <p style={{ fontSize: "11px", color: PS.textMuted, margin: 0 }}>{order.brand}</p>
                            </td>
                            <td style={{ padding: "10px 14px", whiteSpace: "nowrap" }}>
                              <span style={{ ...displayFont, fontSize: "17px", fontWeight: 700, color: PS.textDark }}>{order.amount}</span>
                              <span style={{ fontSize: "12px", color: PS.textMuted }}> €</span>
                            </td>
                            <td style={{ padding: "10px 14px", fontSize: "12px", color: PS.textMuted, whiteSpace: "nowrap" }}>{order.date}</td>
                            <td style={{ padding: "10px 14px" }}>
                              <span style={{ display: "inline-block", padding: "3px 8px", borderRadius: "3px", fontSize: "11px", fontWeight: 700, background: st.bg, color: st.color, border: `1px solid ${st.border}20`, whiteSpace: "nowrap" }}>
                                {STATUS_LABELS[order.status]}
                              </span>
                            </td>
                            <td style={{ padding: "10px 14px" }}>
                              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                                <button
                                  onClick={() => setSelectedOrder(order)}
                                  style={{ display: "flex", alignItems: "center", gap: "4px", background: PS.primary, color: "#fff", border: "none", borderRadius: "3px", padding: "5px 10px", fontSize: "12px", fontWeight: 600, cursor: "pointer" }}
                                  title="Voir"
                                >
                                  <Eye size={12} />
                                  Voir
                                </button>
                                <select
                                  value={order.status}
                                  onChange={(e) => updateStatus(order.id, e.target.value as OrderStatus)}
                                  style={{ padding: "5px 8px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "11px", color: PS.textDark, background: "#fff", outline: "none", cursor: "pointer" }}
                                >
                                  {(Object.keys(STATUS_LABELS) as OrderStatus[]).map((s) => (
                                    <option key={s} value={s}>{STATUS_LABELS[s]}</option>
                                  ))}
                                </select>
                              </div>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>

                  {filtered.length === 0 && (
                    <div style={{ padding: "40px", textAlign: "center", color: PS.textMuted, fontSize: "13px" }}>
                      Aucune commande trouvée.
                    </div>
                  )}
                </div>

                {/* Pagination */}
                <div style={{ padding: "12px 20px", borderTop: `1px solid ${PS.tableBorder}`, display: "flex", alignItems: "center", justifyContent: "space-between", background: PS.tableHead }}>
                  <p style={{ fontSize: "12px", color: PS.textMuted, margin: 0 }}>
                    {filtered.length === 0 ? "0" : `${(currentPage - 1) * perPage + 1}–${Math.min(currentPage * perPage, filtered.length)}`} sur {filtered.length} commande{filtered.length > 1 ? "s" : ""}
                  </p>
                  <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                    <button
                      onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                      disabled={currentPage === 1}
                      style={{ display: "flex", alignItems: "center", padding: "5px 10px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", background: currentPage === 1 ? PS.tableHead : "#fff", color: currentPage === 1 ? PS.gray : PS.textDark, cursor: currentPage === 1 ? "default" : "pointer", fontSize: "12px" }}
                    >
                      <ChevronLeft size={14} />
                    </button>
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                      <button
                        key={p}
                        onClick={() => setCurrentPage(p)}
                        style={{ width: "30px", height: "30px", display: "flex", alignItems: "center", justifyContent: "center", border: `1px solid ${p === currentPage ? PS.primary : PS.panelBorder}`, borderRadius: "3px", background: p === currentPage ? PS.primary : "#fff", color: p === currentPage ? "#fff" : PS.textDark, cursor: "pointer", fontSize: "12px", fontWeight: p === currentPage ? 700 : 400 }}
                      >
                        {p}
                      </button>
                    ))}
                    <button
                      onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                      disabled={currentPage === totalPages}
                      style={{ display: "flex", alignItems: "center", padding: "5px 10px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", background: currentPage === totalPages ? PS.tableHead : "#fff", color: currentPage === totalPages ? PS.gray : PS.textDark, cursor: currentPage === totalPages ? "default" : "pointer", fontSize: "12px" }}
                    >
                      <ChevronRight size={14} />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          {section === "stats" && <AdminStats orders={orders} totalRevenue={totalRevenue} shippedCount={shippedCount} deliveredCount={deliveredCount} />}
        </main>
      </div>

      {/* Order detail panel */}
      {selectedOrder && (
        <div style={{ position: "fixed", inset: 0, zIndex: 200, display: "flex", justifyContent: "flex-end" }}>
          <div style={{ flex: 1, background: "rgba(0,0,0,.35)" }} onClick={() => setSelectedOrder(null)} />
          <div style={{ width: "100%", maxWidth: "480px", background: PS.panelBg, display: "flex", flexDirection: "column", overflowY: "auto", boxShadow: "-4px 0 24px rgba(0,0,0,.15)" }}>
            {/* Panel header */}
            <div style={{ padding: "16px 24px", borderBottom: `1px solid ${PS.panelBorder}`, display: "flex", alignItems: "center", justifyContent: "space-between", background: PS.primary }}>
              <div>
                <p style={{ fontSize: "11px", color: "rgba(255,255,255,.7)", textTransform: "uppercase", letterSpacing: "0.08em", margin: 0 }}>Détail commande</p>
                <p style={{ ...displayFont, fontSize: "20px", fontWeight: 800, color: "#fff", margin: 0 }}>{selectedOrder.id}</p>
              </div>
              <button onClick={() => setSelectedOrder(null)} style={{ background: "rgba(255,255,255,.2)", border: "none", borderRadius: "50%", width: "30px", height: "30px", display: "flex", alignItems: "center", justifyContent: "center", cursor: "pointer", color: "#fff" }}>
                <X size={16} />
              </button>
            </div>

            <div style={{ padding: "24px", display: "flex", flexDirection: "column", gap: "20px" }}>
              {/* Status */}
              <div>
                <p style={{ fontSize: "11px", fontWeight: 700, textTransform: "uppercase", letterSpacing: "0.08em", color: PS.textMuted, marginBottom: "8px" }}>Statut de la commande</p>
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                  <span style={{ padding: "4px 12px", borderRadius: "3px", fontSize: "12px", fontWeight: 700, background: PS_STATUS[selectedOrder.status].bg, color: PS_STATUS[selectedOrder.status].color }}>
                    {STATUS_LABELS[selectedOrder.status]}
                  </span>
                  <select
                    value={selectedOrder.status}
                    onChange={(e) => updateStatus(selectedOrder.id, e.target.value as OrderStatus)}
                    style={{ padding: "6px 10px", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "12px", color: PS.textDark, outline: "none", cursor: "pointer" }}
                  >
                    {(Object.keys(STATUS_LABELS) as OrderStatus[]).map((s) => (
                      <option key={s} value={s}>{STATUS_LABELS[s]}</option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Client block */}
              <div>
                <p style={{ fontSize: "11px", fontWeight: 700, textTransform: "uppercase", letterSpacing: "0.08em", color: PS.textMuted, marginBottom: "8px" }}>Informations client</p>
                <div style={{ background: PS.tableHead, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "14px 16px" }}>
                  <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "10px", fontSize: "13px" }}>
                    {[
                      { label: "Nom", value: selectedOrder.client },
                      { label: "E-mail", value: selectedOrder.email },
                      { label: "Téléphone", value: selectedOrder.phone },
                      { label: "Date", value: selectedOrder.date },
                    ].map((row) => (
                      <div key={row.label}>
                        <p style={{ fontSize: "11px", color: PS.textMuted, margin: "0 0 2px" }}>{row.label}</p>
                        <p style={{ fontWeight: 600, color: PS.textDark, margin: 0 }}>{row.value}</p>
                      </div>
                    ))}
                  </div>
                  <div style={{ marginTop: "10px", paddingTop: "10px", borderTop: `1px solid ${PS.panelBorder}` }}>
                    <p style={{ fontSize: "11px", color: PS.textMuted, margin: "0 0 2px" }}>Adresse de livraison</p>
                    <p style={{ fontWeight: 600, color: PS.textDark, margin: 0, fontSize: "13px" }}>{selectedOrder.address}</p>
                  </div>
                </div>
              </div>

              {/* Product block */}
              <div>
                <p style={{ fontSize: "11px", fontWeight: 700, textTransform: "uppercase", letterSpacing: "0.08em", color: PS.textMuted, marginBottom: "8px" }}>Produit commandé</p>
                <div style={{ background: PS.tableHead, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "14px 16px" }}>
                  <p style={{ fontWeight: 600, color: PS.textDark, margin: "0 0 4px", fontSize: "13px" }}>{selectedOrder.product}</p>
                  <div style={{ display: "flex", gap: "16px", fontSize: "12px", color: PS.textMuted }}>
                    <span>Marque : <strong style={{ color: PS.textDark }}>{selectedOrder.brand}</strong></span>
                    <span>État : <strong style={{ color: PS.textDark }}>Occasion</strong></span>
                    <span>Qté : <strong style={{ color: PS.textDark }}>1</strong></span>
                  </div>
                </div>
              </div>

              {/* Amount */}
              <div style={{ background: PS.tableHead, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "14px 16px", display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                <div>
                  <p style={{ fontSize: "11px", color: PS.textMuted, margin: "0 0 2px" }}>Total commande TTC</p>
                  <p style={{ fontSize: "12px", color: PS.textMuted, margin: 0 }}>Livraison incluse</p>
                </div>
                <span style={{ ...displayFont, fontSize: "36px", fontWeight: 800, color: PS.textDark }}>{selectedOrder.amount} <span style={{ fontSize: "20px", color: PS.textMuted }}>€</span></span>
              </div>

              {/* Actions */}
              <div style={{ display: "flex", gap: "10px", marginTop: "4px" }}>
                <button style={{ flex: 1, padding: "10px", background: "none", border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", fontSize: "12px", fontWeight: 600, color: PS.textMuted, cursor: "pointer" }}>
                  Imprimer la commande
                </button>
                <button style={{ flex: 1, padding: "10px", background: PS.primary, border: "none", borderRadius: "3px", fontSize: "12px", fontWeight: 600, color: "#fff", cursor: "pointer" }}>
                  Envoyer e-mail client
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Admin Stats ─────────────────────────────────────────────────────────────

function AdminStats({ orders, totalRevenue, shippedCount, deliveredCount }: { orders: Order[]; totalRevenue: number; shippedCount: number; deliveredCount: number }) {
  const byBrand: Record<string, { count: number; revenue: number }> = {};
  orders.filter((o) => o.status !== "cancelled").forEach((o) => {
    if (!byBrand[o.brand]) byBrand[o.brand] = { count: 0, revenue: 0 };
    byBrand[o.brand].count++;
    byBrand[o.brand].revenue += o.amount;
  });

  const maxRevenue = Math.max(...Object.values(byBrand).map((b) => b.revenue), 1);

  const byStatus = (Object.keys(STATUS_LABELS) as OrderStatus[]).map((s) => ({
    s, count: orders.filter((o) => o.status === s).length,
  }));

  return (
    <div>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "20px" }}>
        <div>
          <h1 style={{ ...displayFont, fontSize: "28px", fontWeight: 800, color: PS.textDark, margin: 0 }}>STATISTIQUES</h1>
          <p style={{ fontSize: "12px", color: PS.textMuted, marginTop: "2px" }}>Vue d'ensemble de l'activité</p>
        </div>
      </div>

      {/* KPI */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: "12px", marginBottom: "20px" }}>
        {[
          { label: "Commandes totales", value: orders.length, icon: <Package size={20} />, color: PS.primary },
          { label: "Chiffre d'affaires", value: `${totalRevenue} €`, icon: <TrendingUp size={20} />, color: PS.green },
          { label: "Taux de livraison", value: `${Math.round((deliveredCount / orders.length) * 100)} %`, icon: <Truck size={20} />, color: PS.blue },
          { label: "En transit", value: shippedCount, icon: <Truck size={20} />, color: PS.orange },
        ].map((k) => (
          <div key={k.label} style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", padding: "16px 18px", display: "flex", alignItems: "center", gap: "14px", boxShadow: "0 1px 3px rgba(0,0,0,.05)" }}>
            <div style={{ width: "42px", height: "42px", borderRadius: "50%", background: `${k.color}20`, display: "flex", alignItems: "center", justifyContent: "center", color: k.color, flexShrink: 0 }}>{k.icon}</div>
            <div>
              <p style={{ fontSize: "11px", color: PS.textMuted, textTransform: "uppercase", letterSpacing: "0.06em", marginBottom: "2px" }}>{k.label}</p>
              <p style={{ ...displayFont, fontSize: "24px", fontWeight: 800, color: PS.textDark, lineHeight: 1, margin: 0 }}>{k.value}</p>
            </div>
          </div>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "16px" }}>
        {/* Status breakdown */}
        <div style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", overflow: "hidden", boxShadow: "0 1px 3px rgba(0,0,0,.05)" }}>
          <div style={{ padding: "12px 18px", borderBottom: `1px solid ${PS.panelBorder}`, background: PS.tableHead }}>
            <p style={{ fontSize: "13px", fontWeight: 700, color: PS.textDark, margin: 0 }}>Répartition par statut</p>
          </div>
          <div style={{ padding: "16px 18px", display: "flex", flexDirection: "column", gap: "14px" }}>
            {byStatus.map(({ s, count }) => {
              const st = PS_STATUS[s];
              return (
                <div key={s}>
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "5px" }}>
                    <span style={{ display: "inline-block", padding: "2px 8px", borderRadius: "3px", fontSize: "11px", fontWeight: 700, background: st.bg, color: st.color }}>{STATUS_LABELS[s]}</span>
                    <span style={{ fontSize: "13px", fontWeight: 700, color: PS.textDark }}>{count}</span>
                  </div>
                  <div style={{ height: "6px", background: PS.tableBorder, borderRadius: "3px", overflow: "hidden" }}>
                    <div style={{ height: "100%", width: `${(count / orders.length) * 100}%`, background: st.border, borderRadius: "3px", transition: "width .4s ease" }} />
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* By brand */}
        <div style={{ background: PS.panelBg, border: `1px solid ${PS.panelBorder}`, borderRadius: "3px", overflow: "hidden", boxShadow: "0 1px 3px rgba(0,0,0,.05)" }}>
          <div style={{ padding: "12px 18px", borderBottom: `1px solid ${PS.panelBorder}`, background: PS.tableHead }}>
            <p style={{ fontSize: "13px", fontWeight: 700, color: PS.textDark, margin: 0 }}>Ventes par marque</p>
          </div>
          <div style={{ padding: "16px 18px", display: "flex", flexDirection: "column", gap: "14px" }}>
            {Object.entries(byBrand).sort((a, b) => b[1].revenue - a[1].revenue).map(([brand, data]) => (
              <div key={brand}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "5px" }}>
                  <span style={{ fontSize: "13px", fontWeight: 600, color: PS.textDark }}>{brand}</span>
                  <div style={{ display: "flex", alignItems: "center", gap: "12px" }}>
                    <span style={{ fontSize: "11px", color: PS.textMuted }}>{data.count} cmd</span>
                    <span style={{ ...displayFont, fontSize: "15px", fontWeight: 700, color: PS.textDark }}>{data.revenue} €</span>
                  </div>
                </div>
                <div style={{ height: "6px", background: PS.tableBorder, borderRadius: "3px", overflow: "hidden" }}>
                  <div style={{ height: "100%", width: `${(data.revenue / maxRevenue) * 100}%`, background: PS.primary, borderRadius: "3px", transition: "width .4s ease" }} />
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Cookie Banner ────────────────────────────────────────────────────────────

function CookieBanner({
  onAccept,
  onRefuse,
  onCustomize,
}: {
  onAccept: () => void;
  onRefuse: () => void;
  onCustomize: () => void;
}) {
  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 bg-card border-t border-border shadow-2xl">
      <div className="max-w-[1320px] mx-auto px-5 py-5 flex flex-col md:flex-row items-start md:items-center gap-5 justify-between">
        <div className="flex-1 min-w-0">
          <p className="text-xs font-semibold tracking-widest uppercase text-foreground mb-1">
            Gestion des cookies
          </p>
          <p className="text-xs text-muted-foreground leading-relaxed">
            Nous utilisons des cookies strictement nécessaires au fonctionnement du site et, sous réserve de votre consentement, des cookies analytiques pour mesurer notre audience. Vous pouvez accepter, refuser ou personnaliser vos choix à tout moment.{" "}
            <button onClick={onCustomize} className="text-primary underline hover:text-red-400 transition-colors">
              En savoir plus
            </button>
          </p>
        </div>
        <div className="flex flex-wrap gap-2 shrink-0">
          <button
            onClick={onRefuse}
            className="px-5 py-2.5 text-xs font-semibold tracking-widest uppercase border border-border text-muted-foreground hover:border-white/30 hover:text-foreground transition-colors duration-150 rounded"
          >
            Tout refuser
          </button>
          <button
            onClick={onCustomize}
            className="px-5 py-2.5 text-xs font-semibold tracking-widest uppercase border border-border text-muted-foreground hover:border-primary hover:text-primary transition-colors duration-150 rounded"
          >
            Personnaliser
          </button>
          <button
            onClick={onAccept}
            className="px-5 py-2.5 text-xs font-semibold tracking-widest uppercase bg-primary text-white hover:bg-red-700 transition-colors duration-150 rounded"
          >
            Tout accepter
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── App ──────────────────────────────────────────────────────────────────────

export default function App() {
  const [page, setPage] = useState<Page>("home");
  const [cookieConsent, setCookieConsent] = useState<boolean | null>(null);
  const [adminLoggedIn, setAdminLoggedIn] = useState(false);

  function navigateTo(p: Page) {
    setPage(p);
    window.scrollTo(0, 0);
  }

  // Admin section renders its own full-page layout
  if (page === "admin") {
    if (!adminLoggedIn) {
      return <AdminLogin onLogin={() => setAdminLoggedIn(true)} />;
    }
    return (
      <AdminDashboard
        onLogout={() => {
          setAdminLoggedIn(false);
          navigateTo("home");
        }}
      />
    );
  }

  return (
    <div className="min-h-screen flex flex-col bg-background text-foreground">
      <Header page={page} setPage={navigateTo} cartCount={0} />

      <div className="flex-1">
        {page === "home" && <HomePage setPage={navigateTo} />}
        {page === "catalog" && <CatalogPage />}
        {page === "auth" && <AuthPage />}
        {page === "cgv" && <CGVPage />}
        {page === "cgu" && <CGUPage />}
        {page === "privacy" && <PrivacyPage />}
        {page === "cookies" && <CookiesPage />}
        {page === "refund" && <RefundPage />}
        {page === "returns" && <ReturnsPage />}
        {page === "contact" && <ContactPage />}
        {page === "mentions" && <MentionsLegalesPage />}
      </div>

      <Footer setPage={navigateTo} />

      {cookieConsent === null && (
        <CookieBanner
          onAccept={() => setCookieConsent(true)}
          onRefuse={() => setCookieConsent(false)}
          onCustomize={() => {
            navigateTo("cookies");
            setCookieConsent(false);
          }}
        />
      )}
    </div>
  );
}