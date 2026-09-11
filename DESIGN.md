# Konsulin Manager Design Direction

## Product Identity
Konsulin Manager is a high-craft Jira-inspired project management system built specifically for tax, accounting, and corporate advisory services.

## Core Dials
- Dial: ENERGY 2 / RHYTHM 2 / MOTION 1
- Visual Language: White & Deep Navy precision workspace (tactile, crisp, shadcn-inspired).

## Palette
- Primary Dark: Deep Navy (`#0B192C`, `#1E3E62`, `#0F172A`)
- Canvas & Surfaces: Clean White (`#FFFFFF`), Light Slate background (`#F8FAFC`, `#F1F5F9`)
- Borders: Crisp Slate (`#E2E8F0`, `#CBD5E1`)
- Neutral Typography: Charcoal (`#0F172A`, `#334155`), Muted (`#64748B`)
- Status Accents (Functional Only):
  - Not Started / Todo: Slate (`#64748B`, bg `#F1F5F9`)
  - In Progress: Navy Blue (`#1D4ED8`, bg `#EFF6FF`)
  - Waiting Client: Warm Amber (`#B45309`, bg `#FFFBEB`)
  - Completed: Forest Emerald (`#047857`, bg `#ECFDF5`)
  - Blocker / Threat: Crimson (`#B91C1C`, bg `#FEF2F2`)

## Typography & Hierarchy
- Clean sans-serif with strict semantic weights:
  - Headers: 600-700 weight, tight tracking (-0.02em)
  - Labels & Meta: 500-600 weight, 11-12px, uppercase with restrained 0.04em tracking
  - Body & Cards: 13-14px, 400-500 weight, high contrast (> 4.5:1 ratio)

## Craftsmanship & Anti-Slop Rules
1. Zero em dashes in any copy or interface text.
2. Every interactive control has real functional behavior (no dead buttons).
3. Resilient layout with empty states, loading feedback, and search filters.
4. WCAG AA compliant color contrast on all text and badges.
5. Full keyboard accessibility (Tab, Enter/Space, Escape) with visible focus indicators.
