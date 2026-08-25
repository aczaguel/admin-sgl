# Implementation Tasks — Pre-Production Hardening & Final Adjustments

## Overview

Tasks derived from `list_of_requirements.md`. Covers functional fixes, UX improvements, infrastructure corrections, and deployment work completed and pending before full production promotion.

---

## Tasks

### Infrastructure & Deployment

- [x] 1. Deploy Prod IaaS stack via Terraform (EC2, EIP, SGs, IAM, S3 bucket)
  - [x] 1.1 Create terraform-prod-iaas stack and deploy to AWS
  - [x] 1.2 Configure EC2 with Docker + app via user-data
  - [x] 1.3 Connect app to prod RDS and dev S3 bucket
  - [x] 1.4 Configure SSM Session Manager access (no SSH)
  - [x] 1.5 Fix EC2 egress rules for HTTP/HTTPS and MySQL

- [x] 2. EC2 Scheduler — auto start/stop to reduce costs
  - [x] 2.1 Create Lambda (Python) to start/stop EC2 via EventBridge
  - [x] 2.2 Configure EventBridge rules: start 08:00 CDMX / stop 20:00 CDMX daily
  - [x] 2.3 Wire scheduler module into terraform-prod-iaas and validate
  - [ ] 2.4 Run `terraform apply` to deploy scheduler to AWS
  - [ ] 2.5 Test Lambda invocation manually (start/stop)

### Application Fixes (FR-01 to FR-17)

- [x] 3. FR-01 — Timezone fix
  - [x] 3.1 Set MySQL session timezone to America/Mexico_City via Events.php
  - [x] 3.2 Add MYSQLI_INIT_COMMAND to all GroceryCRUD _getDbData() connections

- [x] 4. FR-02 — Extend bank reference field length
  - [x] 4.1 Migration: extend tramite.derechos_refer_banc to VARCHAR(100)
  - [x] 4.2 Update HTML maxlength and server-side validation rules
  - [ ] 4.3 Run `php spark migrate` on production server

- [x] 5. FR-04 — Remove time from reports and exports
  - [x] 5.1 Add format_date_ymd() helper with DD/MM/YYYY format
  - [x] 5.2 Apply to all GroceryCRUD date columns via BaseController

- [ ] 6. FR-05 — Validate subprocedure integration in Pago a Gestor
  - [ ] 6.1 Execute regression testing on subprocedure linking workflow
  - [ ] 6.2 Verify data persistence across steps

- [ ] 7. FR-06 / FR-07 / FR-08 / FR-09 / FR-10 — Pending stakeholder validation
  - [ ] 7.1 Review with stakeholders and decide implementation scope
  - [ ] 7.2 Implement once requirements are confirmed

- [ ] 8. FR-11 / FR-12 — Collection reports
  - [ ] 8.1 Verify if collection reports already exist in the system
  - [ ] 8.2 Design and implement missing report types if needed

- [ ] 9. FR-13 / FR-14 — Invoice and payment filtering
  - [ ] 9.1 Clarify expected behavior with stakeholders
  - [ ] 9.2 Add invoice number and customer payment filters to exports

- [x] 10. FR-15 — Historical contract search
  - [x] 10.1 Remove year filter from tramitesn listing query

- [x] 11. FR-16 — Restore 2026 Consolidated button
  - [x] 11.1 Uncomment and restore the button in _header.php

- [x] 12. FR-17 — Landing page images (already using base_url, no changes needed)

### Unified Layout Improvements

- [x] 13. Search by contrato — show full tramite list when multiple results
  - [x] 13.1 Fetch all tramites for a contrato in search()
  - [x] 13.2 Render results table with folio, contrato, tipo, ejecutivo, entidad, estatus, fecha
  - [x] 13.3 Redirect directly when only one result

- [x] 14. Document preview lightbox
  - [x] 14.1 Add lightbox modal HTML to index.php
  - [x] 14.2 Add CSS styles for lightbox
  - [x] 14.3 Add JS to intercept gallery link clicks (images → img, PDF → iframe)
  - [x] 14.4 Fix PDF inline preview via S3 ResponseContentDisposition=inline
  - [x] 14.5 Add file_inline_url() helper and inlineUrl() to S3FileStorage

- [x] 15. Multiple file uploads per document category
  - [x] 15.1 Add `multiple` attribute to all file inputs in unified layout steps
  - [x] 15.2 Update JS to upload files sequentially via _uploadQueue
  - [x] 15.3 Update upload_step1_doc backend to accumulate files (comma-separated)

- [x] 16. Fix notes in Paso 4 and Paso 5
  - [x] 16.1 Fix bitacoraDb bug — use $db instead of _getDbData() in else branch

- [x] 17. Detailbar improvements
  - [x] 17.1 Add Creación and Inicio date items to the detailbar
  - [x] 17.2 Include created_at and started_at in loadPrototypeReadOnlyTramite()
  - [x] 17.3 Format dates as DD/MM/YYYY in detailbar

- [x] 18. Layout and visual improvements
  - [x] 18.1 Change three-rail grid to 2fr 1.5fr 1fr (more space for forms)
  - [x] 18.2 Fix overflow:hidden → overflow:visible on rails and step-rows
  - [x] 18.3 Fix zoom/gap issue — reduce padding-top on #main-content
  - [x] 18.4 Compact detailbar font (8px labels, 11px values, 10px padding)
  - [x] 18.5 Filter document gallery to only show uploaded documents

