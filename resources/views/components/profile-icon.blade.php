@props(['type' => 'user'])

@if ($type === 'user')
    <svg viewBox="0 0 24 24"><path d="M12 12C14.2 12 16 10.2 16 8C16 5.8 14.2 4 12 4C9.8 4 8 5.8 8 8C8 10.2 9.8 12 12 12Z"/><path d="M4 20C4.8 16.5 7.8 14.5 12 14.5C16.2 14.5 19.2 16.5 20 20H4Z"/></svg>
@elseif ($type === 'language')
    <svg viewBox="0 0 24 24"><path d="M4 5H14"/><path d="M9 5V7"/><path d="M6 19L11 9"/><path d="M4 10H14"/><path d="M13 19L17 9L21 19"/><path d="M14.5 16H19.5"/></svg>
@elseif ($type === 'donation')
    <svg viewBox="0 0 24 24"><path d="M12 3C7.6 3 4 6.6 4 11C4 15.4 7.6 19 12 19C16.4 19 20 15.4 20 11C20 6.6 16.4 3 12 3Z"/><path d="M12 7V15"/><path d="M9.5 9C9.5 7.9 10.6 7 12 7C13.4 7 14.5 7.9 14.5 9C14.5 10.1 13.4 10.7 12 11C10.6 11.3 9.5 11.9 9.5 13C9.5 14.1 10.6 15 12 15C13.4 15 14.5 14.1 14.5 13"/><path d="M5 21H19"/></svg>
@elseif ($type === 'handshake')
    <svg viewBox="0 0 24 24"><path d="M7 12L10 15C10.6 15.6 11.4 15.6 12 15L18 9"/><path d="M3 12L7 8L11 12"/><path d="M21 12L17 8L13 12"/><path d="M8 16L10 18"/><path d="M12 16L14 18"/></svg>
@elseif ($type === 'info')
    <svg viewBox="0 0 24 24"><path d="M12 10V17"/><path d="M12 7H12.01"/></svg>
@elseif ($type === 'terms')
    <svg viewBox="0 0 24 24"><path d="M7 3H17V21H7V3Z"/><path d="M9 7H15"/><path d="M9 11H15"/><path d="M9 15H13"/></svg>
@elseif ($type === 'help')
    <svg viewBox="0 0 24 24"><path d="M5 20V12C5 8.1 8.1 5 12 5C15.9 5 19 8.1 19 12V20"/><path d="M5 13H8V20H5V13Z"/><path d="M16 13H19V20H16V13Z"/></svg>
@elseif ($type === 'logout')
    <svg viewBox="0 0 24 24"><path d="M10 4H5V20H10"/><path d="M14 8L18 12L14 16"/><path d="M18 12H9"/></svg>
@endif