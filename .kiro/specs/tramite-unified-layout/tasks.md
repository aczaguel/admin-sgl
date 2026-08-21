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

### Multi-tenant (Nexter)

- [ ] 19. Configure Nexter as a tenant in the existing multi-tenancy system
  - [ ] 19.1 Create `cliente` record for Nexter
  - [ ] 19.2 Assign Nexter's cli_directo records with cliente_id = nexter_id
  - [ ] 19.3 Create Nexter user accounts and assign in cliente_user table
  - [ ] 19.4 Verify filter isolation (Nexter users only see their tramites)

### Pending Push to Production

- [ ] 20. Push and deploy all pending local changes
  - [ ] 20.1 Push: datetime format DD/MM/YYYY, detailbar dates, grid/overflow CSS, lightbox, PDF inline, scheduler module
  - [ ] 20.2 Run `terraform apply` for scheduler
  - [ ] 20.3 Run `php spark migrate` for bank reference column extension

---

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1", "3", "4", "5", "10", "11", "12"] },
    { "id": 1, "tasks": ["2", "13", "14", "15", "16", "17", "18"] },
    { "id": 2, "tasks": ["20"] },
    { "id": 3, "tasks": ["6", "7", "8", "9", "19"] }
  ]
}
```
