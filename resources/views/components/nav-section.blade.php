@props(['label'])
<li x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
    class="px-3 pt-5 pb-1 text-[10px] font-semibold text-gray-300 uppercase tracking-widest">
    {{ $label }}
</li>
