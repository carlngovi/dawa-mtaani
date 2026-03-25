@props(['href', 'label', 'icon'])
<li>
    <a href="{{ $href }}"
       class="menu-item group"
       :class="[
           act('{{ ltrim($href, '/') }}') ? 'menu-item-active' : 'menu-item-inactive',
           (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
       ]">
        <span class="flex-shrink-0"
              :class="act('{{ ltrim($href, '/') }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
            {!! $icon !!}
        </span>
        <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
              class="truncate">{{ $label }}</span>
    </a>
</li>
