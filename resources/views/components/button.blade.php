<button {{ $attributes->merge(['type' => 'submit', 'class' => 'hss-btn hss-btn-primary']) }}>
    {{ $slot }}
</button>
