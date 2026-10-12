# AI AGENT INSTRUCTIONS & CODING RULES (AGENTS.md)

This repository is **UndanganDigital**, a modern SaaS platform for digital wedding invitations built with Laravel 11, Inertia.js v2, Vue 3 (`<script setup lang="ts">`), Tailwind CSS, and PostgreSQL, deployed on Railway via Docker.

---

## 🚨 MANDATORY INSTRUCTIONS FOR ANY AI AGENT

Before proposing or generating any code in this repository, you **MUST** read and adhere to the guidelines in the [`docs/`](./docs/README.md) directory:
1. [`docs/01_ARCHITECTURE_AND_TECH_STACK.md`](./docs/01_ARCHITECTURE_AND_TECH_STACK.md)
2. [`docs/02_TEMPLATE_AND_THEME_SYSTEM.md`](./docs/02_TEMPLATE_AND_THEME_SYSTEM.md)
3. [`docs/03_UI_UX_AND_DESIGN_SYSTEM.md`](./docs/03_UI_UX_AND_DESIGN_SYSTEM.md)
4. [`docs/04_DATABASE_AND_API_CONTRACTS.md`](./docs/04_DATABASE_AND_API_CONTRACTS.md)
5. [`docs/05_DEVELOPMENT_AND_AI_PROMPTING_RULES.md`](./docs/05_DEVELOPMENT_AND_AI_PROMPTING_RULES.md)

---

## 🚫 STRICT ANTI-PATTERNS (DO NOT DO)

1. **NO BROWSER `alert()` OR `confirm()`**:
   - ❌ Never use `window.alert(...)` or `window.confirm(...)`.
   - ✅ Always use `useToast` (`toast.success()`, `toast.error()`, `toast.info()`, `toast.warning()`).
   - ✅ Always use `useConfirm` (`const ok = await confirmAction({ ... })`).

2. **NO SEPARATE LOGIN FOR ADMIN**:
   - ❌ Never create a separate admin login page, route, or separate admin user table.
   - ✅ Unified login is located at `/login`. The role (`admin` vs `user`) in `users` table automatically determines redirection.

3. **NO BREAKING `design_json` SCHEMA**:
   - ❌ Never delete, rename, or rearrange existing properties in `design_json`.
   - ✅ Schema is strictly additive. New features must provide fallback defaults.

4. **MAINTAIN 0ms POSTMESSAGE SYNC**:
   - Keep the `postMessage({ type: 'UPDATE_DESIGN', ... })` communication between `Editor/Index.vue` and `InvitationView.vue` intact.

5. **USE VISUAL FORM INPUTS**:
   - Date: `<input type="date">`
   - DateTime: `<input type="datetime-local">`
   - Color: `<input type="color">`

6. **VERIFY BUILD**:
   - Always verify your changes compile cleanly with `npm run build`.
