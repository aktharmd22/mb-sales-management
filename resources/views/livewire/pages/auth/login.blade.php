<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Admins can reach any page, so honour the intended URL. Salespeople
        // must never be sent to an admin-only "intended" URL (that 403s), so
        // they always land on their dashboard.
        if (auth()->user()?->isAdmin()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            Session::forget('url.intended');
            $this->redirect(route('dashboard'), navigate: true);
        }
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Welcome back</h1>
    <p class="mt-1 text-sm text-slate-500">Sign in to your Malayznbeat account.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="login" class="mt-6 space-y-5">
        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 grid place-items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                </span>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                       placeholder="you@malayznbeat.com"
                       class="w-full rounded-xl border-slate-200 bg-slate-50 pl-11 pr-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/15 transition" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-medium text-blue-600 hover:text-blue-700">Forgot password?</a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <span class="absolute inset-y-0 left-3.5 grid place-items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                </span>
                <input wire:model="form.password" id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl border-slate-200 bg-slate-50 pl-11 pr-11 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/15 transition" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 grid place-items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L9.88 9.88" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
        </div>

        {{-- Remember --}}
        <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input wire:model="form.remember" type="checkbox" class="rounded-md border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-slate-600">Remember me</span>
        </label>

        {{-- Submit --}}
        <button type="submit" wire:loading.attr="disabled" wire:target="login"
                class="w-full flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 h-12 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 transition active:scale-[0.99] disabled:opacity-90 disabled:cursor-wait">
            <svg wire:loading wire:target="login" class="w-[18px] h-[18px] animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>
</div>
