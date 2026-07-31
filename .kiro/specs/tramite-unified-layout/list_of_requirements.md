# Spec: SGL Pre-Production Hardening & Final Adjustments

## Overview

Before promoting the current SGL implementation to the Production environment, a final stabilization phase is required.

This specification groups together all functional adjustments, bug fixes, usability improvements, pending validations, infrastructure corrections, and cleanup tasks identified during testing and review sessions.

The objective is **not to introduce new business features**, but to ensure that the existing implementation is stable, consistent, and ready for Production.

---

# Objectives

- Fix remaining functional issues.
- Improve data consistency.
- Improve report usability.
- Improve Production readiness.
- Complete pending validations.
- Resolve historical search limitations.
- Remove hardcoded configurations.
- Review unfinished requirements before Production deployment.

---

# Scope

This specification includes:

- Date/Time fixes
- Database adjustments
- UX improvements
- Report improvements
- Historical searches
- Infrastructure corrections
- Validation of pending requirements
- Minor workflow refinements

No business process redesign is expected.

---

# Functional Requirements

---

## FR-01 — Review Time Zone Handling

### Description

Review how dates are stored and displayed throughout the application.

Currently some dates appear with approximately **6 hours of difference**, indicating an incorrect timezone conversion.

### Areas to review

- Database save operations
- Models
- Controllers
- Lists
- Forms
- Reports
- Exported files

### Expected Result

Dates must remain consistent during:

- Save
- Read
- Display
- Export

---

## FR-02 — Extend Bank Reference Length

### Description

The bank reference field is currently truncated.

Increase the allowed length in:

- Database column
- Validation rules
- Forms
- Models (if applicable)

The user must be able to enter the complete banking reference without truncation.

---

## FR-03 — Email Notifications (Future Phase)

Leave this functionality documented for the next development iteration.

Desired behavior:

- Send an email automatically whenever a new procedure is created.
- Include:
  - Procedure Number (Folio)
  - Direct URL to the procedure
  - Assigned service users

**Status:** Deferred to a future release.

---

## FR-04 — Remove Time Portion from Dates

### Description

Downloaded reports currently include both **date and time**.

This makes filtering information by day in Excel unnecessarily difficult.

### Required Changes

Remove the time portion from:

- Calendar controls (where time is not required)
- Tables
- Lists
- Excel exports
- PDF reports (if applicable)

Display only:

```
YYYY-MM-DD
```

instead of

```
YYYY-MM-DD HH:MM:SS
```

---

## FR-05 — Validate Subprocedure Integration

Recent implementation changes appear to have solved the Subprocedure integration within "Pago a Gestor".

### Required Action

Execute regression testing to verify:

- Subprocedure linking
- Workflow integrity
- Data persistence

No development is expected unless defects are identified.

---

## FR-06 — Review Payment Rights Requirement

There was a previous request to include Subprocedure Rights inside Payment Rights.

Current status is uncertain.

### Required Action

- Review the README documentation.
- Validate with stakeholders.
- Decide whether implementation is still required.

---

## FR-07 — Review Pending "Pago a Gestor" Requirement

Previous notes indicate that an additional modification was requested.

The exact requirement is currently unknown.

### Required Action

- Review previous documentation.
- Validate with stakeholders before implementing.

---

## FR-08 — Review Direct Navigation After Editing

Previous notes mention providing a direct link after editing a procedure.

Current expected behavior is unclear.

### Required Action

Review previous documentation before implementation.

---

## FR-09 — Quotation Responsibility

Currently the procedure creator remains the responsible user throughout the workflow.

Evaluate introducing a dedicated field:

```
Quotation Responsible
```

This would allow separating:

- Procedure Creator
- Quotation Responsible

Business validation is required before implementation.

---

## FR-10 — Invoice Number Field

Review the previous request related to the Invoice Number field.

Implementation remains pending until clarification.

---

## FR-11 — Collection Reports

Verify whether Collection Reports already exist.

If they do not exist:

- Design them.
- Implement them.
- Validate generated information.

---

## FR-12 — Collection Report Types

Review which Collection Reports should be available.

Possible report types include:

- Pending Collections
- Paid Collections
- Monthly Collections
- Customer Collections

Business validation required.

---

## FR-13 — Customer Invoice Requirement

Clarify the original requirement before implementation.

No development should begin until the expected behavior is confirmed.

---

## FR-14 — Download & Filter Improvements

Merge the previous duplicated requests.

Downloaded information should allow filtering by:

- Invoice Number
- Customer Payment

Review export format if necessary.

---

## FR-15 — Historical Contract Search

### Description

Current contract search only retrieves contracts from the current year.

### Required Change

Modify search logic to retrieve information from the complete historical dataset.

Expected behavior:

- Search across all years.
- Include archived contracts.
- Preserve existing filters.

---

## FR-16 — Restore 2026 Consolidated Button

Restore the **2026 Consolidated** button.

Validate:

- Permissions
- Export behavior
- Data source

---

## FR-17 — Migrate Landing Page Images to Amazon S3

### Description

The images displayed on the application's landing page are currently referenced using absolute local/server paths.

This configuration is not suitable for the Production environment and prevents proper content delivery.

### Required Changes

- Review all image references used on the landing page.
- Replace absolute file system paths with Amazon S3 object URLs.
- Ensure image paths are configurable through the application configuration or environment variables where applicable.
- Verify that all images load correctly from the S3 bucket in Development, QA, and Production environments.
- Remove any hardcoded local paths that are no longer required.

### Expected Result

- All landing page images are served directly from the configured Amazon S3 bucket.
- No absolute server paths remain in the application.
- Image loading is environment-independent and fully compatible with Production deployments.

---

# Out of Scope

The following items are intentionally excluded from this specification:

- New business workflows
- Additional functional modules
- Major UI redesign
- Email notification implementation
- New business processes
- Functional enhancements unrelated to Production readiness

---

# Acceptance Criteria

The implementation will be considered complete when:

- No timezone inconsistencies remain.
- Bank references are no longer truncated.
- Reports display only dates where appropriate.
- Historical contract searches include every available year.
- Pending requirements have been reviewed and documented.
- Collection reports have been validated or implemented.
- Landing page images are loaded exclusively from the configured Amazon S3 bucket.
- No hardcoded infrastructure paths remain.
- Regression testing completes successfully.
- No new issues are introduced.
- The application is considered Production Ready.

---

# Deliverables

- Updated application source code
- Database migrations (if required)
- Updated README documentation
- Configuration updates
- Regression testing completed
- Production-ready Release Candidate