### Client-Facing View

- [x] 19. Client read-only unified layout (uclient) — steps 1-3 only
  - [x] 19.1 Add `unified_client()` method to Tramitesn controller with full read-only view data
  - [x] 19.2 Create `app/Views/deskapp/tramite_unified/index_client.php` (steps 1-3, no financials, no actions)
  - [x] 19.3 Add route `GET deskapp/clientes/ver/(:num)` → redirect to `tramitesn/unified-client?tramite_id={id}`
  - [x] 19.4 Add route `GET tramitesn/unified-client` → `Tramitesn::unified_client`

### Performance & CSS Fixes

- [x] 21. Remove attention bucket toolbar and heavy SQL queries from tramite list
  - [x] 21.1 Remove buildAttentionBucketSql() WHERE clause from renderTramiteList()
  - [x] 21.2 Remove getAttentionTrackedStatusIds() and buildAttentionListSummary() calls
  - [x] 21.3 Simplify started_at and cobro_status_id callbacks to O(1) status checks
  - [x] 21.4 Remove pre_output_html toolbar view from salida_total

- [x] 22. CSS zoom and sidebar responsiveness fixes
  - [x] 22.1 Add height:auto + min-height to .header and .header-left/.header-right
  - [x] 22.2 Add max-height:100vh + overflow-y:auto to .left-side-bar
  - [x] 22.3 Use clamp() for ribbon-layout main-container padding-top
  - [x] 22.4 Add max-height + overflow to .left-side-bar .menu-block

- [x] 23. Fix GroceryCRUD export (ob_start removal)
  - [x] 23.1 Remove ob_start() callback from Events.php pre_system event that blocked binary file downloads

### Cobranza Reports

- [x] 24. CSV export reports for cobranza
  - [x] 24.1 Add exportarPendientes() method to Cobranza controller
  - [x] 24.2 Add exportarPorPeriodo() method with date range filter
  - [x] 24.3 Add export routes: GET deskapp/cobranza/exportar/pendientes and /periodo
  - [x] 24.4 Add export toolbar with date pickers to cobranza/index.php view
  - [x] 24.5 Fix 404: remove duplicate /deskapp/ prefix from routes inside group

### Dashboard Cliente Analysis

- [ ] 25. Improve client dashboard (/deskapp/clientes/dashboard)
  - [x] 25.1 Analyze current dashboard — identified missing: tramites list, alerts with links, navigation CTA
  - [ ] 25.2 Add "recent tramites" section with link to unified-client view
  - [ ] 25.3 Add urgent/atorados mini-list with direct links per tramite
  - [ ] 25.4 Add "Ver todos mis trámites" CTA button
  - [ ] 25.5 Link facturas pendientes tile to filtered tramites list

### External API (REST)

- [ ] 26. REST API for external app consumption
  - [ ] 26.1 Design auth strategy — API key or Bearer token stored in `api_tokens` table (user_id, token, scopes, expires_at)
  - [ ] 26.2 Create `ApiAuthFilter` middleware — validates `Authorization: Bearer {token}` header on all `/api/v2/*` routes
  - [ ] 26.3 `GET /api/v2/tramites` — paginated list with filters: status, tipo, cliente, fecha_inicio, fecha_fin
  - [ ] 26.4 `GET /api/v2/tramites/{id}` — full tramite detail including current status, new_format_step, folio, tipo, cliente, gestor
  - [ ] 26.5 `POST /api/v2/tramites/{id}/status` — advance status to canonical step (body: `{"step": 2}`); uses same forward-only logic as updateTramiteStatus()
  - [ ] 26.6 `GET /api/v2/tramites/{id}/status` — returns current `tra_status_id`, `new_format_step`, step label, and status history
  - [ ] 26.7 `GET /api/v2/tramites/{id}/documents` — list uploaded documents per category with presigned URLs
  - [ ] 26.8 `GET /api/v2/clientes` — list of cli_directo scoped to the token's user permissions
  - [ ] 26.9 `GET /api/v2/catalogos/statuses` — returns full status catalog with new_format_step grouping
  - [ ] 26.10 Add routes in Routes.php under `group('api/v2', ...)` with `ApiAuthFilter`
  - [ ] 26.11 Return consistent JSON envelope: `{"data": ..., "meta": {...}, "error": null}`
  - [ ] 26.12 Document all endpoints in `API_EXTERNA.md` at project root (curl examples + Postman collection JSON)

### Pending Push to Production

- [ ] 20. Push and deploy all pending local changes
  - [ ] 20.1 Push all accumulated local changes to repo and deploy to EC2
  - [ ] 20.2 Run `terraform apply` for scheduler module
  - [ ] 20.3 Run `php spark migrate` for bank reference column extension

---

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1", "3", "4", "5", "10", "11", "12"] },
    { "id": 1, "tasks": ["2", "13", "14", "15", "16", "17", "18"] },
    { "id": 2, "tasks": ["19", "21", "22", "23", "24"] },
    { "id": 3, "tasks": ["20", "25"] },
    { "id": 4, "tasks": ["6", "7", "8", "9"] },
    { "id": 5, "tasks": ["26"] }
  ]
}
```
