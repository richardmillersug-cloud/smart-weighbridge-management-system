<button
    type="button"
    class="theme-toggle"
    x-data="{ light: document.documentElement.classList.contains('light') }"
    @click="
        light = ! light;
        document.documentElement.classList.toggle('light', light);
        localStorage.setItem('woms-theme', light ? 'light' : 'dark');
        window.dispatchEvent(new CustomEvent('woms-theme-changed', { detail: { light } }));
    "
    :aria-label="light ? 'Switch to dark theme' : 'Switch to light theme'"
    :title="light ? 'Dark theme' : 'Light theme'"
>
    <svg x-show="! light" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <circle cx="12" cy="12" r="4"/>
        <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
    </svg>
    <svg x-show="light" x-cloak class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/>
    </svg>
    <span class="hidden sm:inline" x-text="light ? 'Dark' : 'Light'"></span>
</button>
