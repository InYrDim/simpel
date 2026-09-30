---
name: Skibidi Academic Management
description: Warm, dense, role-based workspace for university academic operations (light and dark).
colors:
    background: 'oklch(0.994 0 0)'
    foreground: 'oklch(0 0 0)'
    card: 'oklch(0.994 0 0)'
    popover: 'oklch(0.9911 0 0)'
    primary: 'oklch(0.55 0.158 243.9173)'
    primary-foreground: 'oklch(1 0 0)'
    secondary: 'oklch(0.954 0.0063 255.4755)'
    secondary-foreground: 'oklch(0.1344 0 0)'
    muted: 'oklch(0.9702 0 0)'
    muted-foreground: 'oklch(0.4386 0 0)'
    accent: 'oklch(0.9782 0.0167 215.4555)'
    accent-foreground: 'oklch(0.5 0.1238 229.6187)'
    destructive: 'oklch(0.55 0.1902 23.0704)'
    destructive-foreground: 'oklch(1 0 0)'
    success: 'oklch(0.52 0.15 150)'
    success-foreground: 'oklch(1 0 0)'
    border: 'oklch(0.93 0.0094 286.2156)'
    input: 'oklch(0.9401 0 0)'
    ring: 'oklch(0.6441 0.1755 249.2498)'
    sidebar: 'oklch(0.19 0.014 264)'
    sidebar-foreground: 'oklch(0.93 0.004 260)'
    sidebar-accent: 'oklch(0.27 0.018 263)'
    sidebar-active: 'oklch(0.42 0.13 258)'
    sidebar-active-hover: 'oklch(0.47 0.13 258)'
typography:
    page-title:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '1.5rem'
        fontWeight: 700
        lineHeight: '2rem'
    heading:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '1.25rem'
        fontWeight: 600
        lineHeight: '1.75rem'
        letterSpacing: '-0.025em'
    section-title:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '1.125rem'
        fontWeight: 600
        lineHeight: '1.75rem'
    body:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '0.875rem'
        fontWeight: 400
        lineHeight: '1.25rem'
    label:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '0.875rem'
        fontWeight: 500
        lineHeight: '1'
    caption:
        fontFamily: 'Plus Jakarta Sans, sans-serif'
        fontSize: '0.75rem'
        fontWeight: 500
        lineHeight: '1rem'
rounded:
    sm: '18.4px'
    md: '20.4px'
    lg: '22.4px'
    xl: '26.4px'
spacing:
    1: '4.32px'
    2: '8.64px'
    3: '12.96px'
    4: '17.28px'
    6: '25.92px'
components:
    button-primary:
        backgroundColor: '{colors.primary}'
        textColor: '{colors.primary-foreground}'
        typography: '{typography.label}'
        rounded: '{rounded.md}'
        padding: '8.64px 17.28px'
        height: '38.88px'
    button-primary-hover:
        backgroundColor: 'oklch(0.55 0.158 243.9173 / 0.9)'
    button-destructive:
        backgroundColor: '{colors.destructive}'
        textColor: '{colors.destructive-foreground}'
        typography: '{typography.label}'
        rounded: '{rounded.md}'
        padding: '8.64px 17.28px'
        height: '38.88px'
    button-outline:
        backgroundColor: '{colors.background}'
        textColor: '{colors.foreground}'
        typography: '{typography.label}'
        rounded: '{rounded.md}'
        padding: '8.64px 17.28px'
        height: '38.88px'
    button-outline-hover:
        backgroundColor: '{colors.accent}'
        textColor: '{colors.accent-foreground}'
    button-ghost-hover:
        backgroundColor: '{colors.accent}'
        textColor: '{colors.accent-foreground}'
    input-default:
        backgroundColor: 'transparent'
        textColor: '{colors.foreground}'
        typography: '{typography.body}'
        rounded: '{rounded.md}'
        padding: '4.32px 12.96px'
        height: '38.88px'
    card-default:
        backgroundColor: '{colors.card}'
        textColor: '{colors.foreground}'
        rounded: '{rounded.xl}'
        padding: '25.92px'
    badge-default:
        backgroundColor: '{colors.primary}'
        textColor: '{colors.primary-foreground}'
        typography: '{typography.caption}'
        rounded: '{rounded.md}'
        padding: '2.16px 8.64px'
    sidebar-item-active:
        backgroundColor: '{colors.sidebar-active}'
        textColor: '{colors.sidebar-foreground}'
        typography: '{typography.body}'
---

# Design System: Skibidi Academic Management

## Overview

**Creative North Star: "The Academic Commons"**

A shared working room for a university: administrators, faculty and students use the same interface with different doors. The mood chosen for it is warm, approachable and refined. Warmth here comes from generous radii, relaxed spacing and a friendly geometric sans, not from a warm palette; the color system is a cool blue on near-white with a dark slate sidebar.

