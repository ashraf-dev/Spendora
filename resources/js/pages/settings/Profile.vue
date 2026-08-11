<script setup lang="ts">
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Camera, LogOut, ShieldCheck } from '@lucide/vue';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const nameParts = user.value.name.trim().split(/\s+/);
const firstName = ref(nameParts.shift() ?? '');
const lastName = ref(nameParts.join(' '));

const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

const initials = computed(() =>
    user.value.name
        .split(/\s+/)
        .slice(0, 2)
        .map((name) => name.charAt(0).toUpperCase())
        .join(''),
);

function updateProfile(): void {
    profileForm.name =
        `${firstName.value.trim()} ${lastName.value.trim()}`.trim();
    profileForm.submit(ProfileController.update(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <Head title="Profile settings" />

        <main
            class="min-h-[calc(100vh-4rem)] bg-[#f4fbf4] px-5 py-8 text-[#161d19] md:px-10 md:py-10 dark:bg-[#161d19] dark:text-[#ebf3eb]"
        >
            <div class="mx-auto w-full max-w-7xl">
                <header class="mb-8">
                    <h1
                        class="text-3xl font-bold tracking-[-0.02em] sm:text-4xl lg:text-5xl"
                    >
                        Profile Settings
                    </h1>
                    <p
                        class="mt-2 text-base text-[#565e74] dark:text-[#bbcabf]"
                    >
                        Manage your personal details, account security, and
                        preferences.
                    </p>
                </header>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <aside class="flex flex-col gap-6 lg:col-span-1">
                        <section
                            class="flex flex-col items-center rounded-xl border border-[#dde4dd] bg-white p-6 text-center shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] dark:border-[#3c4a42] dark:bg-[#2b322d]"
                        >
                            <Form
                                v-bind="ProfileController.uploadAvatar.form()"
                                force-form-data
                                class="group relative mb-4 size-32"
                                v-slot="{ errors, processing, submit }"
                            >
                                <div
                                    class="flex size-32 items-center justify-center overflow-hidden rounded-full border-4 border-[#e8f0e9] bg-[#eef6ee] text-3xl font-bold text-[#006c49] dark:border-[#3c4a42] dark:bg-[#161d19] dark:text-[#4edea3]"
                                >
                                    <img
                                        v-if="user.avatar"
                                        :src="user.avatar"
                                        :alt="user.name"
                                        class="size-full object-cover"
                                    />
                                    <span v-else>{{ initials }}</span>
                                </div>

                                <label
                                    for="profile-avatar"
                                    class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-[#0f172a]/0 text-white opacity-0 transition-all duration-200 group-focus-within:bg-[#0f172a]/55 group-focus-within:opacity-100 group-hover:bg-[#0f172a]/55 group-hover:opacity-100"
                                >
                                    <Spinner v-if="processing" class="size-6" />
                                    <Camera v-else class="size-6" />
                                    <span class="sr-only"
                                        >Change profile photo</span
                                    >
                                </label>
                                <input
                                    id="profile-avatar"
                                    class="sr-only"
                                    type="file"
                                    name="avatar"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    data-test="upload-avatar-button"
                                    @change="() => submit()"
                                />
                                <InputError
                                    :message="errors.avatar"
                                    class="absolute top-full left-1/2 mt-2 w-64 -translate-x-1/2"
                                />
                            </Form>

                            <h2 class="text-2xl font-semibold tracking-tight">
                                {{ user.name }}
                            </h2>
                            <p
                                class="mt-1 text-sm break-all text-[#565e74] dark:text-[#bbcabf]"
                            >
                                {{ user.email }}
                            </p>
                            <span
                                class="mt-4 inline-flex rounded-full bg-[#dae2fd] px-3 py-1 text-xs font-semibold text-[#3f465c] dark:bg-[#3f465c] dark:text-[#dae2fd]"
                            >
                                Spendora Member
                            </span>

                            <Form
                                v-if="user.avatar"
                                v-bind="ProfileController.deleteAvatar.form()"
                                class="mt-4"
                                v-slot="{ processing }"
                            >
                                <button
                                    type="submit"
                                    class="text-xs font-semibold text-[#565e74] underline decoration-[#bbcabf] underline-offset-4 transition-colors hover:text-[#006c49] dark:text-[#bbcabf] dark:hover:text-[#4edea3]"
                                    :disabled="processing"
                                    data-test="delete-avatar-button"
                                >
                                    {{
                                        processing
                                            ? 'Removing photo...'
                                            : 'Remove photo'
                                    }}
                                </button>
                            </Form>
                        </section>

                        <section
                            class="rounded-xl border border-[#dde4dd] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] dark:border-[#3c4a42] dark:bg-[#2b322d]"
                        >
                            <h2
                                class="border-b border-[#dde4dd] pb-3 text-xl font-semibold dark:border-[#3c4a42]"
                            >
                                Account Actions
                            </h2>
                            <div class="mt-4 flex flex-col gap-3">
                                <Link
                                    :href="logout()"
                                    method="post"
                                    as="button"
                                    class="flex min-h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#dde4dd] px-4 text-sm font-semibold text-[#3c4a42] transition-colors hover:bg-[#bbcabf] dark:bg-[#3c4a42] dark:text-[#ebf3eb] dark:hover:bg-[#565e74]"
                                >
                                    <LogOut class="size-5" />
                                    Logout
                                </Link>
                                <DeleteUser />
                            </div>
                        </section>
                    </aside>

                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <section
                            class="rounded-xl border border-[#dde4dd] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] dark:border-[#3c4a42] dark:bg-[#2b322d]"
                        >
                            <h2
                                class="border-b border-[#dde4dd] pb-3 text-2xl font-semibold dark:border-[#3c4a42]"
                            >
                                Personal Information
                            </h2>

                            <form
                                class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2"
                                @submit.prevent="updateProfile"
                            >
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="first-name"
                                        class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                        >First Name</label
                                    >
                                    <input
                                        id="first-name"
                                        v-model="firstName"
                                        type="text"
                                        required
                                        autocomplete="given-name"
                                        class="min-h-12 rounded-lg border border-[#bbcabf] bg-[#f4fbf4] px-4 text-base transition outline-none focus:border-[#006c49] focus:ring-1 focus:ring-[#006c49] dark:border-[#6c7a71] dark:bg-[#161d19] dark:focus:border-[#4edea3] dark:focus:ring-[#4edea3]"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="last-name"
                                        class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                        >Last Name</label
                                    >
                                    <input
                                        id="last-name"
                                        v-model="lastName"
                                        type="text"
                                        autocomplete="family-name"
                                        class="min-h-12 rounded-lg border border-[#bbcabf] bg-[#f4fbf4] px-4 text-base transition outline-none focus:border-[#006c49] focus:ring-1 focus:ring-[#006c49] dark:border-[#6c7a71] dark:bg-[#161d19] dark:focus:border-[#4edea3] dark:focus:ring-[#4edea3]"
                                    />
                                </div>
                                <InputError
                                    v-if="profileForm.errors.name"
                                    :message="profileForm.errors.name"
                                    class="md:col-span-2"
                                />

                                <div
                                    class="flex flex-col gap-1.5 md:col-span-2"
                                >
                                    <label
                                        for="email"
                                        class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                        >Email Address</label
                                    >
                                    <input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        autocomplete="username"
                                        class="min-h-12 rounded-lg border border-[#bbcabf] bg-[#f4fbf4] px-4 text-base transition outline-none focus:border-[#006c49] focus:ring-1 focus:ring-[#006c49] dark:border-[#6c7a71] dark:bg-[#161d19] dark:focus:border-[#4edea3] dark:focus:ring-[#4edea3]"
                                    />
                                    <InputError
                                        :message="profileForm.errors.email"
                                    />
                                </div>

                                <div
                                    v-if="
                                        page.props.mustVerifyEmail &&
                                        !user.email_verified_at
                                    "
                                    class="rounded-lg bg-[#fff4dc] p-4 text-sm text-[#735c00] md:col-span-2 dark:bg-[#514619] dark:text-[#ffe178]"
                                >
                                    Your email address is unverified.
                                    <Link
                                        :href="send()"
                                        as="button"
                                        class="font-semibold underline underline-offset-4"
                                    >
                                        Re-send the verification email.
                                    </Link>
                                    <p
                                        v-if="
                                            page.props.status ===
                                            'verification-link-sent'
                                        "
                                        class="mt-2 font-semibold text-[#006c49] dark:text-[#4edea3]"
                                    >
                                        A new verification link has been sent.
                                    </p>
                                </div>

                                <div
                                    class="flex justify-end pt-2 md:col-span-2"
                                >
                                    <button
                                        type="submit"
                                        class="min-h-12 rounded-lg bg-[#006c49] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#005236] disabled:cursor-not-allowed disabled:opacity-60 dark:bg-[#10b981] dark:text-[#002113] dark:hover:bg-[#4edea3]"
                                        :disabled="profileForm.processing"
                                        data-test="update-profile-button"
                                    >
                                        {{
                                            profileForm.processing
                                                ? 'Saving...'
                                                : 'Save Changes'
                                        }}
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section
                            class="rounded-xl border border-[#dde4dd] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] dark:border-[#3c4a42] dark:bg-[#2b322d]"
                        >
                            <div
                                class="flex items-center gap-3 border-b border-[#dde4dd] pb-3 dark:border-[#3c4a42]"
                            >
                                <ShieldCheck
                                    class="size-6 text-[#006c49] dark:text-[#4edea3]"
                                />
                                <h2 class="text-2xl font-semibold">Security</h2>
                            </div>

                            <Form
                                v-bind="SecurityController.update.form()"
                                :options="{ preserveScroll: true }"
                                reset-on-success
                                :reset-on-error="[
                                    'password',
                                    'password_confirmation',
                                    'current_password',
                                ]"
                                class="mt-5 flex flex-col gap-4"
                                v-slot="{ errors, processing }"
                            >
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="current_password"
                                        class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                        >Current Password</label
                                    >
                                    <PasswordInput
                                        id="current_password"
                                        name="current_password"
                                        autocomplete="current-password"
                                        placeholder="Enter your current password"
                                        class="min-h-12 border-[#bbcabf] bg-[#f4fbf4] dark:border-[#6c7a71] dark:bg-[#161d19]"
                                    />
                                    <InputError
                                        :message="errors.current_password"
                                    />
                                </div>
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <div class="flex flex-col gap-1.5">
                                        <label
                                            for="password"
                                            class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                            >New Password</label
                                        >
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            autocomplete="new-password"
                                            placeholder="Enter a new password"
                                            class="min-h-12 border-[#bbcabf] bg-[#f4fbf4] dark:border-[#6c7a71] dark:bg-[#161d19]"
                                        />
                                        <InputError
                                            :message="errors.password"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <label
                                            for="password_confirmation"
                                            class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                            >Confirm Password</label
                                        >
                                        <PasswordInput
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            autocomplete="new-password"
                                            placeholder="Confirm your new password"
                                            class="min-h-12 border-[#bbcabf] bg-[#f4fbf4] dark:border-[#6c7a71] dark:bg-[#161d19]"
                                        />
                                        <InputError
                                            :message="
                                                errors.password_confirmation
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        class="min-h-12 rounded-lg bg-[#dde4dd] px-6 text-sm font-semibold text-[#3c4a42] transition-colors hover:bg-[#bbcabf] disabled:opacity-60 dark:bg-[#3c4a42] dark:text-[#ebf3eb] dark:hover:bg-[#565e74]"
                                        :disabled="processing"
                                        data-test="update-password-button"
                                    >
                                        {{
                                            processing
                                                ? 'Updating...'
                                                : 'Update Password'
                                        }}
                                    </button>
                                </div>
                            </Form>
                        </section>

                        <section
                            class="rounded-xl border border-[#dde4dd] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] dark:border-[#3c4a42] dark:bg-[#2b322d]"
                        >
                            <h2
                                class="border-b border-[#dde4dd] pb-3 text-2xl font-semibold dark:border-[#3c4a42]"
                            >
                                Preferences
                            </h2>
                            <Form
                                v-bind="ProfileController.updateLanguage.form()"
                                class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end"
                                v-slot="{ errors, processing }"
                            >
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label
                                        for="language"
                                        class="text-sm font-medium text-[#3c4a42] dark:text-[#bbcabf]"
                                        >Language</label
                                    >
                                    <select
                                        id="language"
                                        name="language"
                                        class="min-h-12 rounded-lg border border-[#bbcabf] bg-[#f4fbf4] px-4 text-base transition outline-none focus:border-[#006c49] focus:ring-1 focus:ring-[#006c49] dark:border-[#6c7a71] dark:bg-[#161d19] dark:focus:border-[#4edea3] dark:focus:ring-[#4edea3]"
                                        :aria-invalid="Boolean(errors.language)"
                                    >
                                        <option
                                            value="en"
                                            :selected="user.language === 'en'"
                                        >
                                            English
                                        </option>
                                        <option
                                            value="ar"
                                            :selected="user.language === 'ar'"
                                        >
                                            Arabic
                                        </option>
                                    </select>
                                    <InputError :message="errors.language" />
                                </div>
                                <button
                                    type="submit"
                                    class="min-h-12 rounded-lg bg-[#006c49] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#005236] disabled:opacity-60 dark:bg-[#10b981] dark:text-[#002113]"
                                    :disabled="processing"
                                    data-test="update-language-button"
                                >
                                    {{
                                        processing
                                            ? 'Saving...'
                                            : 'Save Preference'
                                    }}
                                </button>
                            </Form>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
