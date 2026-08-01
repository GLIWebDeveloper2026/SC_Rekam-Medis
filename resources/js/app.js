import Alpine from '@alpinejs/csp';
import {
    Activity,
    ArrowRight,
    BadgePlus,
    CalendarDays,
    ChartNoAxesColumnIncreasing,
    ClipboardCheck,
    Clock3,
    createIcons,
    FileLock2,
    HeartPulse,
    History,
    LayoutDashboard,
    ListOrdered,
    LogOut,
    MapPin,
    MessageCircle,
    Menu,
    Phone,
    Pill,
    Quote,
    ShieldCheck,
    Stethoscope,
    UserRound,
    UsersRound,
    X,
} from 'lucide';

const createIdempotencyKey = () => {
    const cryptoApi = window.crypto || window.msCrypto;

    if (cryptoApi && typeof cryptoApi.randomUUID === 'function') {
        return cryptoApi.randomUUID();
    }

    const bytes = new Uint8Array(16);

    if (cryptoApi && typeof cryptoApi.getRandomValues === 'function') {
        cryptoApi.getRandomValues(bytes);
    } else {
        for (let index = 0; index < bytes.length; index += 1) {
            bytes[index] = Math.floor(Math.random() * 256);
        }
    }

    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;

    const hexadecimal = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');

    return `${hexadecimal.slice(0, 8)}-${hexadecimal.slice(8, 12)}-${hexadecimal.slice(12, 16)}-${hexadecimal.slice(16, 20)}-${hexadecimal.slice(20)}`;
};

Alpine.data('navigation', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('clinicLanding', () => ({
    menuOpen: false,
    toggleMenu() {
        this.menuOpen = !this.menuOpen;
    },
    closeMenu() {
        this.menuOpen = false;
    },
}));

Alpine.data('patientNavigation', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('clinicChat', () => ({
    open: false,
    input: '',
    busy: false,
    error: null,
    messages: [],
    toolResults: [],
    openPanel() {
        this.open = true;
        this.$nextTick(() => this.$refs.chatInput?.focus());
    },
    closePanel() {
        this.open = false;
    },
    async send() {
        const content = this.input.trim();

        if (!content || this.busy) {
            return;
        }

        this.messages.push({ role: 'user', content });
        this.input = '';
        this.busy = true;
        this.error = null;
        this.toolResults = [];

        try {
            const response = await fetch('/assistant/messages', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    idempotency_key: createIdempotencyKey(),
                    messages: this.messages.slice(-12),
                    current_page: window.location.pathname,
                }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Asisten tidak dapat memproses permintaan.');
            }

            this.messages.push({ role: 'assistant', content: data.message });
            this.toolResults = data.tool_results || [];
        } catch (error) {
            this.error = error.message;
        } finally {
            this.busy = false;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();

createIcons({
    icons: {
        Activity, ArrowRight, BadgePlus, CalendarDays, ChartNoAxesColumnIncreasing, ClipboardCheck,
        Clock3, FileLock2, HeartPulse, History, LayoutDashboard, ListOrdered, LogOut, MapPin,
        Menu, MessageCircle, Phone, Pill, Quote, ShieldCheck, Stethoscope, UserRound, UsersRound, X,
    },
    attrs: {
        'aria-hidden': 'true',
        'stroke-width': 1.8,
    },
});
