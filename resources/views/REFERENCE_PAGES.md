# DawaMtaani Reference Pages
**Version:** v3.6
**Purpose:** Golden reference for all page styling across the project.
**These 6 files are the canonical source of truth for every UI pattern.**
**Last updated:** March 2026

---

## FILE 1 — App Layout (layouts/app.blade.php)

Key structural classes:

```html
<html class="h-full dark">
<body class="bg-gray-900 text-gray-100 min-h-screen">

{{-- Page load spinner --}}
<div class="fixed ... bg-gray-900">
    <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-yellow-400 border-t-transparent"></div>
</div>

{{-- Mobile overlay --}}
<div class="fixed inset-0 bg-black/60 z-[99998] xl:hidden"></div>

{{-- Main wrapper --}}
<div class="min-h-screen xl:flex">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 ease-in-out"
         :class="{
            'xl:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
            'xl:ml-[76px]':  !$store.sidebar.isExpanded && !$store.sidebar.isHovered
         }">
        @include('layouts.app-header')
        <div class="px-4 md:px-6 pt-4 pb-8">
            {{-- Flash messages --}}
            {{-- @yield('content') --}}
        </div>
    </div>
</div>
```

Flash message patterns:
```html
{{-- Success --}}
<div class="rounded-lg bg-green-900/20 border border-green-800 px-4 py-3 text-sm text-green-300 flex items-center gap-2 mb-4">

{{-- Error --}}
<div class="rounded-lg bg-red-900/20 border border-red-800 px-4 py-3 text-sm text-red-300 flex items-center gap-2 mb-4">
```

---

## FILE 2 — Header (layouts/app-header.blade.php)

```html
<header class="sticky top-0 flex w-full bg-gray-900 border-b border-gray-800 z-[9999]">

{{-- Sidebar toggle (desktop) --}}
<button class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-400 border border-gray-700 rounded-lg hover:bg-gray-800">

{{-- Sidebar toggle (mobile) --}}
<button class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-400 rounded-lg">

{{-- Search bar --}}
<div class="hidden xl:block flex-1 max-w-md">
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" ...>
        </span>
        <input type="text" placeholder="Search..."
               class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-9 pr-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors"/>
    </div>
</div>

{{-- Theme toggle --}}
<button class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-gray-800 border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition-colors">

{{-- User avatar + dropdown --}}
<button class="flex items-center gap-2 text-gray-300">
    <div class="h-9 w-9 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
        <span class="text-sm font-bold text-gray-900">U</span>
    </div>
</button>

{{-- Dropdown panel --}}
<div class="absolute right-0 mt-3 w-56 rounded-2xl border border-gray-700 bg-gray-800 p-3 shadow-lg z-50">
    <div class="pb-3 border-b border-gray-700">
        <span class="block font-medium text-white text-sm">Name</span>
        <span class="block text-xs text-gray-400 mt-0.5">email</span>
    </div>
    <button class="flex items-center w-full gap-3 px-3 py-2 font-medium text-red-400 rounded-lg text-sm hover:bg-gray-700">
        Sign out
    </button>
</div>
```

---

## FILE 3 — Sidebar (layouts/sidebar.blade.php)

