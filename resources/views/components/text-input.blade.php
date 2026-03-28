@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-600 focus:border-gray-700 focus:ring-yellow-400 rounded-md shadow-sm']) }}>
