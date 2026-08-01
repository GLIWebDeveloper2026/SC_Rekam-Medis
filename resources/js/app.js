import Alpine from '@alpinejs/csp';
import {
    Activity,
    ArrowRight,
    BadgePlus,
    CalendarDays,
    ChartNoAxesColumnIncreasing,
    Clock3,
    createIcons,
    FileLock2,
    HeartPulse,
    LayoutDashboard,
    ListOrdered,
    LogOut,
    MapPin,
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
    modalOpen: false,
    toggleMenu() {
        this.menuOpen = !this.menuOpen;
    },
    closeMenu() {
        this.menuOpen = false;
    },
    openModal() {
        this.modalOpen = true;
    },
    openAppointment() {
        this.menuOpen = false;
        this.modalOpen = true;
    },
    closeModal() {
        this.modalOpen = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();

createIcons({
    icons: {
        Activity, ArrowRight, BadgePlus, CalendarDays, ChartNoAxesColumnIncreasing, Clock3,
        FileLock2, HeartPulse, LayoutDashboard, ListOrdered, LogOut, MapPin, Menu,
        Phone, Pill, Quote, ShieldCheck, Stethoscope, UserRound, UsersRound, X,
    },
    attrs: {
        'aria-hidden': 'true',
        'stroke-width': 1.8,
    },
});
