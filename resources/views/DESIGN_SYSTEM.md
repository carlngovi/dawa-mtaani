# DawaMtaani UI Design System
**Version:** v3.6
**Theme:** Dark (forced globally via `class="dark"` on `<html>`)
**Brand colors:** Yellow-400 (#FACC15) accent, Gray-900 (#111827) base
**Last updated:** March 2026

---

## COLOR PALETTE

| Role | Class | Hex |
|------|-------|-----|
| Page background | `bg-gray-900` | #111827 |
| Card / surface | `bg-gray-800` | #1F2937 |
| Elevated surface | `bg-gray-700` | #374151 |
| Inset / recessed | `bg-gray-900/50` | — |
| Input background | `bg-gray-900` | #111827 |
| Code / terminal bg | `bg-gray-900` | #111827 |
| Border (standard) | `border-gray-700` | #374151 |
| Border (input / subtle) | `border-gray-600` | #4B5563 |
| Heading text | `text-white` | #FFFFFF |
| Body text | `text-gray-300` | #D1D5DB |
| Secondary text | `text-gray-400` | #9CA3AF |
| Disabled / placeholder | `text-gray-500` | #6B7280 |
| Brand accent | `text-yellow-400` | #FACC15 |
| Brand accent bg | `bg-yellow-400` | #FACC15 |
| Success | `text-green-400` | #4ADE80 |
| Warning | `text-amber-400` | #FBBF24 |
| Danger | `text-red-400` | #F87171 |
| Info | `text-blue-400` | #60A5FA |

---

## LAYOUT

### Page wrapper
```html
<div class="space-y-6">
    {{-- All page content --}}
</div>
```

### Page header
```html
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-white">Page Title</h1>
        <p class="text-sm text-gray-400 mt-1">Page description</p>
    </div>
    {{-- Optional: tier badge or action button --}}
</div>
```

### Page header with tier badge
```html
<div class="flex items-center gap-3">
    <div>
        <h1 class="text-2xl font-bold text-white">Page Title</h1>
        <p class="text-sm text-gray-400 mt-1">Description</p>
    </div>
    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
        Tier 1
    </span>
</div>
```

### Page header with action button
```html
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-white">Page Title</h1>
        <p class="text-sm text-gray-400 mt-1">Description</p>
    </div>
    <button class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
        + Create Item
    </button>
</div>
```

---

## CARDS

### Standard card
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    {{-- Card content --}}
</div>
```

### Card with header
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-700">
        <h3 class="text-sm font-semibold text-gray-300">Section Title</h3>
    </div>
    <div class="p-5">
        {{-- Content --}}
    </div>
</div>
```

### Card with tinted header (form sections)
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
        <h3 class="text-sm font-semibold text-gray-300">Form Section</h3>
    </div>
    <div class="p-5">
        {{-- Form fields --}}
    </div>
</div>
```

### Stat / KPI card
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
    <p class="text-xs text-gray-400">Label</p>
    <p class="text-3xl font-bold text-white mt-1">42</p>
</div>
```

### Stat card with contextual border
```html
{{-- Border color changes based on value --}}
<div class="bg-gray-800 rounded-xl border border-{{ $value > 0 ? 'red-800' : 'gray-700' }} p-5">
    <p class="text-xs text-gray-400">Label</p>
    <p class="text-3xl font-bold text-{{ $value > 0 ? 'red-400' : 'white' }} mt-1">
        {{ $value }}
    </p>
</div>
```

### Empty state
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
    <p class="text-gray-400 text-sm">No items found.</p>
</div>
```

---

## TABLES

### Full table pattern
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Column</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Responsive Col</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <tr class="hover:bg-gray-700/50">
                    <td class="px-5 py-3 font-medium text-gray-200">Primary text</td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">Secondary</td>
                    <td class="px-5 py-3 text-right text-gray-300">KES 1,200</td>
                    <td class="px-5 py-3">
                        {{-- Action buttons --}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Table cell variants
```html
{{-- Primary cell --}}
<td class="px-5 py-3 font-medium text-gray-200">Name</td>

{{-- Secondary / date cell --}}
<td class="px-5 py-3 text-xs text-gray-400">12 Mar, 14:30</td>

{{-- Monospace cell (IDs, codes, refs) --}}
<td class="px-5 py-3 font-mono text-xs text-gray-300">ABC123XY</td>

{{-- Right-aligned numeric cell --}}
<td class="px-5 py-3 text-right text-gray-300">KES 5,400</td>

{{-- Responsive hidden column --}}
<td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">Value</td>
```

### Compact inline table (no card wrapper)
```html
<table class="min-w-full text-xs">
    <thead>
        <tr class="text-gray-400 border-b border-gray-700">
            <th class="text-left pb-1 pr-4">Col</th>
        </tr>
    </thead>
    <tbody>
        <tr class="border-b border-gray-800">
            <td class="py-1.5 pr-4 text-gray-200">Value</td>
        </tr>
    </tbody>
</table>
```

---

## BUTTONS

### Primary (brand action)
```html
<button class="px-6 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm font-bold hover:bg-yellow-500 transition-colors">
    Save Settings
</button>
```

### Primary (operational — green)
```html
<button class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
    + Create Item
</button>
```

### Danger / destructive
```html
<button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
    Submit Request
</button>
```

### Secondary / ghost
```html
<button class="px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
    Cancel
</button>
```

### Small table action button
```html
<button class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800">
    Approve
</button>
```

### Small outline button
```html
<button class="px-3 py-1.5 border border-gray-600 text-gray-400 rounded-lg text-xs hover:bg-gray-700/50">
    Details
</button>
```

### Small danger outline button
```html
<button class="px-3 py-1.5 border border-red-800 text-red-400 rounded-lg text-xs hover:bg-red-900/20">
    Reject
</button>
```

### Small warning / retry button
```html
<button class="px-3 py-1.5 bg-amber-900/30 text-amber-400 border border-amber-800 rounded-lg text-xs hover:bg-amber-900/50">
    Retry
</button>
```

---

## BADGES / STATUS

### Pattern
```html
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {classes}">
    LABEL
</span>
```

### Status badge classes

| Status | Classes |
|--------|---------|
| Success / Active / OK | `bg-green-900/30 text-green-400` |
| Warning / Pending | `bg-amber-900/30 text-amber-400` |
| Danger / Failed / Error | `bg-red-900/30 text-red-400` |
| Info / In Progress | `bg-blue-900/30 text-blue-400` |
| Critical (high severity) | `bg-red-900/30 text-red-400` |
| High severity | `bg-orange-900/30 text-orange-400` |
| Medium severity | `bg-amber-900/30 text-amber-400` |
| Low / Neutral / Inactive | `bg-gray-700 text-gray-400` |
| Default / Unknown | `bg-gray-700 text-gray-400` |

### Tier badge (in page headers)
```html
{{-- Tier 0 (Technical Admin) --}}
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-red-800">
    Tier 0
</span>

{{-- Tier 1 (Super Admin) --}}
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
    Tier 1
</span>
```

### Role / category badge
```html
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300">
    role name
</span>
```

### Badge with counter (pills)
```html
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
    Label
    <span class="bg-gray-600 text-gray-200 rounded-full px-1.5 py-0.5 text-xs">12</span>
</span>
```

---

## FORMS

### Input field
```html
<div>
    <label class="block text-xs font-medium text-gray-400 mb-1">Field Label</label>
    <input type="text" name="field"
           placeholder="Placeholder text..."
           class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
</div>
```

### Select dropdown
```html
<div>
    <label class="block text-xs font-medium text-gray-400 mb-1">Select Label</label>
    <select name="field" required
            class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Select...</option>
        <option value="A">Option A</option>
    </select>
</div>
```

### Textarea
```html
<div>
    <label class="block text-xs font-medium text-gray-400 mb-1">Description</label>
    <textarea name="field" rows="4" required
              placeholder="Enter details..."
              class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
</div>
```

### Terminal / code textarea
```html
<textarea name="sql" rows="4"
          placeholder="SELECT * FROM ..."
          class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 rounded-lg text-sm text-green-400 font-mono placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
```

### Compact inline input (tables)
```html
<input type="text" name="field" placeholder="Value" required
       class="text-xs rounded border border-gray-600 bg-gray-800 px-2 py-1.5">
```

### Checkbox
```html
<label class="flex items-center gap-2 text-sm text-gray-300">
    <input type="checkbox" name="field" value="1"> Label text
</label>
```

### Filter bar
```html
<form method="GET" class="flex flex-wrap gap-3 items-center">
    <select name="filter"
            class="px-3 py-2.5 border border-gray-600 bg-gray-800 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">All</option>
    </select>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search..."
           class="px-3 py-2.5 border border-gray-600 bg-gray-800 text-white placeholder-gray-500 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-green-500">
    <button type="submit"
            class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
        Filter
    </button>
    <a href="/current-page"
       class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-700/50">
        Clear
    </a>
</form>
```

---

## ALERTS / NOTICES

### Pattern
```html
<div class="bg-{color}-900/20 border border-{color}-800 text-{color}-300 text-sm px-4 py-3 rounded-lg">
    Alert message text.
</div>
```

### Alert variants

| Type | Classes |
|------|---------|
| Warning | `bg-amber-900/20 border-amber-800 text-amber-300` |
| Error | `bg-red-900/20 border-red-800 text-red-300` |
| Success | `bg-green-900/20 border-green-800 text-green-300` |
| Info | `bg-blue-900/20 border-blue-800 text-blue-300` |

### Flash success (from session)
```html
@if(session('success'))
<div class="rounded-lg bg-green-900/20 border border-green-800 px-4 py-3 text-sm text-green-300 flex items-center gap-2">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
</div>
@endif
```

---

## MODALS

### Standard modal
```html
<div x-show="open"
     class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
     x-cloak>
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-700">
        <h3 class="text-base font-semibold text-white">Modal Title</h3>
        <p class="text-sm text-gray-400 mt-1">Description text.</p>

        <form class="mt-4 space-y-4">
            {{-- Form fields --}}

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                    Confirm
                </button>
                <button type="button" @click="open = false"
                        class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
```

### Small confirm modal
```html
<div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl border border-gray-700">
    <h3 class="text-base font-semibold text-white">Confirm Action</h3>
    <p class="text-sm text-gray-400 mt-2">Are you sure?</p>
    <p class="text-xs text-amber-400 mt-2">This action cannot be undone.</p>
    <div class="flex gap-3 mt-6">
        <button class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
            Confirm
        </button>
        <button class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
            Cancel
        </button>
    </div>
</div>
```

---

## TYPOGRAPHY

| Element | Classes |
|---------|---------|
| Page title (h1) | `text-2xl font-bold text-white` |
| Page subtitle | `text-sm text-gray-400 mt-1` |
| Section title (h2) | `font-semibold text-white` |
| Card section title (h3) | `text-sm font-semibold text-gray-300` |
| Body text | `text-sm text-gray-300` |
| Label text | `text-xs text-gray-400` |
| Stat label | `text-xs text-gray-400 uppercase tracking-wide` |
| Stat number (normal) | `text-3xl font-bold text-white` |
| Stat number (contextual) | `text-2xl font-semibold text-{color}` |
| Code / monospace | `font-mono text-xs text-gray-300` |
| Link | `text-yellow-400 hover:underline` |
| Danger link | `text-red-400 hover:underline` |
| Timestamp | `text-xs text-gray-400` |
| Helper / footnote | `text-xs text-gray-400` |
| Empty state text | `text-gray-400 text-sm` |

---

## GRID PATTERNS

### KPI card grid (4 cols)
```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
```

### KPI card grid (3 cols)
```html
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
```

### Integration health grid
```html
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
```

### Form grid (2 cols)
```html
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
```

### Info panel grid (3 cols with dividers)
```html
<dl class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-700">
    <div class="px-5 py-4">
        <dt class="text-xs text-gray-400">Label</dt>
        <dd class="mt-1 text-sm font-medium text-gray-200">Value</dd>
    </div>
</dl>
```

---

## STATUS CARD PATTERNS (conditional borders)

### Health indicator card
```php
@php
    $cardClass = match($status) {
        'OK'    => 'border-green-800 bg-green-900/20',
        'WARN'  => 'border-yellow-800 bg-yellow-900/20',
        'ERROR' => 'border-red-800 bg-red-900/20',
        default => 'border-gray-700 bg-gray-800',
    };
    $badgeClass = match($status) {
        'OK'    => 'bg-green-900/30 text-green-400',
        'WARN'  => 'bg-yellow-900/30 text-yellow-400',
        'ERROR' => 'bg-red-900/30 text-red-400',
        default => 'bg-gray-700 text-gray-400',
    };
@endphp
<div class="rounded-xl border p-4 {{ $cardClass }}">
    <p class="text-xs font-medium text-gray-300">{{ $name }}</p>
    <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
        {{ $status }}
    </span>
</div>
```

---

## CONVENTIONS

1. **No `dark:` prefix needed** — dark mode is forced globally. Use dark values directly.
2. **Card radius:** Always `rounded-xl` for cards, `rounded-lg` for inputs/badges, `rounded-2xl` for modals.
3. **Padding:** Cards use `p-5` or `p-6`. Tables use `px-5 py-3` cells. Forms use `p-5 space-y-4`.
4. **Spacing between sections:** `space-y-6` on page wrapper.
5. **Responsive columns:** Hide on mobile with `hidden md:table-cell` or `hidden lg:table-cell`.
6. **Table min-width:** Use `min-w-[700px]` to `min-w-[900px]` for horizontal scroll on mobile.
7. **Timezone:** All displayed times use `->timezone('Africa/Nairobi')->format('d M, H:i')`.
8. **Currency:** Always use `{{ $currency['symbol'] }} {{ number_format($value, $currency['decimal_places']) }}`.
9. **Alpine.js:** Used for modals (`x-show`, `x-cloak`), tab states, and sidebar state.
10. **No emojis in production views** — use SVG icons or text indicators.