```html
<aside class="fixed flex flex-col top-0 px-4 left-0 bg-gray-950 h-screen transition-all duration-300 ease-in-out z-[99999] border-r border-gray-800 w-[280px] -translate-x-full xl:translate-x-0"
       :class="{
           'translate-x-0': $store.sidebar.isMobileOpen,
           'xl:w-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
           'xl:w-[76px]':  !$store.sidebar.isExpanded && !$store.sidebar.isHovered
       }">

{{-- Logo --}}
<div class="flex-shrink-0 pt-7 pb-6 flex border-b border-gray-800">
    <a href="{{ $logoHref }}" class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-lg bg-yellow-400 flex items-center justify-center flex-shrink-0">
            <span class="text-gray-900 font-bold text-sm tracking-tight">DM</span>
        </div>
        <div x-show="expanded || hovered || mobileOpen">
            <span class="text-white font-bold text-base tracking-tight">
                Dawa<span class="text-yellow-400">Mtaani</span>
            </span>
            <p class="text-gray-500 text-[10px] tracking-widest uppercase mt-0.5">Quality, Affordably</p>
        </div>
    </a>
</div>

{{-- Nav (scrollable) --}}
<div class="flex flex-col overflow-y-auto scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-gray-700 flex-1 py-4">

{{-- Footer --}}
<div class="flex-shrink-0 border-t border-gray-800">
    {{-- User pill --}}
    <div class="flex items-center gap-3 px-3 pt-3 pb-2">
        <div class="h-8 w-8 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
            <span class="text-gray-900 font-bold text-xs">U</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-white text-sm font-medium truncate">Name</p>
            <p class="text-gray-500 text-xs truncate">Role</p>
        </div>
    </div>
    {{-- Sign Out --}}
    <button class="w-full flex items-center gap-3 px-3 py-2 text-gray-400 hover:text-red-400 hover:bg-gray-800/50 transition-colors text-sm">
</div>
```

---

## FILE 4 — Diagnostics Page (tech/diagnostics.blade.php)

This is the primary reference for data-heavy dashboard pages.

### Page wrapper
```html
<div class="space-y-6">
```

### Page header with tier badge
```html
<div class="flex items-center gap-3">
    <div>
        <h1 class="text-2xl font-bold text-white">System Diagnostics</h1>
        <p class="text-sm text-gray-400 mt-1">Integration health and read-only query console</p>
    </div>
    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-red-800">
        Tier 0
    </span>
</div>
```

### Status indicator cards (conditional coloring)
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

### Stat cards with conditional borders
```html
<div class="bg-gray-800 rounded-xl border border-{{ $value > 0 ? 'red-800' : 'gray-700' }} p-5">
    <p class="text-xs text-gray-400">Label</p>
    <p class="text-3xl font-bold text-{{ $value > 0 ? 'red-400' : 'white' }} mt-1">
        {{ number_format($value) }}
    </p>
</div>
```

### Card with tinted header
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
        <h3 class="text-sm font-semibold text-gray-300">Section Title</h3>
    </div>
    <div class="p-5 space-y-4">
        {{-- content --}}
    </div>
</div>
```

### Warning alert
```html
<div class="bg-amber-900/20 border border-amber-800 text-amber-300 text-xs px-3 py-2 rounded-lg">
    Warning message with <a href="#" class="underline font-medium">link</a>.
</div>
```

### Error alert
```html
<div class="bg-red-900/20 border border-red-800 text-red-300 text-sm px-4 py-3 rounded-lg">
    Error message
</div>
```

### Terminal/code textarea
```html
<textarea class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 rounded-lg text-sm text-green-400 font-mono placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
```

### Query results table (compact)
```html
<table class="w-full text-xs border border-gray-700">
    <thead class="bg-gray-900 text-gray-400">
        <tr>
            <th class="px-3 py-2 text-left border-b border-gray-700 font-medium">Col</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-700">
        <tr class="hover:bg-gray-700/50">
            <td class="px-3 py-2 font-mono text-gray-300 whitespace-nowrap">Value</td>
        </tr>
    </tbody>
</table>
```

---

## FILE 5 — Job Monitor (tech/jobs.blade.php)

This is the primary reference for table-heavy monitoring pages.

### Full data table in card
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-700">
        <h3 class="text-sm font-semibold text-gray-300">Table Title</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Column</th>
                    <th class="px-5 py-3 text-left hidden md:table-cell">Responsive</th>
                    <th class="px-5 py-3 text-right">Number</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <tr class="hover:bg-gray-700/50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-300">mono text</td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">secondary</td>
                    <td class="px-5 py-3 text-right text-xs text-gray-400">123ms</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Status badges (PHP match)
```php
$statusBadge = match($status) {
    'COMPLETED' => 'bg-green-900/30 text-green-400',
    'FAILED'    => 'bg-red-900/30 text-red-400',
    'STARTED'   => 'bg-blue-900/30 text-blue-400',
    default     => 'bg-gray-700 text-gray-400',
};
```

### Health badges
```php
$healthBadge = match($health) {
    'CRITICAL' => 'bg-red-900/30 text-red-400',
    'WARNING'  => 'bg-amber-900/30 text-amber-400',
    default    => 'bg-green-900/30 text-green-400',
};
```

### Badge HTML
```html
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
    {{ $label }}