Density follows the work. Tables, filters and inline dialogs carry most screens, so type stays at 14px and controls stay compact, while cards and dialogs get large corners and 26px padding to keep dense data from feeling clinical. Light and dark themes are both first-class.

**Key Characteristics:**

- Cool blue on near-white, with a permanently dark slate sidebar
- Pill-shaped controls (md radius on 38.88px heights) inside large-radius cards
- One sans family (Plus Jakarta Sans) across all roles
- Flat at rest; shadows mark layer order only
- Every token pair documented here clears WCAG AA in both themes, with one noted exception

## Colors

A restrained OKLCH palette: one blue, one red, one green, and neutrals tinted slightly toward blue-violet. Values below are for light mode; the `.dark` set in `resources/css/app.css` mirrors the roles.

### Primary

- **Academic Blue** (`oklch(0.55 0.158 243.9173)`): buttons, links, default badges, selected states. White text on it is 4.71:1. Dark mode uses `oklch(0.56 0.158 243.9173)` (white 4.52:1).
- **Focus Ring Blue** (`oklch(0.6441 0.1755 249.2498)`): the 3px focus ring at 50% opacity around inputs, buttons and badges.

### Secondary

- **Mist** (`oklch(0.954 0.0063 255.4755)`): secondary buttons and badges, with near-black text.

### Tertiary

- **Confirm Green** (`oklch(0.52 0.15 150)`): success messages (5.02:1 on the page). Dark mode `oklch(0.76 0.15 150)`.

### Neutral

- **Paper** (`oklch(0.994 0 0)`): page, card and outline-button background.
- **Ink** (`oklch(0 0 0)`): body text.
- **Slate Text** (`oklch(0.4386 0 0)`): muted and secondary text (7.68:1).
- **Hairline** (`oklch(0.93 0.0094 286.2156)`): borders and dividers.
- **Field Gray** (`oklch(0.9401 0 0)`): input borders.
- **Cyan Wash** (`oklch(0.9782 0.0167 215.4555)`) with **Wash Text** (`oklch(0.5 0.1238 229.6187)`, 5.33:1): hover and accent surfaces.

### Status

- **Alert Red** (`oklch(0.55 0.1902 23.0704)`): destructive fills and error text; white on it is 5.35:1, and it is 5.26:1 as text on Paper. Dark mode is a lighter red (`oklch(0.7043 0.1781 22.7836)`) paired with near-black content.

### Sidebar (scoped to `[data-slot='sidebar']`)

- **Slate Night** (`oklch(0.19 0.014 264)`): sidebar surface in both themes.
- **Sidebar Text** (`oklch(0.93 0.004 260)`); section labels use it at 65%.
- **Active Pill** (`oklch(0.42 0.13 258)`), hover `oklch(0.47 0.13 258)`: solid, no glow; white text is 8.59:1.

### Named Rules

**The Quiet Blue Rule.** Primary blue marks actions, links, focus and the active state, and nothing else. Status belongs to destructive and success; never tint status with a raw Tailwind palette class.

**The AA Floor Rule.** Every foreground and background token pair clears 4.5:1 in both themes. The one known near-miss is dark-mode primary text on the page background (4.38:1); keep it to large or bold text.

**The Dark Anchor Rule.** The sidebar is dark in light and dark mode alike. It is the fixed anchor the light content is read against.

## Typography

**Sans:** Plus Jakarta Sans (400, 500, 600, 700) for everything.
**Serif:** Lora (400) and **Mono:** IBM Plex Mono (400, 500) are loaded and exposed as `--font-serif` and `--font-mono`; the mono face is used in only two places today (`two-factor-recovery-codes.tsx` and `peran-columns.tsx`).

**Character:** Rounded, open geometric letterforms that soften a data-heavy interface without losing precision.

### Hierarchy

- **Page title** (700, 1.5rem, 2rem): one `h1` per page, for example "Prodi" or "Pengguna".
- **Heading** (600, 1.25rem, 1.75rem, -0.025em): the shared `Heading` component in settings.
- **Section title** (600, 1.125rem, 1.75rem): subsections and dialog titles.
- **Body** (400, 0.875rem, 1.25rem): tables, forms, descriptions. Muted copy uses Slate Text.
- **Label** (500, 0.875rem, 1): form labels and button text.
- **Caption** (500, 0.75rem, 1rem): badges, counters, sidebar group labels.

There is no display size; nothing in the product needs one.

## Layout

A fixed left sidebar plus a scrolling content column. Sidebar width is 16rem (256px), 18rem (288px) as the mobile drawer, and 3rem (48px) when collapsed to icons. Below 768px the sidebar becomes a slide-in sheet.

