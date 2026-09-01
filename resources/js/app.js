// Livewire 4 ships with Alpine bundled inside its ESM build.
// Import both from the same module so wire:* and x-data share one Alpine instance.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Livewire = Livewire;
window.Alpine = Alpine;

Alpine.start();
Livewire.start();