</span>
```

### Danger section card
```html
<div class="bg-gray-800 rounded-xl border border-red-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-red-800 bg-red-900/20">
        <h3 class="text-sm font-semibold text-red-400">Failed Jobs (last 20)</h3>
    </div>
    {{-- table content --}}
</div>
```

### Action buttons in table cells
```html
{{-- Ghost/outline button --}}
<button class="px-3 py-1.5 border border-gray-600 text-gray-400 rounded-lg text-xs hover:bg-gray-700/50">
    Details
</button>

{{-- Warning action button --}}
<button class="px-3 py-1.5 bg-amber-900/30 text-amber-400 border border-amber-800 rounded-lg text-xs hover:bg-amber-900/50">
    Retry
</button>
```

### Modal
```html
<div x-show="open"
     class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
     x-cloak>
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-xl border border-gray-700">
        <h3 class="text-base font-semibold text-white">Modal Title</h3>
        <pre class="mt-3 text-xs font-mono bg-gray-900 border border-gray-700 rounded-lg p-4 overflow-auto max-h-64 text-red-400"
             x-text="content"></pre>
        <button @click="open = false"
                class="mt-4 w-full px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
            Close
        </button>
    </div>
</div>
```

---

## FILE 6 — Write Operations (tech/write.blade.php)

This is the primary reference for form-heavy pages.

### Form in card with tinted header
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
        <h3 class="text-sm font-semibold text-gray-300">Form Title</h3>
    </div>
    <form class="p-5 space-y-4">
        @csrf
        {{-- fields --}}
    </form>
</div>
```

### Form label
```html
<label class="block text-xs font-medium text-gray-400 mb-1">Label</label>
```

### Select input
```html
<select class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
```

### Text input
```html
<input type="text"
       placeholder="placeholder..."
       class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
```

### Textarea
```html
<textarea rows="4" required
          placeholder="Description..."
          class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400"></textarea>
```

### Danger submit button
```html
<button type="submit"
        class="px-6 py-2.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
    Submit Request
</button>
```

### Clickable stat card (link)
```html
<a href="/target"
   class="bg-gray-800 rounded-xl border border-{{ $value > 0 ? 'amber-800' : 'gray-700' }} p-5 block hover:shadow-md hover:border-gray-600 transition-all">
    <p class="text-xs text-gray-400">Label</p>
    <p class="text-3xl font-bold text-{{ $value > 0 ? 'amber-400' : 'white' }} mt-1">{{ $value }}</p>
    <p class="text-xs text-green-400 mt-2 font-medium">View details →</p>
</a>
```

---

## FILE 7 — Incidents (tech/incidents.blade.php)

### Page header with action button
```html
<div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Page Title</h1>
            <p class="text-sm text-gray-400 mt-1">Description</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-red-800">
            Tier 0
        </span>
    </div>
    <button class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
        + Create Item
    </button>
</div>
```

### Severity badges
```php
$badge = match($severity) {
    'CRITICAL' => 'bg-red-900/30 text-red-400',
    'HIGH'     => 'bg-orange-900/30 text-orange-400',
    'MEDIUM'   => 'bg-amber-900/30 text-amber-400',
    'LOW'      => 'bg-gray-700 text-gray-400',
    default    => 'bg-gray-700 text-gray-400',
};
```

### Empty state
```html
<div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
    <p class="text-gray-400 text-sm">No items found.</p>
</div>
```

