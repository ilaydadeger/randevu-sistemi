---
name: Lumière Minimalist
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1b1c1c'
  on-surface-variant: '#504444'
  inverse-surface: '#303030'
  inverse-on-surface: '#f3f0ef'
  outline: '#827473'
  outline-variant: '#d4c2c2'
  surface-tint: '#7a5555'
  primary: '#7a5555'
  on-primary: '#ffffff'
  primary-container: '#c89b9b'
  on-primary-container: '#533333'
  inverse-primary: '#ebbbbb'
  secondary: '#645d55'
  on-secondary: '#ffffff'
  secondary-container: '#ebe1d6'
  on-secondary-container: '#6a635b'
  tertiary: '#635d5a'
  on-tertiary: '#ffffff'
  tertiary-container: '#aca5a0'
  on-tertiary-container: '#403b38'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad9'
  primary-fixed-dim: '#ebbbbb'
  on-primary-fixed: '#2e1415'
  on-primary-fixed-variant: '#603e3e'
  secondary-fixed: '#ebe1d6'
  secondary-fixed-dim: '#cec5bb'
  on-secondary-fixed: '#1f1b14'
  on-secondary-fixed-variant: '#4c463e'
  tertiary-fixed: '#eae1dc'
  tertiary-fixed-dim: '#cdc5c0'
  on-tertiary-fixed: '#1f1b18'
  on-tertiary-fixed-variant: '#4b4642'
  background: '#fcf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e5e2e1'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.1em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 64px
---

## Brand & Style

The design system is anchored in a "Modern Gallery" aesthetic, designed to mirror the luxury and precision of high-end nail artistry. It targets a discerning, style-conscious demographic that values editorial clarity over cluttered functionality.

The visual direction combines **Minimalism** with **Glassmorphism** to create a "New Generation" feel. By prioritizing expansive whitespace and a reduced visual noise, the UI allows high-resolution imagery of nail designs to take center stage. The emotional response is one of calm, professional luxury—evoking the atmosphere of a premium boutique salon where every detail is intentional.

## Colors

The palette is a sophisticated curation of skin-toned nudes and muted florals, grounded by a deep charcoal for functional clarity.

- **Primary (Muted Rose):** Used for key actions, selection states, and brand highlights. It feels organic and soft.
- **Secondary (Sophisticated Nude):** The core surface color for cards and container backgrounds, providing a warm, skin-like backdrop.
- **Tertiary (Pearl Bone):** Used for subtle differentiations in layout, like navigation bars or secondary containers.
- **Neutral (Deep Charcoal):** Reserved for high-contrast typography and primary iconography to ensure accessibility against soft backgrounds.

The default mode is **Light**, utilizing high-key lighting to maintain a fresh, airy appearance.

## Typography

This design system employs a high-contrast typographic pairing to signal luxury. 

**Playfair Display** provides an authoritative, editorial voice for headings and service titles. It should be used with generous leading (line-height) to maintain a relaxed, premium feel.

**Inter** serves as the functional workhorse for all UI elements, pricing data, and body copy. To maintain the minimalist aesthetic, use the `label-caps` style for section headers and small metadata, which adds a structured, "catalog" feel to the interface.

## Layout & Spacing

The layout philosophy follows a **Fixed Grid** for desktop (max-width 1440px) to ensure the editorial compositions remain stable and visually balanced. 

- **Desktop:** A 12-column grid with 64px side margins. Large "negative space" blocks (using `xl` spacing) should be used to separate different service categories or booking steps.
- **Mobile:** A 4-column fluid grid. Side margins are reduced to 16px, but vertical spacing remains generous to ensure a "breathable" feel even on small screens.

Spacing follows an 8px rhythmic scale, but emphasizes larger increments (`lg` and `xl`) to avoid a cramped "utility" look.

## Elevation & Depth

Hierarchy is established through **Ambient Shadows** and **Tonal Layering** rather than traditional borders.

- **Low Elevation:** Surfaces use a 1px solid border in a color only slightly darker than the surface itself (e.g., a 5% darker nude) for a "soft-carved" look.
- **High Elevation:** Used for active booking modals and floating action buttons. These use an extra-diffused shadow with a slight Primary (Rose) tint: `0px 20px 40px rgba(200, 155, 155, 0.15)`.
- **Glass Effects:** On scroll, navigation bars should use a 20px backdrop blur with a 70% transparent white tint to maintain depth while keeping the focus on underlying imagery.

## Shapes

The shape language is defined by significant roundedness to evoke softness and organic beauty. 

Standard components use `rounded-md` (0.5rem), while primary containers, image carousels, and main buttons use `rounded-xl` (1.5rem) or `rounded-2xl` (2rem). This high degree of curvature creates a friendly, "new generation" aesthetic that feels approachable and modern.

## Components

### Buttons
Primary buttons are pill-shaped and use a subtle gradient from the Primary color to a slightly lighter Rose. Secondary buttons should be transparent with a thin Deep Charcoal border or a soft Nude background.

### Input Fields
Inputs avoid heavy boxes. Use a "Soft Inset" style: a subtle secondary color background with a 2px bottom border that animates to Primary color on focus. Labels should always use the `label-caps` typography style.

### Cards (Service & Portfolio)
Cards should have no visible borders. They rely on the `secondary_color` background and soft ambient shadows. Images within cards must use a 1:1 or 4:5 aspect ratio with 1.5rem rounded corners to maintain the gallery feel.

### Appointment Chips
For time-slot selection, use chips with a Secondary background. The "Selected" state should transition to the Deep Charcoal background with white text, creating a bold, unmistakable contrast.

### Pricing Lists
Pricing should be displayed with an editorial flair: Service name in `body-lg` (Inter), price in `headline-sm` (Playfair Display), and a subtle dotted leader line connecting them to guide the eye.