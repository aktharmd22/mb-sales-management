<x-app-layout>
    <x-page-header title="My profile" subtitle="Manage your account and password." />

    <div class="max-w-2xl space-y-5">
        <div class="card p-5 sm:p-7">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="card p-5 sm:p-7">
            <livewire:profile.update-password-form />
        </div>

        <div class="card p-5 sm:p-7">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>
