/**
 * assets/js/products-data.js
 * Central product catalog used by search, quick-view, compare, cart, wishlist
 * and recently-viewed. Tries the live PHP/MySQL API first (GET /api/products.php)
 * and falls back to this embedded catalog when no PHP server is running --
 * e.g. when the site is opened directly as a static file -- so every feature
 * still works out of the box. The embedded records mirror database/schema.sql.
 */
import { apiGet } from './utils.js';

export const FALLBACK_PRODUCTS = [
  { id: 1, name: 'Phantom Vision Class', slug: 'phantom-vision-class', tag: 'Entry Recon', category: 'Drones', price: 649.00, image: 'images/drone_1.png', short_desc: "The workhorse for first-time operators — stabilized gimbal, live downlink, and a flight envelope that forgives.", specs: { 'Flight Time': '25 min', 'Range': '4 km', 'Camera': '12MP / 4K' } },
  { id: 2, name: 'Phantom 4 Multispectral', slug: 'phantom-4-multispectral', tag: 'Multispectral', category: 'Drones', price: 5999.00, image: 'images/drone_2.png', short_desc: 'A six-sensor imaging array built for crop health, NDVI mapping, and environmental survey work.', specs: { 'Flight Time': '27 min per battery', 'Positioning': 'RTK, sub-5cm', 'Sensors': 'RGB + Green/Red/Red Edge/NIR' } },
  { id: 3, name: 'Phantom 4 Pro', slug: 'phantom-4-pro', tag: 'Pro Series', category: 'Drones', price: 1799.00, image: 'images/drone_6.png', short_desc: 'Our best-selling airframe. A 1-inch sensor, obstacle sensing on four sides, and 30 minutes of hold time.', specs: { 'Flight Time': '30 min', 'Sensor': '1-inch CMOS', 'Obstacle Sensing': '4-directional' } },
  { id: 4, name: 'Phantom 4 RTK', slug: 'phantom-4-rtk', tag: 'RTK / Survey', category: 'Drones', price: 8499.00, image: 'images/drone_3.png', short_desc: "Centimetre-level positioning for mapping crews who can't afford drift between flight lines.", specs: { 'Flight Time': '30 min', 'Positioning': 'RTK, sub-5cm', 'Use Case': 'Mapping / Survey' } },
  { id: 5, name: 'Matrice Industrial Series', slug: 'matrice-industrial-series', tag: 'Enterprise', category: 'Drones', price: 14999.00, image: 'images/drone_9.jpg', short_desc: 'Heavy-lift industrial airframe for inspection and mapping crews operating beyond visual line of sight.', specs: { 'Flight Time': '45 min', 'Payloads': 'Modular / interchangeable', 'Build': 'Carbon fiber arms' } },
  { id: 6, name: 'AgriSpray Field Platform', slug: 'agrispray-field-platform', tag: 'Agriculture', category: 'Drones', price: 12499.00, image: 'images/drone_11.jpg', short_desc: 'Agricultural spraying and mapping octocopter for large-acreage operations.', specs: { 'Tank Capacity': '16 L', 'Coverage': '8 ha/hr' } },
  { id: 7, name: 'Aero X Advanced', slug: 'aero-x-advanced', tag: 'Pro Series', category: 'Drones', price: 2399.00, image: 'images/drone_7.jpg', short_desc: 'Compact folding airframe with dual-lens gimbal and extended transmission range.', specs: { 'Flight Time': '34 min', 'Range': '15 km', 'Camera': 'Dual-lens' } },
  { id: 8, name: 'Compact Fold Duo', slug: 'compact-fold-duo', tag: 'Entry Recon', category: 'Drones', price: 219.00, image: 'images/drone_10.jpg', short_desc: 'Budget-friendly folding quadcopter, sold as a two-unit training pack.', specs: { 'Flight Time': '13 min', 'Camera': '1080p' } },
  { id: 16, name: 'Aero Duo Field Kit', slug: 'aero-duo-field-kit', tag: 'Fleet Bundle', category: 'Drones', price: 4299.00, image: 'images/drone_8.jpg', short_desc: 'Two matched Aero-class folding airframes in one case — built for crews running parallel missions or hot-swap redundancy in the field.', specs: { 'Units Included': '2', 'Flight Time': '34 min each', 'Case': 'Hard-shell dual carry' } },
  { id: 17, name: 'Aero X Night Ops', slug: 'aero-x-night-ops', tag: 'Pro Series', category: 'Drones', price: 2599.00, image: 'images/drone_12.jpg', short_desc: 'The Aero X platform with illuminated arm markers for low-light and dusk operations, plus the same dual-lens gimbal.', specs: { 'Flight Time': '34 min', 'Arm Lighting': 'Multi-color status LEDs', 'Camera': 'Dual-lens' } },
  { id: 9, name: 'Intelligent Flight Battery', slug: 'intelligent-flight-battery', tag: 'Standard Cell', category: 'Batteries', price: 179.00, image: 'images/drone_4.png', short_desc: 'LED charge readout, auto-discharge past 10 days idle, and a 15-minute rapid top-up on the fast charger.', specs: { 'Capacity': '5870mAh', 'Voltage': '15.2V' } },
  { id: 10, name: 'High-Capacity Flight Battery', slug: 'high-capacity-flight-battery', tag: 'Extended Cell', category: 'Batteries', price: 229.00, image: 'images/drone_5.png', short_desc: 'The extended pack for survey missions — more line coverage per sortie, same charge-cycle lifespan.', specs: { 'Capacity': '5870mAh Extended', 'Voltage': '15.2V' } },
  { id: 11, name: 'Xingeto Industrial 6S Pack', slug: 'xingeto-industrial-6s-pack', tag: 'Industrial 6S', category: 'Batteries', price: 389.00, image: 'images/battery_xingeto_set.jpg', short_desc: 'High-discharge 6S industrial pack family, from 16,000 to 30,000mAh, for heavy-lift industrial airframes.', specs: { 'Capacity': '16000-30000mAh', 'Voltage': '22.2V', 'Wh': '270Wh' } },
  { id: 12, name: 'LiPower Solid-State 80000', slug: 'lipower-solid-state-80000', tag: 'Solid-State', category: 'Batteries', price: 1249.00, image: 'images/battery_lipower_80000.webp', short_desc: 'Next-generation solid-state cell pack rated at 1776Wh for maximum-endurance heavy-lift missions.', specs: { 'Capacity': '80000mAh', 'Voltage': '22.2V', 'Wh': '1776Wh' } },
  { id: 13, name: 'Herewin 22000 Dual Pack', slug: 'herewin-22000-dual-pack', tag: 'Smart Pack', category: 'Batteries', price: 459.00, image: 'images/battery_herewin_22000.jpg', short_desc: 'Matched dual-battery smart pack with balance leads, sold as a pair for redundant field operation.', specs: { 'Capacity': '22000mAh', 'Voltage': '48V' } },
  { id: 14, name: 'Heltec Energy 5200 LiPo', slug: 'heltec-energy-5200-lipo', tag: 'Racing / Compact', category: 'Batteries', price: 89.00, image: 'images/battery_heltec_5200.jpg', short_desc: 'Lightweight 6S 5200mAh competition-grade pack for compact and racing airframes.', specs: { 'Capacity': '5200mAh', 'Voltage': '22.2V', 'Discharge': '100C' } },
  { id: 15, name: 'Oro Compact Smart Battery', slug: 'oro-compact-smart-battery', tag: 'Compact Smart', category: 'Batteries', price: 69.00, image: 'images/battery_oro.jpg', short_desc: 'Compact self-heating smart battery with USB-C charge port, built for consumer-class folding drones.', specs: { 'Capacity': '1600mAh', 'Voltage': '7.4V', 'Wh': '11.84Wh' } },
];

let cache = null;

/** Fetch the catalog from the PHP/MySQL API; fall back to the embedded list. */
export async function loadProducts() {
  if (cache) return cache;
  const { ok, data } = await apiGet('api/products.php?per_page=48');
  if (ok && data && Array.isArray(data.items) && data.items.length) {
    cache = data.items.map(normalizeApiProduct);
  } else {
    cache = FALLBACK_PRODUCTS;
  }
  return cache;
}

function normalizeApiProduct(row) {
  return {
    id: row.id,
    name: row.name,
    slug: row.slug,
    tag: row.tag,
    category: row.category_name || row.category_type || '',
    price: Number(row.price),
    image: row.primary_image,
    short_desc: row.short_desc,
    specs: row.specs_json ? JSON.parse(row.specs_json) : {},
  };
}

export function getById(id) {
  return (cache || FALLBACK_PRODUCTS).find((p) => String(p.id) === String(id));
}