### Form modal
```html
<div x-show="open"
     class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
     x-cloak>
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-700">
        <h3 class="text-base font-semibold text-white">Modal Title</h3>
        <form class="mt-4 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Label</label>
                <select class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Label</label>
                <input type="text" required
                       class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                    Submit
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

### Pagination
```html
<div>{{ $items->links() }}</div>
```
Uses the published dark-themed pagination at `vendor/pagination/tailwind.blade.php`.

---

## COMPLETE COLOR REFERENCE

### Backgrounds
| Use | Class |
|-----|-------|
| Page | `bg-gray-900` |
| Sidebar | `bg-gray-950` |
| Header | `bg-gray-900` |
| Card | `bg-gray-800` |
| Card header (tinted) | `bg-gray-900/50` |
| Input | `bg-gray-900` |
| Input (in filter bar) | `bg-gray-800` |
| Modal overlay | `bg-black/60` |
| Success alert | `bg-green-900/20` |
| Warning alert | `bg-amber-900/20` |
| Error alert | `bg-red-900/20` |
| Info alert | `bg-blue-900/20` |
| OK card | `bg-green-900/20 border-green-800` |
| Warn card | `bg-yellow-900/20 border-yellow-800` |
| Error card | `bg-red-900/20 border-red-800` |

### Text
| Use | Class |
|-----|-------|
| Heading | `text-white` |
| Body | `text-gray-300` |
| Secondary | `text-gray-400` |
| Disabled | `text-gray-500` |
| Placeholder | `placeholder-gray-500` |
| Brand accent | `text-yellow-400` |
| Success | `text-green-400` |
| Warning | `text-amber-400` |
| Danger | `text-red-400` |
| Info | `text-blue-400` |
| Link | `text-yellow-400 hover:underline` |
| Sign out | `text-red-400` |

### Borders
| Use | Class |
|-----|-------|
| Card | `border-gray-700` |
| Input | `border-gray-600` |
| Section divider | `border-gray-700` |
| Sidebar border | `border-gray-800` |
| Alert border | `border-{color}-800` |

### Buttons
| Type | Classes |
|------|---------|
| Primary (brand) | `bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-medium` |
| Operational (green) | `bg-green-700 hover:bg-green-800 text-white` |
| Danger | `bg-red-600 hover:bg-red-700 text-white` |
| Ghost/Cancel | `border border-gray-600 text-gray-400 hover:bg-gray-700/50` |
| Warning action | `bg-amber-900/30 text-amber-400 border-amber-800 hover:bg-amber-900/50` |
| Filter | `bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-medium` |

### Focus rings
| Use | Class |
|-----|-------|
| All inputs | `focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400` |

### Badges
| Status | Classes |
|--------|---------|
| Success/OK | `bg-green-900/30 text-green-400` |
| Warning | `bg-amber-900/30 text-amber-400` |
| Danger/Error | `bg-red-900/30 text-red-400` |
| Info/Active | `bg-blue-900/30 text-blue-400` |
| Neutral/Default | `bg-gray-700 text-gray-400` |
| Critical | `bg-red-900/30 text-red-400` |
| High | `bg-orange-900/30 text-orange-400` |
| Medium | `bg-amber-900/30 text-amber-400` |
| Low | `bg-gray-700 text-gray-400` |
| Tier 0 | `bg-red-900/30 text-red-400 border border-red-800` |
| Tier 1 | `bg-yellow-900/30 text-yellow-400 border border-yellow-800` |

---

## RULES

1. **Never use** `bg-white`, `bg-gray-50`, `text-gray-900`, `border-gray-200`, `bg-blue-600`, or any `dark:` prefix.
2. **Dark mode is forced globally** — use dark values directly.
3. **Card radius:** `rounded-xl` for cards, `rounded-lg` for inputs/badges, `rounded-2xl` for modals.
4. **Padding:** Cards `p-5`, Tables `px-5 py-3`, Forms `p-5 space-y-4`.
5. **Spacing:** `space-y-6` on page wrapper.
6. **Responsive columns:** Hide with `hidden md:table-cell`.
7. **Table min-width:** `min-w-[700px]` to `min-w-[900px]`.
8. **Timezone:** `->timezone('Africa/Nairobi')->format('d M, H:i')`.
9. **Currency:** `{{ $currency['symbol'] }} {{ number_format($value, $currency['decimal_places']) }}`.
10. **Alpine.js:** Modals use `x-show`, `x-cloak`. Sidebar uses `$store.sidebar`.
