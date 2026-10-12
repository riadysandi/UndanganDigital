---
name: wedding-platform
description: >-
  Use this skill whenever working on the UndanganDigital codebase, developing new themes,
  modifying the interactive visual editor, adding new invitation features, or managing
  guest RSVP, check-in, and billing workflows.
---

# Wedding Platform Expert Skill

This skill equips the agent with domain-specific knowledge, data contracts, and architectural rules for the **UndanganDigital** application.

## Core Rules

1. **Adhere to `docs/`**:
   - Skema desain: [docs/02_TEMPLATE_AND_THEME_SYSTEM.md](../../../docs/02_TEMPLATE_AND_THEME_SYSTEM.md)
   - Notifikasi & Dialog: [docs/03_UI_UX_AND_DESIGN_SYSTEM.md](../../../docs/03_UI_UX_AND_DESIGN_SYSTEM.md)
   - Database & API: [docs/04_DATABASE_AND_API_CONTRACTS.md](../../../docs/04_DATABASE_AND_API_CONTRACTS.md)

2. **Never Use Native Browser Dialogs**:
   - Use `useToast` from `@/composables/useToast` for toasts.
   - Use `useConfirm` from `@/composables/useConfirm` for confirmation modals.

3. **Maintain Real-time PostMessage Sync**:
   - `Editor/Index.vue` communicates with `InvitationView.vue` via `postMessage({ type: 'UPDATE_DESIGN', design: form.design_json })`.

4. **Additive Schema Modifications Only**:
   - Add new properties to `design_json` with sensible defaults. Never delete existing properties.

5. **Build Verification**:
   - Run `npm run build` to verify Vue 3 + TypeScript compilation before completing tasks.
