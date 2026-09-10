// Livewire (JS + Alpine + styles) is auto-injected by Livewire itself on every
// page that renders a Livewire component; guest-only pages get it explicitly
// via @livewireScripts in layouts/guest.blade.php.
//
// Do NOT bundle livewire.esm / Alpine here again: the auto-injected livewire.js
// plus this copy used to spawn two Livewire/Alpine instances (classic script
// executes first and claims the DOM, the ESM copy overwrites window.Livewire),
// which broke cross-component browser events — Livewire.dispatch('cart-updated')
// from the product card never reached the cart-dropdown component.
//
// This file remains the Vite JS entrypoint (see layouts/app.blade.php @vite).