Spacing uses a 0.27rem base unit (4.32px), so Tailwind steps are slightly larger than the defaults: `gap-6` and card padding are 25.92px, `h-9` controls are 38.88px, `px-4` is 17.28px. Page headers sit above list pages that combine a search field, an action button and a data table; create, edit and delete happen in dialogs rather than separate pages.

## Elevation & Depth

Flat by default with a small, soft vocabulary. Shadows are hsl black at 5 to 10% with a 1px offset and a 1 to 3px blur.

- **Control** (`shadow-xs`, `0 1px 3px 0 hsl(0 0% 0% / 0.05)`): buttons, inputs.
- **Card** (`shadow-sm`, `0 1px 3px 0 hsl(0 0% 0% / 0.1), 0 1px 2px -1px hsl(0 0% 0% / 0.1)`): cards, always together with a 1px border.
- **Menu** (`shadow-md`): dropdowns and popovers.
- **Dialog** (`shadow-lg`) over a `bg-black/80` overlay.

### Named Rules

**The Flat Rest Rule.** Controls rest on shadow-xs, cards on shadow-sm, menus on shadow-md, dialogs on shadow-lg. Depth states layer order only; no colored halos.

## Shapes

Generously round. The base radius is 1.4rem (22.4px) and the scale steps around it: sm 18.4px, md 20.4px, lg 22.4px, xl 26.4px. Controls use md, dialogs lg, cards xl, dropdown rows sm. Borders are 1px Hairline; focus is a 3px ring, not a thicker border.

### Named Rules

**The Soft Control Rule.** Buttons, inputs, selects and badges share the md radius. On 38.88px controls that radius exceeds half the height, so they read as pills. Cards step up to xl; dialogs use lg.

## Components

### Buttons

- **Shape:** md radius, 38.88px high (47.52px on coarse pointers), 17.28px horizontal padding, 14px / 500 text.
- **Default:** Academic Blue fill, white text, `shadow-xs`; hover at 90% opacity.
- **Destructive:** Alert Red fill with `destructive-foreground` text.
- **Outline:** Paper fill, Field Gray border; hover switches to the cyan wash.
- **Secondary, Ghost, Link:** Mist fill; transparent with cyan-wash hover; text-only with underline on hover.
- **Icon:** 38.88px square, 47.52px on coarse pointers. Must carry an `aria-label`.
- **Focus:** 3px ring at 50% ring color. **Disabled:** 50% opacity, no pointer events.

### Inputs / Fields

- **Style:** transparent fill, 1px Field Gray border, md radius, 38.88px high, 12.96px horizontal padding, `shadow-xs`.
- **Focus:** border turns ring blue plus a 3px ring at 50%.
- **Error:** border and ring switch to destructive (ring 20% in light, 40% in dark).

### Cards / Containers

- **Shape:** xl radius, 1px border, `shadow-sm`, 25.92px vertical padding and 25.92px horizontal padding on each section, 25.92px gap between sections.
- **Title:** semibold with `leading-none`; description in Slate Text at 14px.

### Badges

- **Style:** md radius, 12px / 500 text, 8.64px by 2.16px padding, 1px border. Variants: default (blue), secondary (mist), destructive (red), outline.

### Navigation (sidebar)

- **Surface:** Slate Night, always dark. Items are 14px text; hover adds a 6% white wash; the active item is the solid Active Pill with white 500-weight text; pressing scales to 0.98 (removed under reduced motion).
- **Groups:** a caption-size label at 65% opacity, with collapsible child items.

### Dialogs

- **Shape:** lg radius, 1px border, `shadow-lg`, 25.92px padding, max width 32rem on wider screens, zoom and fade in over 200ms (fade only under reduced motion).

## Do's and Don'ts

### Do:

- Do draw status color from tokens: text-destructive for errors, text-success for confirmations.
- Do give every icon-only button an aria-label; title alone is not a name.
- Do keep controls at 38.88px (h-9) on fine pointers; they grow to 47.52px (size-11) on coarse pointers automatically.
- Do use page-title (24px / 700) once per page and section-title (18px / 600) inside it.
- Do pair bg-destructive with text-destructive-foreground; in dark mode that foreground is near-black by design.
- Do keep fades when motion is reduced; slide and zoom are removed globally under prefers-reduced-motion.

### Don't:

- Don't use text-destructive-foreground as a text color on light surfaces; it is the color for content placed on a destructive fill.
- Don't introduce radii outside sm, md, lg, xl (18.4 / 20.4 / 22.4 / 26.4px).
- Don't hard-code palette classes (red-600, green-600, neutral-*) or hex colors in components.
- Don't add a second elevation signal to a card: it already carries a border and shadow-sm.
- Don't size text off the ramp (10px, 13px); the smallest step is caption at 12px.
- Don't override icon button size with fixed h-/w- classes; that defeats the coarse-pointer target size.
