# UI Table Design Guidelines

This document defines the standards for table design across all dashboard pages to ensure a consistent, professional, and modern look using DaisyUI and Tailwind CSS.

## Table Container
- Use a carded container: `bg-white rounded-xl shadow-sm p-4 border`.
- Add margin-top for separation from other elements: `mt-6`.

## Table Structure
- Use DaisyUI's `table` and `table-zebra` classes for all tables.
- Table header: subtle color (`text-gray-500`), bold font for column titles.
- Table rows: zebra striping, hover effect (`hover:bg-base-200/60`).
- First column: checkbox for bulk actions (`checkbox checkbox-sm`).
- Last column: action menu (ellipsis button or icons).

## Table Content
- Use badges for status fields: `badge badge-success`, `badge badge-warning`, etc.
- Use DaisyUI buttons for actions: `btn btn-ghost btn-xs` for row actions.
- Use consistent spacing and font sizes for all cells.
- Use rounded corners for table and controls.

## Filters & Search
- Place filter selects and search input in a flex row above the table.
- Use DaisyUI `select select-bordered select-sm` for filters.
- Use DaisyUI `input input-bordered input-sm` for search.
- Export buttons: `btn btn-outline btn-sm`.

## Pagination
- Use DaisyUI's `join` class for pagination controls.
- Active page: `btn-active`.
- Place pagination below the table, centered or justified as needed.

## Modals
- Use DaisyUI modal for create/edit actions.
- Form fields: DaisyUI `input`, `select`, and `form-control` classes.
- Modal actions: `btn btn-primary` for submit, `btn btn-ghost` for cancel.

## Sidebar
- Use icons and section dividers for clarity.
- Highlight active menu item with background and color.
- Add hotel info, staff badge, dark mode toggle, help/support, and logout.

## General Principles
- Consistent use of DaisyUI and Tailwind classes.
- Minimal, clean, and modern look.
- Responsive design for all table layouts.
- Use avatars and icons where appropriate.
- Avoid excessive borders; use soft shadows and spacing.

---

**Reference:**
All table designs should closely follow the style shown in the shared dashboard images for a unified user experience.
