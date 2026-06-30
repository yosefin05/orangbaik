@switch($type)

    {{-- User --}}
    @case('user')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/>
        </svg>
        @break

    {{-- Bahasa --}}
    @case('language')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18"/>
            <path d="M12 3a15 15 0 0 1 0 18"/>
            <path d="M12 3a15 15 0 0 0 0 18"/>
        </svg>
        @break

    {{-- Donasi --}}
    @case('donation')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 21s-7-4.5-9-9a5.5 5.5 0 0 1 9-6 5.5 5.5 0 0 1 9 6c-2 4.5-9 9-9 9z"/>
        </svg>
        @break

    {{-- Penggalang Dana --}}
    @case('handshake')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 12l3 3a2 2 0 0 0 3 0l5-5"/>
            <path d="M2 10l4-4 5 5-4 4z"/>
            <path d="M13 11l5-5 4 4-5 5z"/>
        </svg>
        @break

    {{-- Tentang --}}
    @case('info')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4"/>
            <circle cx="12" cy="8" r="1"/>
        </svg>
        @break

    {{-- S&K --}}
    @case('terms')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2h9l5 5v15H6z"/>
            <path d="M15 2v5h5"/>
            <path d="M9 13h6"/>
            <path d="M9 17h4"/>
        </svg>
        @break

    {{-- Bantuan --}}
    @case('help')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9.5 9a2.5 2.5 0 1 1 5 0c0 2-2.5 2.5-2.5 4"/>
            <circle cx="12" cy="17" r="1"/>
        </svg>
        @break

    {{-- Logout --}}
    @case('logout')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <path d="M16 17l5-5-5-5"/>
            <path d="M21 12H9"/>
        </svg>
        @break

@endswitch

<style>
    .menu-icon{
    width: 48px;
    height: 48px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#3365AF;
    background:#EEF4FF;
    border-radius:12px;
}

.menu-icon svg{
    width:24px;
    height:24px;
}
</style>